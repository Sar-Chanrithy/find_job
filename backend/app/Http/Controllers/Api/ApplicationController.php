<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use App\Notifications\ApplicationUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    public function uploadCv(Request $request)
    {
        $request->validate(['cv' => 'required|file|mimes:pdf|extensions:pdf|max:2048']);
        $file = $request->file('cv');
        // Validate the actual file signature as well as MIME and extension.
        abort_unless(file_get_contents($file->getRealPath(), false, null, 0, 5) === '%PDF-', 422, 'Please upload a valid PDF.');
        $path = $file->store('profiles', 'cvs');
        try {
            $old = DB::transaction(function () use ($request, $path, $file) {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $old = $user->cv_path;
                $user->cv_path = $path;
                $user->cv_name = Str::limit(basename($file->getClientOriginalName()), 200, '');
                $user->save();
                return $old;
            });
        } catch (\Throwable $error) {
            Storage::disk('cvs')->delete($path);
            throw $error;
        }
        if ($old) Storage::disk('cvs')->delete($old);
        return response()->json(['user' => AuthController::profile($request->user()->fresh())]);
    }

    public function myCv(Request $request)
    {
        $path = $request->user()->cv_path;
        abort_unless($path && Storage::disk('cvs')->exists($path), 404);
        return Storage::disk('cvs')->download($path, 'my-cv.pdf', ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function store(Request $request, JobPost $job)
    {
        $data = $request->validate(['cover_letter' => 'nullable|string|max:5000']);
        $snapshot = null;
        try {
            $application = DB::transaction(function () use ($request, $job, $data, &$snapshot) {
                // Lock the candidate to serialize duplicate submissions and CV replacements.
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                abort_unless(JobPost::visible()->whereKey($job->id)->exists(), 422, 'This job is no longer accepting applications.');
                abort_if($job->is_demo, 422, 'This is a sample listing. Apply to a real employer listing instead.');
                abort_unless($job->company->owner?->is_active, 422, 'This employer is not accepting applications.');
                if (JobApplication::where('job_post_id', $job->id)->where('candidate_id', $user->id)->exists()) {
                    throw ValidationException::withMessages(['application' => 'You have already applied to this job.']);
                }
                abort_unless($user->cv_path && Storage::disk('cvs')->exists($user->cv_path), 422, 'Upload your PDF CV in your dashboard first.');
                $snapshot = 'applications/'.Str::uuid().'.pdf';
                Storage::disk('cvs')->copy($user->cv_path, $snapshot);
                $application = JobApplication::create([
                    'job_post_id' => $job->id, 'candidate_id' => $user->id,
                    'cv_path' => $snapshot, 'cv_name' => $user->cv_name,
                    'cover_letter' => $data['cover_letter'] ?? null,
                ]);
                $application->events()->create(['status' => 'submitted']);
                $job->company->owner->notify(new ApplicationUpdate("{$user->name} applied for {$job->title}.", $application->id));
                $user->notify(new ApplicationUpdate("Your application for {$job->title} has been submitted.", $application->id));
                return $application;
            });
        } catch (\Throwable $error) {
            if ($snapshot) Storage::disk('cvs')->delete($snapshot);
            throw $error;
        }
        return response()->json(['application' => $application], 201);
    }

    public function index(Request $request)
    {
        $query = JobApplication::with(['job.company:id,name,owner_id', 'candidate:id,name,email', 'events']);
        if ($request->user()->role === 'candidate') $query->where('candidate_id', $request->user()->id);
        if ($request->user()->role === 'employer') $query->whereHas('job.company', fn ($q) => $q->where('owner_id', $request->user()->id));
        return $query->latest('id')->paginate(15);
    }

    private function authorizeView(Request $request, JobApplication $application): void
    {
        $user = $request->user();
        abort_unless($user->role === 'admin' || ($user->role === 'candidate' && $application->candidate_id === $user->id)
            || ($user->role === 'employer' && $application->job->company->owner_id === $user->id), 403);
    }

    public function cv(Request $request, JobApplication $application)
    {
        $this->authorizeView($request, $application);
        abort_unless(Storage::disk('cvs')->exists($application->cv_path), 404);
        return Storage::disk('cvs')->download($application->cv_path, 'application-'.$application->id.'-cv.pdf', [
            'Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }

    public function update(Request $request, JobApplication $application)
    {
        $this->authorizeView($request, $application);
        $data = $request->validate(['status' => 'required|in:reviewing,shortlisted,accepted,rejected,withdrawn']);
        DB::transaction(function () use ($request, $application, $data) {
            $application = JobApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $candidate = $request->user()->role === 'candidate';
            abort_if($candidate && $data['status'] !== 'withdrawn', 403);
            abort_if(!$candidate && $data['status'] === 'withdrawn', 403);
            $transitions = [
                'submitted' => ['reviewing', 'shortlisted', 'accepted', 'rejected', 'withdrawn'],
                'reviewing' => ['shortlisted', 'accepted', 'rejected', 'withdrawn'],
                'shortlisted' => ['accepted', 'rejected', 'withdrawn'],
            ];
            abort_unless(in_array($data['status'], $transitions[$application->status] ?? [], true), 422, 'This status transition is not allowed.');
            $application->update(['status' => $data['status']]);
            $application->events()->create(['status' => $data['status']]);
            $recipient = $candidate ? $application->job->company->owner : $application->candidate;
            $recipient?->notify(new ApplicationUpdate("Application for {$application->job->title}: {$data['status']}.", $application->id));
        });
        return response()->json(['application' => $application->fresh('events')]);
    }
}
