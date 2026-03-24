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
    Schema::create('credit_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->enum('type', [
            'purchase',      // admin tops up credits
            'reserve',       // resident books a meal
            'deduct',        // resident checks in OR is marked no-show
            'release',       // resident cancels booking
            'admin_adjust',  // manual correction
        ]);
        $table->integer('amount');              // always positive — type tells the direction
        $table->integer('balance_after');       // snapshot of balance after this transaction
        $table->text('notes')->nullable();      // e.g. "No-show: dinner 2025-03-23"
        $table->foreignId('booking_id')
               ->nullable()
               ->constrained()
               ->nullOnDelete();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
