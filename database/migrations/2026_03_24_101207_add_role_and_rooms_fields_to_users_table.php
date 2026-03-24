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
    Schema::table('users', function (Blueprint $table) {
        $table->enum('role', ['resident', 'chef', 'admin'])->default('resident')->after('email');
        $table->string('room_number')->nullable()->after('role');
        $table->string('phone')->nullable()->after('room_number');
        $table->uuid('qr_token')->unique()->nullable()->after('phone');
        $table->string('fcm_token')->nullable()->after('qr_token');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['role', 'room_number', 'phone', 'qr_token', 'fcm_token']);
    });
}
};
