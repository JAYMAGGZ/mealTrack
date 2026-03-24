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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('meal_slot_id')->constrained()->cascadeOnDelete();
        $table->enum('status', [
            'pending',       // booked, waiting for check-in
            'confirmed',     // checked in at dining hall
            'cancelled',     // cancelled by resident before cutoff
            'no_show',       // cutoff passed, never showed up
        ])->default('pending');
        $table->timestamp('checked_in_at')->nullable();   // when they actually arrived
        $table->timestamps();

        // A resident cannot book the same meal slot twice
        $table->unique(['user_id', 'meal_slot_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
