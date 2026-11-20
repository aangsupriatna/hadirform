<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Response extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'event_id',
        'form_id',
        'form_version_id',
        'receipt_code',
        'status',
        'source',
        'submitted_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'dedupe_key',
        'consent_version',
        'consented_at',
        'idempotency_token',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'consented_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class, 'form_version_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function participantSignature(): HasOne
    {
        return $this->hasOne(Signature::class)->ofMany([
            'id' => 'max',
        ], function ($query) {
            $query->where('signer_type', 'participant');
        });
    }

    public function operatorSignature(): HasOne
    {
        return $this->hasOne(Signature::class)->ofMany([
            'id' => 'max',
        ], function ($query) {
            $query->where('signer_type', 'operator');
        });
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(ResponseVerification::class)->orderByDesc('id');
    }

    public function getAnswerValue(string $fieldKey, mixed $default = null): mixed
    {
        $answer = $this->answers->firstWhere('field_key', $fieldKey);
        return $answer ? $answer->value_json : $default;
    }
}
