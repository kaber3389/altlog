<?php

namespace App\Models;

use App\Http\Enums\Hold\HoldStatus;
use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    protected $fillable = [
        'slot_id',
        'idempotency_key',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'status' => HoldStatus::class,
    ];
}
