<?php

namespace App\Http\Controllers;

use App\Services\SlotService;
use Illuminate\Http\Request;

class HoldController extends Controller
{
    public function __construct(
        private readonly SlotService $slotService,
    ) {
    }

    public function create(Request $request, int $id)
    {
        $validated = $request->validate([
            'UUID' => ['required', 'integer', 'min:0'],
        ]);

        $result = $this->slotService->createHold($id, (int) $validated['UUID']);

        // если ответ не кешированный, значит изменили бд, забыаем кеш
        if( isset($result['cached']) && $result['cached'] === false ){
            $this->slotService->invalidateAvailabilityCache();
        }

        return response()->json($result['data'], $result['status']);
    }

    public function current()
    {
        return response()->json(
            $this->slotService->getCurrentHolds(),
            200
        );
    }

    public function confirm(int $id)
    {
        $result = $this->slotService->confirmHold($id);

        // если ответ не кешированный, значит изменили бд, забыаем кеш
        if( isset($result['cached']) && $result['cached'] === false ){
            $this->slotService->invalidateAvailabilityCache();
        }

        return response()->json($result['data'], $result['status']);
    }

    public function destroy(int $id)
    {
        $result = $this->slotService->cancelHold($id);

        // если ответ не кешированный, значит изменили бд, забыаем кеш
        if( isset($result['cached']) && $result['cached'] === false ){
            $this->slotService->invalidateAvailabilityCache();
        }

        return response()->json($result['data'], $result['status']);
    }
}
