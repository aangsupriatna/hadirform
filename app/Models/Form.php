<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'event_id',
        'title',
        'description',
        'public_slug',
        'status',
        'opens_at',
        'closes_at',
        'current_version_id',
        'settings_json',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'settings_json' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class, 'current_version_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function attendeeLookups(): HasMany
    {
        return $this->hasMany(FormAttendeeLookup::class);
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();
        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    public function getThemeColorAttribute(): string
    {
        return $this->settings_json['theme_color'] ?? 'indigo';
    }

    public function getThemeAttribute(): array
    {
        return \App\Services\FormThemeService::get($this->theme_color);
    }

    public function getHeaderImageAttribute(): ?string
    {
        return $this->settings_json['header_image'] ?? ($this->currentVersion?->schema_json['settings']['header_image'] ?? null);
    }
}
