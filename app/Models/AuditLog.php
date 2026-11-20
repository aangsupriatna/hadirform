<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'actor_user_id',
        'action',
        'subject_type',
        'subject_id',
        'before_json',
        'after_json',
        'request_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'before_json' => 'array',
        'after_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function log(
        string $action,
        Model $subject,
        ?array $before = null,
        ?array $after = null,
        ?int $orgId = null
    ): self {
        $user = auth()->user();
        return self::create([
            'organization_id' => $orgId ?? $user?->organization_id ?? ($subject->organization_id ?? null),
            'actor_user_id' => $user?->id,
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'before_json' => $before,
            'after_json' => $after,
            'request_id' => request()->header('X-Request-ID') ?? request()->fingerprint() ?? null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
