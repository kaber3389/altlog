<?php

namespace App\Http\Enums\Hold;

enum HoldStatus: string
{
    case HELD = 'held';
    case CONFIRMED = 'confirmed';
    case CANCELED = 'cancelled';
}