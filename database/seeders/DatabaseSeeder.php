<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name'       => 'Administrador',
                'email'      => 'admin@resortic.com',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Hotel Brisas del Mar',
                'email'      => 'brisasdelmar@hotel.com',
                'password'   => Hash::make('hotel123'),
                'role'       => 'hotel',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Ana García',
                'email'      => 'ana.garcia@gmail.com',
                'password'   => Hash::make('cliente123'),
                'role'       => 'client',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
