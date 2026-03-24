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
    Schema::create('meal_plans', function (Blueprint $table) {
        $table->id();
        $table->string('name');                      // e.g. "Full Board", "Half Board"
        $table->integer('credit_amount');            // how many credits this plan gives
        $table->decimal('price', 10, 2);             // cost in USD or ZWL
        $table->text('description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
