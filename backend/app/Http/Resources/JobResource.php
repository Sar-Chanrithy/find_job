<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $salaryText = ($this->salary_min > 0 && $this->salary_max > 0)
            ? '$'.number_format($this->salary_min).' – $'.number_format($this->salary_max)
            : (($this->salary_min > 0) ? '$'.number_format($this->salary_min).'+' : 'Negotiable');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'company' => $this->company->name,
            'logo' => $this->company->logo_text,
            'color' => $this->company->brand_color,
            'category' => $this->category,
            'location' => $this->location,
            'type' => $this->employment_type,
            'mode' => $this->work_mode,
            'salary' => $salaryText,
            'currency' => $this->currency,
            'days' => (int) $this->published_at->diffInDays(now()),
            'published_at' => $this->published_at->toIso8601String(),
            'is_demo' => $this->is_demo,
            'description' => $this->description,
            'responsibilities' => $this->responsibilities,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
        ];
    }
}
