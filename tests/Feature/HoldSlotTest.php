<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HoldSlotTest extends TestCase
{
    use RefreshDatabase;

    public function test_hold_returns_conflict_when_capacity_equals_remaining(): void
    {
        DB::table('slot')->insert([
            'slot_id' => 1,
            'capacity' => 10,
            'remaining' => 10,
        ]);

        $response = $this->postJson('/slots/1/hold', [
            'UUID' => 111,
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'message' => 'Slot has no remaining capacity',
            ]);

        $this->assertDatabaseCount('holds', 0);
    }

    public function test_hold_is_created_when_capacity_does_not_equal_remaining(): void
    {
        DB::table('slot')->insert([
            'slot_id' => 2,
            'capacity' => 10,
            'remaining' => 9,
        ]);

        $response = $this->postJson('/slots/2/hold', [
            'UUID' => 222,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'ok')
            ->assertJsonStructure(['id']);

        $this->assertDatabaseHas('holds', [
            'to_slot' => 2,
            'UUID' => 222,
            'status' => 'held',
        ]);

        $this->assertDatabaseHas('slot', [
            'slot_id' => 2,
            'remaining' => 10,
        ]);
    }

    public function test_confirm_hold_updates_status_and_decrements_remaining_atomically(): void
    {
        DB::table('slot')->insert([
            'slot_id' => 3,
            'capacity' => 10,
            'remaining' => 2,
        ]);

        $holdId = DB::table('holds')->insertGetId([
            'to_slot' => 3,
            'at_end' => now()->addMinutes(5),
            'status' => 'held',
            'UUID' => 333,
        ]);

        $response = $this->postJson("/holds/{$holdId}/confirm");

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'ok')
            ->assertJsonPath('id', $holdId)
            ->assertJsonPath('status', 'confirmed');

        $this->assertDatabaseHas('holds', [
            'id' => $holdId,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('slot', [
            'slot_id' => 3,
            'remaining' => 1,
        ]);
    }

    public function test_confirm_hold_returns_conflict_when_slot_has_no_remaining_capacity(): void
    {
        DB::table('slot')->insert([
            'slot_id' => 4,
            'capacity' => 10,
            'remaining' => 0,
        ]);

        $holdId = DB::table('holds')->insertGetId([
            'to_slot' => 4,
            'at_end' => now()->addMinutes(5),
            'status' => 'held',
            'UUID' => 444,
        ]);

        $response = $this->postJson("/holds/{$holdId}/confirm");

        $response
            ->assertStatus(409)
            ->assertJson([
                'message' => 'Slot has no remaining capacity',
            ]);

        $this->assertDatabaseHas('holds', [
            'id' => $holdId,
            'status' => 'held',
        ]);

        $this->assertDatabaseHas('slot', [
            'slot_id' => 4,
            'remaining' => 0,
        ]);
    }

    public function test_delete_hold_cancels_hold_and_returns_slot_to_available(): void
    {
        DB::table('slot')->insert([
            'slot_id' => 5,
            'capacity' => 10,
            'remaining' => 5,
        ]);

        $holdId = DB::table('holds')->insertGetId([
            'to_slot' => 5,
            'at_end' => now()->addMinutes(5),
            'status' => 'held',
            'UUID' => 555,
        ]);

        $response = $this->deleteJson("/holds/{$holdId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'ok')
            ->assertJsonPath('id', $holdId)
            ->assertJsonPath('status', 'cancelled');

        $this->assertDatabaseHas('holds', [
            'id' => $holdId,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('slot', [
            'slot_id' => 5,
            'remaining' => 4,
        ]);
    }
}
