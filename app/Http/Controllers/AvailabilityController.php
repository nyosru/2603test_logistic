<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\SlotService;
use App\Http\Resources\SlotResource;
use App\Models\Slot;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly SlotService $slotService,
    ) {
    }

    public function availability()
    {
        $slots = $this->slotService->getAvailability();

        return $slots
            ->map(fn (Slot $slot) => (new SlotResource($slot))->resolve())
            ->values();
    }
}
