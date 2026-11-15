<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSearchRequest;
use App\Http\Resources\JobResource;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobController extends Controller
{
    public function index(JobSearchRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = JobPost::query()->visible()->with('company');

        if (!empty($filters['q'])) {
            // Bound parameter, with LIKE metacharacters escaped for literal searches.
            $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['q']).'%';
            $query->where(fn (Builder $q) => $q->where('title', 'ilike', $term)
                ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'ilike', $term)));
        }

        foreach (['category' => 'category', 'location' => 'location', 'type' => 'employment_type'] as $key => $column) {
            if (!empty($filters[$key])) {
                $query->where($column, $filters[$key]);
            }
        }
        if ($request->boolean('remote')) {
            $query->where('work_mode', 'Remote');
        }
        if (array_key_exists('ids', $filters)) {
            $query->whereIn('id', $filters['ids']);
        }
        if (($filters['sort'] ?? 'featured') === 'featured') {
            $query->orderByDesc('is_featured');
        }

        return JobResource::collection($query->orderByDesc('published_at')->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 9));
    }

    public function show(string $id): JobResource
    {
        // Avoid bigint overflow and hide drafts, closed, expired, and scheduled jobs.
        abort_if(strlen($id) > 10 || (int) $id > 2147483647, 404);
        return new JobResource(JobPost::query()->visible()->with('company')->findOrFail($id));
    }
}
