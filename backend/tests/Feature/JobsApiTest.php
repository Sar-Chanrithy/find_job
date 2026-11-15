<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobPost;
use Database\Seeders\JobSeeder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class JobsApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Tests only insert fixtures inside a rollback transaction in the dedicated DB.
        $this->assertSame('devhire_test', DB::connection()->getDatabaseName());
        $this->seed(JobSeeder::class);
    }

    public function test_public_listing_is_paginated_and_has_only_public_fields(): void
    {
        $response = $this->getJson('/api/jobs?per_page=2')->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 9)
            ->assertJsonPath('meta.last_page', 5);
        $job = $response->json('data.0');
        $this->assertArrayNotHasKey('owner_id', $job);
        $this->assertArrayNotHasKey('password', $job);
        $this->assertArrayNotHasKey('status', $job);
        $this->getJson('/api/jobs?page=2&per_page=2')->assertOk()
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_filters_search_company_and_sort_are_applied_in_postgres(): void
    {
        $this->getJson('/api/jobs?q=developer&remote=1&type=Full%20time')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Frontend Developer');
        $this->getJson('/api/jobs?category=Design&location=Battambang')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Graphic Designer');
        $this->getJson('/api/jobs?q=mekong')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/jobs?sort=newest')->assertOk()->assertJsonPath('data.0.days', 1);
    }

    public function test_saved_id_filter_and_detail_return_the_correct_job(): void
    {
        $job = JobPost::where('title', 'Frontend Developer')->firstOrFail();
        $this->getJson('/api/jobs?ids[]='.$job->id)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $job->id);
        $this->getJson('/api/jobs/'.$job->id)->assertOk()
            ->assertJsonPath('data.title', 'Frontend Developer')
            ->assertJsonStructure(['data' => ['description', 'responsibilities', 'requirements', 'benefits']]);
    }

    public function test_unpublished_closed_expired_and_future_jobs_are_hidden(): void
    {
        $jobs = JobPost::orderBy('id')->take(4)->get();
        $jobs[0]->update(['status' => 'draft']);
        $jobs[1]->update(['status' => 'closed']);
        $jobs[2]->update(['expires_at' => now()->subMinute()]);
        $jobs[3]->update(['published_at' => now()->addDay()]);
        $this->getJson('/api/jobs')->assertOk()->assertJsonPath('meta.total', 5);
        foreach ($jobs as $job) {
            $this->getJson('/api/jobs/'.$job->id)->assertNotFound();
        }
    }

    public function test_invalid_query_parameters_are_rejected(): void
    {
        foreach ([
            'q[]' => 'array', 'q' => str_repeat('a', 121), 'page' => -1,
            'per_page' => 1000, 'sort' => 'title; DROP TABLE users', 'remote' => 'yes',
            'type' => 'invalid', 'ids[0]' => 'not-a-number',
        ] as $key => $value) {
            $this->getJson('/api/jobs?'.http_build_query([$key => $value]))->assertUnprocessable();
        }
    }

    public function test_sql_and_like_metacharacters_are_treated_as_search_text(): void
    {
        foreach (["' OR 1=1 --", '%', '_', '\\'] as $term) {
            $this->getJson('/api/jobs?'.http_build_query(['q' => $term]))
                ->assertOk()->assertJsonCount(0, 'data');
        }
        $this->assertSame(9, JobPost::count());
    }

    public function test_no_unauthenticated_write_endpoints_exist(): void
    {
        $id = JobPost::firstOrFail()->id;
        $this->postJson('/api/jobs', ['title' => 'Injected job'])->assertStatus(405);
        $this->patchJson('/api/jobs/'.$id, ['status' => 'published'])->assertStatus(405);
        $this->deleteJson('/api/jobs/'.$id)->assertStatus(405);
        $this->assertSame(9, JobPost::count());
    }

    public function test_cors_allows_only_configured_frontends(): void
    {
        $this->withHeader('Origin', 'http://127.0.0.1:5173')->getJson('/api/jobs')
            ->assertOk()->assertHeader('Access-Control-Allow-Origin', 'http://127.0.0.1:5173')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->withHeader('Origin', 'https://untrusted.example')->getJson('/api/jobs')
            ->assertOk()->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_rate_limit_returns_429(): void
    {
        RateLimiter::for('public-api', fn () => Limit::perMinute(1)->by('isolated-rate-test'));
        $this->getJson('/api/jobs')->assertOk();
        $this->getJson('/api/jobs')->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_unknown_and_oversized_ids_return_json_404_without_debug_trace(): void
    {
        foreach (['0', '999999999999999999999999', 'not-an-id'] as $id) {
            $response = $this->getJson('/api/jobs/'.$id)->assertNotFound();
            $this->assertArrayNotHasKey('trace', $response->json());
            $this->assertArrayNotHasKey('exception', $response->json());
        }
    }

    public function test_seeder_is_repeatable_and_creates_no_default_user(): void
    {
        $this->seed(JobSeeder::class);
        $this->assertSame(9, JobPost::count());
        $this->assertSame(5, Company::count());
        $this->assertSame(0, DB::table('users')->count());
    }
}
