<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobSeeder extends Seeder
{d
    public function run(): void
    {
        $rows = json_decode(file_get_contents(__DIR__.'/data/jobs.json'), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $company = Company::firstOrCreate(['slug' => Str::slug($row['company'])], [
                    'name' => $row['company'],
                    'logo_text' => $row['logo'] ?? strtoupper(substr($row['company'], 0, 3)),
                    'brand_color' => $row['color'] ?? '#8250ce',
                    'description' => "Verified company {$row['company']} operating in Cambodia.",
                ]);

                if (isset($row['salary_min'], $row['salary_max']) && ($row['salary_min'] !== null || $row['salary_max'] !== null)) {
                    $min = (int) ($row['salary_min'] ?? 0);
                    $max = (int) ($row['salary_max'] ?? $min);
                } else {
                    preg_match_all('/[\d,]+/', $row['salary'] ?? '0', $salary);
                    $min = !empty($salary[0][0]) ? (int) str_replace(',', '', $salary[0][0]) : 0;
                    $max = !empty($salary[0][1]) ? (int) str_replace(',', '', $salary[0][1]) : $min;
                }

                $requirements = is_array($row['requirements'] ?? null)
                    ? $row['requirements']
                    : (isset($row['requirements']) ? [$row['requirements']] : [
                        "Relevant skills or experience for the {$row['title']} role.",
                        'Clear communication and a collaborative mindset.',
                        'Curiosity, ownership, and attention to detail.',
                    ]);

                $responsibilities = $row['responsibilities'] ?? [
                    "Execute key responsibilities for the {$row['title']} position in {$row['category']}.",
                    "Collaborate with teammates at {$row['company']} to achieve project targets.",
                    'Share progress, solve problems proactively, and ensure work quality.',
                    'Continuously develop your professional skills and industry knowledge.',
                ];

                $benefits = $row['benefits'] ?? (isset($row['source_url']) && $row['source_url']
                    ? "Competitive compensation, professional working environment, and career growth opportunities. Real verified vacancy (Source: {$row['source_url']})."
                    : 'A supportive team, opportunities to learn, and career development opportunities.');

                JobPost::updateOrCreate(['slug' => Str::slug($row['company'].'-'.$row['title'])], [
                    'company_id' => $company->id,
                    'title' => $row['title'],
                    'category' => $row['category'],
                    'location' => $row['location'],
                    'employment_type' => $row['type'],
                    'work_mode' => in_array($row['mode'] ?? '', ['Remote', 'Hybrid', 'On site']) ? $row['mode'] : 'On site',
                    'salary_min' => $min,
                    'salary_max' => $max,
                    'currency' => $row['currency'] ?? 'USD',
                    'description' => $row['description'] ?? "Join {$row['company']} as a {$row['title']} in {$row['location']}.",
                    'responsibilities' => $responsibilities,
                    'requirements' => $requirements,
                    'benefits' => $benefits,
                    'status' => 'published',
                    'is_demo' => false,
                    'is_featured' => $row['is_featured'] ?? false,
                    'published_at' => now()->subDays($row['days'] ?? 1),
                ]);
            }
        });
    }
}
