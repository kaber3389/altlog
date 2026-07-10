<?php

namespace App\Http\Controllers;

use App\Exceptions\ResourceConflictException;
use App\Http\Requests\Hold\StoreHoldRequest;
use App\Http\Resources\HoldResource;
use App\Http\Services\SlotService;

class HoldController extends Controller
{
    public function hold(int $slotId, StoreHoldRequest $request, SlotService $slotService)
    {
        $hold = $slotService->hold($slotId, $request->validated('idempotency_key'));

        return HoldResource::make($hold)
            ->response()
            ->setStatusCode($hold->wasRecentlyCreated ? 201 : 200);
    }

    public function confirm(int $holdId, SlotService $slotService)
    {
        return HoldResource::make($slotService->confirm($holdId));
    }

    public function cancel(int $holdId, SlotService $slotService)
    {
        return HoldResource::make($slotService->cancel($holdId));
    }
}
