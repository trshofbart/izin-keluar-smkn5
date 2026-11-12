<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun admin default. GANTI PASSWORD setelah instalasi pertama!
        // Lihat README.md / LARAGON_SETUP.md.
        User::firstOrCreate(
            ['email' => 'admin@smkn5.sch.id'],
            [
                'name' => 'Admin SMKN 5 Surakarta',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
            ]
        );

        // Akun satpam default untuk halaman /satpam. GANTI PASSWORD juga!
        User::firstOrCreate(
            ['email' => 'satpam@smkn5.sch.id'],
            [
                'name' => 'Satpam SMKN 5 Surakarta',
                'password' => Hash::make('satpam12345'),
                'role' => 'satpam',
            ]
        );
    }
}
