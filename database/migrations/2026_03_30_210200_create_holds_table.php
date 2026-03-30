<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('to_slot');
            $table->dateTime('at_end');
            $table->enum('status', ['confirmed', 'held', 'cancelled'])->default('held');
            $table->unsignedInteger('UUID')->unique();

            $table->foreign('to_slot')
                ->references('slot_id')
                ->on('slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
