<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KepalaTUSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kepala.tu@smkn4.com'],
            [
                'name' => 'Kepala Tata Usaha',
                'password' => Hash::make('password123'),
                'role' => 'kepala_tu',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
    }
}
