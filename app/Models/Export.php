<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'requested_by',
        'form_id',
        'format',
        'filters_json',
        'status',
        'file_path',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'filters_json' => 'array',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
