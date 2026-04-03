<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'chef@mealtrack.com'],
            [
                'name' => 'Chef Portal',
                'password' => Hash::make('password'),
                'role' => 'chef',
                'qr_token' => (string) Str::uuid(),
                'room_number' => null,
                'phone' => null,
                'fcm_token' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@mealtrack.com'],
            [
                'name' => 'Admin Portal',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'qr_token' => (string) Str::uuid(),
                'room_number' => null,
                'phone' => null,
                'fcm_token' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'resident',
                'qr_token' => (string) Str::uuid(),
                'room_number' => 'A-101',
                'phone' => null,
                'fcm_token' => null,
            ]
        );
    }
}
