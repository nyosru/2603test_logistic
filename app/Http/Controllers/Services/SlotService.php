<?php

namespace App\Http\Controllers\Services;

use App\Models\Hold;
use App\Models\Slot;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SlotService
{
    public function getAvailability(): Collection
    {
        return Slot::query()
            ->orderBy('slot_id')
            ->get();
    }

    public function createHold(int $slotId, int $uuid): array
    {
        return DB::transaction(function () use ($slotId, $uuid) {

            $existingHold = Hold::query()
                ->where('UUID', $uuid)
                ->first();

            if ($existingHold) {
                return [
                    'status' => 200,
                    'data' => $this->mapHoldResponse($existingHold),
                ];
            }

            $slot = Slot::where('slot_id', $slotId)
                ->lockForUpdate()
                ->first();

            if (!$slot) {
                return [
                    'status' => 404,
                    'data' => ['message' => 'Slot not found'],
                ];
            }

            if ((int) $slot->capacity === (int) $slot->remaining) {
                return [
                    'status' => 409,
                    'data' => ['message' => 'Slot has no remaining capacity'],
                ];
            }

            $atHold = now()->addMinutes(5);

            try {
                $hold = Hold::query()->create([
                    'to_slot' => $slotId,
                    'at_end' => $atHold,
                    'UUID' => $uuid,
                    'status' => 'held',
                ]);
            } catch (QueryException $ex) {
                $duplicate = Hold::query()
                    ->where('UUID', $uuid)
                    ->first();

                if ($duplicate) {
                    return [
                        'status' => 200,
                        'data' => $this->mapHoldResponse($duplicate),
                    ];
                }

                return ['data' => [], 'status' => 404, 'error' => true];
            }

            $slot->increment('remaining');

            return [
                'status' => 200,
                'data' => $this->mapHoldResponse($hold),
            ];
        });
    }

    public function getCurrentHolds(): Collection
    {
        return Hold::query()
            ->orderBy('at_end')
            ->get()
            ->map(fn (Hold $hold): array => [
                'id' => (int) $hold->id,
                'to_slot' => (int) $hold->to_slot,
                'UUID' => (int) $hold->UUID,
                'status' => (string) $hold->status,
                'at_end' => (string) $hold->at_end,
            ]);
    }

    public function confirmHold(int $holdId): array
    {
        return DB::transaction(function () use ($holdId) {
            $hold = Hold::query()
                ->with('slot')
                ->where('status', '!=', 'cancelled' )
                ->lockForUpdate()
                ->find($holdId);

            if (!$hold) {
                return [
                    'status' => 404,
                    'data' => ['message' => 'Hold not found'],
                ];
            }

            if ((string) $hold->status === 'confirmed') {
                return [
                    'status' => 200,
                    'data' => $this->mapHoldResponse($hold),
                ];
            }

            if ((string) $hold->status !== 'held') {
                return [
                    'status' => 409,
                    'data' => ['message' => 'Invalid hold state for confirmation'],
                ];
            }

            $remaining = $hold->slot->remaining;

            if ($remaining == 0 ) {
                return [
                    'status' => 409,
                    'data' => ['message' => 'Slot has no remaining capacity'],
                ];
            }

            $hold->slot->decrement('remaining');
            $hold->status = 'confirmed';
            $hold->save();

            return [
                'status' => 200,
                'data' => $this->mapHoldResponse($hold),
            ];
        });
    }

    public function cancelHold(int $holdId): array
    {
        return DB::transaction(function () use ($holdId) {
            $hold = Hold::query()
                ->with('slot')
                ->where('status', '=', 'held')
                ->lockForUpdate()
                ->find($holdId);

            if (!$hold) {
                return [
                    'status' => 404,
                    'data' => ['message' => 'Hold not found'],
                ];
            }

            $hold->slot->decrement('remaining');
            $hold->status = 'cancelled';
            $hold->save();

            return [
                'status' => 200,
                'data' => $this->mapHoldResponse($hold),
            ];
        });
    }

    private function mapHoldResponse(Hold $hold): array
    {
        return [
            'message' => 'ok',
            'id' => (int) $hold->id,
            'status' => (string) $hold->status,
        ];
    }
}
