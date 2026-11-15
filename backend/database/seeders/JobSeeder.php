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
                    'name' => $row['company'], 'logo_text' => $row['logo'],
                    'brand_color' => $row['color'], 'description' => 'Illustrative company for the DevHire learning project.',
                ]);
                preg_match_all('/[\d,]+/', $row['salary'], $salary);
                JobPost::firstOrCreate(['slug' => Str::slug($row['company'].'-'.$row['title'])], [
                    'company_id' => $company->id, 'title' => $row['title'],
                    'category' => $row['category'], 'location' => $row['location'],
                    'employment_type' => $row['type'], 'work_mode' => $row['mode'],
                    'salary_min' => (int) str_replace(',', '', $salary[0][0]),
                    'salary_max' => (int) str_replace(',', '', $salary[0][1]),
                    'description' => "Join {$row['company']} as a {$row['title']} and help a growing team turn thoughtful ideas into useful experiences. This is an illustrative opportunity in {$row['location']}.",
                    'responsibilities' => [
                        "Collaborate with your team to deliver high-quality work in {$row['category']}.",
                        'Bring fresh ideas and a thoughtful approach to everyday challenges.',
                        'Share progress, ask questions, and help your teammates succeed.',
                        'Learn from feedback and continuously develop your skills.',
                    ],
                    'requirements' => [
                        "Relevant skills or project experience for the {$row['title']} role.",
                        'Clear communication and a collaborative mindset.',
                        'Curiosity, ownership, and attention to detail.',
                        'A portfolio or CV that tells your story.',
                    ],
                    'benefits' => 'A supportive team, opportunities to learn, and meaningful work. This is sample content, not a live employer offer.',
                    'status' => 'published', 'is_demo' => true,
                    'is_featured' => $row['is_featured'] ?? ($row['id'] <= 10),
                    'published_at' => now()->subDays($row['days']),
                ]);
            }
        });
    }
}
