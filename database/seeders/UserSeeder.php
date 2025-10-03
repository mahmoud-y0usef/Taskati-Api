<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create verified test user
        User::create([
            'name' => 'Verified User',
            'email' => 'mahmoudyousef59@outlook.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create unverified test user for testing email verification
        User::create([
            'name' => 'Unverified User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null, // Not verified
        ]);
    }
}
