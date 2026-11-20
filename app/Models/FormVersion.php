<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'version_number',
        'schema_json',
        'published_at',
        'published_by',
        'schema_hash',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'published_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }
}
