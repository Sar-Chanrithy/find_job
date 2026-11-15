<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Explicit public fields only: never serialize users or company owners.
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
            'salary' => '$'.number_format($this->salary_min).' – $'.number_format($this->salary_max),
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
