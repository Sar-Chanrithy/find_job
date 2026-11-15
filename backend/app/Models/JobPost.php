<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPost extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    // Controllers only pass validated, explicitly selected job fields.
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'responsibilities' => 'array', 'requirements' => 'array',
            'is_demo' => 'boolean', 'is_featured' => 'boolean',
            'published_at' => 'datetime', 'expires_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
