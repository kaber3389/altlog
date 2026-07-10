<?php

namespace App\Http\Services;

use App\Exceptions\ResourceConflictException;
use App\Http\Enums\Hold\HoldStatus;
use App\Models\Hold;
use App\Models\Slot;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class  SlotService
{
    const CACHE_KEY = 'slots.availability';
    const CACHE_TTL = 10;

    public function availability()
    {
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached) {
            return $cached;
        }

        $lock = Cache::lock(self::CACHE_KEY . '.lock', 10);
        $lock->block(5);

        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return Slot::orderBy('id')->get()->map(function ($slot) {
                    return [
                        'slot_id' => $slot->id,
                        'capacity' => $slot->capacity,
                        'remaining' => $slot->remaining,
                    ];
                });
            });
        } finally {
            $lock->release();
        }
    }

    public function hold(int $slotId, string $idempotencyKey)
    {
        $existingHold = Hold::where('slot_id', $slotId)->where('idempotency_key', $idempotencyKey)->first();

        if ($existingHold && !$existingHold->expires_at->isPast()) {
            return $existingHold;
        }

        $slot = Slot::where('id', $slotId)->firstOrFail();

        if ($slot->remaining <= 0) {
            throw new ResourceConflictException('There are no free seats in this slot.');
        }

        try {
            return Hold::create([
                'slot_id' => $slot->id,
                'idempotency_key' => $idempotencyKey,
                'status' => HoldStatus::HELD,
                'expires_at' => now()->addMinutes(5),
            ]);
        } catch (UniqueConstraintViolationException) {
            return Hold::where('slot_id', $slotId)->where('idempotency_key', $idempotencyKey)->firstOrFail();
        }
    }

    public function confirm(int $holdId)
    {
        $hold = DB::transaction(function () use ($holdId) {
            $hold = Hold::lockForUpdate()->findOrFail($holdId);

            if ($hold->status !== HoldStatus::HELD || $hold->expires_at->isPast()) {
                throw new ResourceConflictException('Hold is not active');
            }

            $updated = Slot::where('id', $hold->slot_id)
                ->where('remaining', '>', 0)
                ->decrement('remaining');

            if (!$updated) {
                throw new ResourceConflictException('Slot not found');
            }

            $hold->update(['status' => HoldStatus::CONFIRMED]);

            return $hold;
        });

        $this->forgetSlotAvailability();

        return $hold;
    }

    public function cancel(int $holdId)
    {
        $hold = DB::transaction(function () use ($holdId) {
            $hold = Hold::lockForUpdate()->findOrFail($holdId);

            if ($hold->status === HoldStatus::CANCELED) {
                return $hold;
            }

            if ($hold->status === HoldStatus::CONFIRMED) {
                Slot::where('id', $hold->slot_id)->increment('remaining');
            }

            $hold->update(['status' => HoldStatus::CANCELED]);

            return $hold;
        });

        $this->forgetSlotAvailability();

        return $hold;
    }

    public function forgetSlotAvailability(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
