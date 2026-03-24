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
    Schema::create('meal_slots', function (Blueprint $table) {
        $table->id();
        $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
        $table->date('date');
        $table->dateTime('booking_cutoff');      // deadline to book this slot
        $table->dateTime('serving_start');       // when dining hall opens
        $table->dateTime('serving_end');         // when dining hall closes
        $table->integer('max_capacity')->default(200);
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        // A dining hall cannot have two dinners on the same day
        $table->unique(['meal_type', 'date']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_slots');
    }
};
