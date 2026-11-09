<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat Akun Admin Master
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin', // Role diset manual jadi admin
        ]);

        // Membuat Akun Contoh Pelanggan
        User::create([
            'name' => 'Hansen',
            'email' => 'hansen@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
        ]);
        User::create([
            'name' => 'Daniel',
            'email' => 'daniel@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
        ]);
        User::create([
            'name' => 'Tantio',
            'email' => 'tantio@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
        ]);
        User::create([
            'name' => 'HansenDT',
            'email' => 'hansendt@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'customer',
        ]);
    }
}