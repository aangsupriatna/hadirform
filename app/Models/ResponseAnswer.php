<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'field_key',
        'value_json',
    ];

    protected $casts = [
        'value_json' => 'json',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }
}
