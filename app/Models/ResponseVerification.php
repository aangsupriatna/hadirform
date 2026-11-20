<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseVerification extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'response_id',
        'operator_id',
        'decision',
        'reason',
        'signature_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }
}
