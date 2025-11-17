<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Admin global
        User::create([
            'name' => 'Admin SaaS',
            'email' => 'admin@locasaas.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 🔹 Quelques propriétaires
        User::factory()->count(5)->create([
            'role' => 'owner',
        ]);

        // 🔹 Quelques agents
        User::factory()->count(5)->create([
            'role' => 'agent',
        ]);
    }
}

