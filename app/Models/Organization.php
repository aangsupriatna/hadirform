<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'brand_color',
        'settings_json',
    ];

    protected $casts = [
        'settings_json' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }
}
