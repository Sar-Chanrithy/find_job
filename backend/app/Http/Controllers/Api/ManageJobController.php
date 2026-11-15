<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManageJobController extends Controller
{
    private function authorizeJob(Request $request, JobPost $job): void
    {
        abort_unless($request->user()->role === 'admin' || $job->company->owner_id === $request->user()->id, 403);
    }

    public function index(Request $request)
    {
        $query = JobPost::with('company:id,name,owner_id');
        if ($request->user()->role !== 'admin') {
            $query->whereHas('company', fn ($q) => $q->where('owner_id', $request->user()->id));
        }
        return $query->latest('id')->paginate(15);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:150', 'company_id' => 'required|integer|exists:companies,id',
            'category' => 'required|in:IT & Software,Marketing,Sales,Design,Finance,HR & Admin,Engineering',
            'location' => 'required|in:Phnom Penh,Siem Reap,Battambang',
            'employment_type' => 'required|in:Full time,Part time,Internship',
            'work_mode' => 'required|in:Remote,Hybrid,On site',
            'salary_min' => 'required|integer|min:0|max:1000000',
            'salary_max' => 'required|integer|gte:salary_min|max:1000000',
            'description' => 'required|string|min:30|max:10000',
            'responsibilities' => 'required|array|min:1|max:20', 'responsibilities.*' => 'required|string|max:500',
            'requirements' => 'required|array|min:1|max:20', 'requirements.*' => 'required|string|max:500',
            'benefits' => 'required|string|max:5000',
            'status' => 'required|in:draft,published,closed',
            'expires_at' => 'nullable|date|after:now',
        ]);
    }

    private function authorizeCompany(Request $request, int $companyId): void
    {
        $company = Company::findOrFail($companyId);
        abort_unless($request->user()->role === 'admin' || $company->owner_id === $request->user()->id, 403);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->authorizeCompany($request, $data['company_id']);
        $job = JobPost::create([
            ...$data, 'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(12)),
            'published_at' => $data['status'] === 'published' ? now() : null,
            'is_demo' => false, 'is_featured' => false,
        ]);
        return response()->json(['job' => $job->load('company:id,name')], 201);
    }

    public function update(Request $request, JobPost $job)
    {
        $this->authorizeJob($request, $job);
        $data = $this->validated($request);
        $this->authorizeCompany($request, $data['company_id']);
        // Keep applications visible to their original employer; ownership cannot be transferred.
        abort_if((int) $data['company_id'] !== $job->company_id, 422, 'A job cannot be moved to another company.');
        if ($data['status'] === 'published' && !$job->published_at) $data['published_at'] = now();
        $job->update($data);
        return response()->json(['job' => $job->fresh('company:id,name')]);
    }

    public function destroy(Request $request, JobPost $job)
    {
        $this->authorizeJob($request, $job);
        $job->delete(); // Preserve application history while removing the public listing.
        return response()->json(['message' => 'Job removed. Application history has been preserved.']);
    }
}
