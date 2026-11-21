<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent sample data, without creating a known-password user account.
        $rows = json_decode(file_get_contents(__DIR__.'/data/jobs.json'), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $company = Company::firstOrCreate(['slug' => Str::slug($row['company'])], [
                    'name' => $row['company'],
                    'logo_text' => $row['logo'] ?? strtoupper(substr($row['company'], 0, 3)),
                    'brand_color' => $row['color'] ?? '#8250ce',
                    'description' => 'Illustrative company for the DevHire learning project.',
                ]);

                if (isset($row['salary_min'], $row['salary_max'])) {
                    $min = (int) $row['salary_min'];
                    $max = (int) $row['salary_max'];
                } else {
                    preg_match_all('/[\d,]+/', $row['salary'] ?? '0', $salary);
                    $min = (int) str_replace(',', '', $salary[0][0] ?? 0);
                    $max = (int) str_replace(',', '', $salary[0][1] ?? $min);
                }

                $requirements = is_array($row['requirements'] ?? null)
                    ? $row['requirements']
                    : (isset($row['requirements']) ? [$row['requirements']] : [
                        "Relevant skills or project experience for the {$row['title']} role.",
                        'Clear communication and a collaborative mindset.',
                        'Curiosity, ownership, and attention to detail.',
                        'A portfolio or CV that tells your story.',
                    ]);

                $responsibilities = $row['responsibilities'] ?? [
                    "Collaborate with your team to deliver high-quality work in {$row['category']}.",
                    'Bring fresh ideas and a thoughtful approach to everyday challenges.',
                    'Share progress, ask questions, and help your teammates succeed.',
                    'Learn from feedback and continuously develop your skills.',
                ];

                JobPost::updateOrCreate(['slug' => Str::slug($row['company'].'-'.$row['title'])], [
                    'company_id' => $company->id,
                    'title' => $row['title'],
                    'category' => $row['category'],
                    'location' => $row['location'],
                    'employment_type' => $row['type'],
                    'work_mode' => $row['mode'],
                    'salary_min' => $min,
                    'salary_max' => $max,
                    'description' => $row['description'] ?? "Join {$row['company']} as a {$row['title']} and help a growing team turn thoughtful ideas into useful experiences. This is an illustrative opportunity in {$row['location']}.",
                    'responsibilities' => $responsibilities,
                    'requirements' => $requirements,
                    'benefits' => $row['benefits'] ?? 'A supportive team, opportunities to learn, and meaningful work. This is sample content, not a live employer offer.',
                    'status' => 'published',
                    'is_demo' => true,
                    'is_featured' => $row['is_featured'] ?? false,
                    'published_at' => now()->subDays($row['days'] ?? 1),
                ]);
            }
        });
    }
}
