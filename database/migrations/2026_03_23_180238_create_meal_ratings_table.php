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
    Schema::create('meal_ratings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('meal_slot_id')->constrained()->cascadeOnDelete();
        $table->unsignedTinyInteger('rating');      // 1–5
        $table->text('comment')->nullable();
        $table->timestamps();

        // One rating per resident per meal slot
        $table->unique(['user_id', 'meal_slot_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_ratings');
    }
};
