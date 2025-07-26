<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Usuario Admin',
            'email' => 'admin@example.com',
            'role' => 'admin', // Rol admin
            'password' => Hash::make('password'),
            'max_simultaneous_reservations' => 10,
        ]);

        User::create([
            'name' => 'Usuario Regular',
            'email' => 'user@example.com',
            'role' => 'user', // Rol user
            'password' => Hash::make('password'),
            'max_simultaneous_reservations' => 5,
        ]);
        
        User::factory()->count(5)->create([
            'role' => 'user',
        ]);
    }
}
