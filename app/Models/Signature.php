<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'response_id',
        'signer_type',
        'signed_by_user_id',
        'file_path',
        'mime_type',
        'file_size',
        'sha256_hash',
        'signed_at',
        'consent_version',
        'metadata_json',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
