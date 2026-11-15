<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        return Company::query()->when($request->user()->role !== 'admin', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->orderBy('name')->paginate(50);
    }

    private function fields(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120', 'description' => 'nullable|string|max:5000',
            'logo_text' => 'required|string|max:12', 'brand_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->fields($request);
        $company = Company::create([...$data, 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(10))]);
        return response()->json(['company' => $company], 201);
    }

    public function update(Request $request, Company $company)
    {
        abort_unless($request->user()->role === 'admin' || $company->owner_id === $request->user()->id, 403);
        $company->update($this->fields($request));
        return response()->json(['company' => $company->fresh()]);
    }

    public function destroy(Company $company)
    {
        abort_if($company->owner_id !== null, 422, 'An employer company cannot be removed while its account exists.');
        abort_if(JobPost::withTrashed()->where('company_id', $company->id)->exists(), 422, 'This company has job or application history.');
        $company->delete();
        return response()->json(['message' => 'Company removed.']);
    }
}
