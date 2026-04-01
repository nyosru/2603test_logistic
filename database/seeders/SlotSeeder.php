<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlotSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('slot')->insert([
            [
                'slot_id' => 1,
                'capacity' => rand(10,15),
                'remaining' => 10,
            ],
            [
                'slot_id' => 2,
                'capacity' => 15,
                'remaining' => 2,
            ],
            [
                'slot_id' => 3,
                'capacity' => 20,
                'remaining' => 10,
            ],
        ]);
    }
}
