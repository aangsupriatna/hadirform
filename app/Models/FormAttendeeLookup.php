<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAttendeeLookup extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'identifier',
        'name',
        'email',
        'phone',
        'institution',
        'position',
        'custom_data_json',
        'is_attended',
        'attended_at',
        'response_id',
    ];

    protected $casts = [
        'custom_data_json' => 'array',
        'is_attended' => 'boolean',
        'attended_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }
}
