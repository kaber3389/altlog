<?php

namespace App\Http\Controllers;

use App\Http\SlotService;

class SlotController extends Controller
{
    public function __invoke(SlotService $slotService)
    {
        return response()->json($slotService->availability());
    }
}
