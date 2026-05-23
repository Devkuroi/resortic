<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuarios ──────────────────────────────────────────────────────
        DB::table('users')->insert([
            ['name'=>'Administrador',       'email'=>'admin@resortic.com',          'password'=>Hash::make('admin123'),   'role'=>'admin',  'status'=>'active', 'created_at'=>now(), 'updated_at'=>now()],
            ['name'=>'Hotel Brisas del Mar','email'=>'brisasdelmar@hotel.com',       'password'=>Hash::make('hotel123'),   'role'=>'hotel',  'status'=>'active', 'created_at'=>now(), 'updated_at'=>now()],
            ['name'=>'Hotel Montaña Azul',  'email'=>'montanaazul@hotel.com',        'password'=>Hash::make('hotel123'),   'role'=>'hotel',  'status'=>'active', 'created_at'=>now(), 'updated_at'=>now()],
            ['name'=>'Ana García',          'email'=>'ana.garcia@gmail.com',         'password'=>Hash::make('cliente123'), 'role'=>'client', 'status'=>'active', 'created_at'=>now(), 'updated_at'=>now()],
            ['name'=>'Jorge Martínez',      'email'=>'jorge.m@outlook.com',          'password'=>Hash::make('cliente123'), 'role'=>'client', 'status'=>'active', 'created_at'=>now(), 'updated_at'=>now()],
        ]);

        // ── Habitaciones ──────────────────────────────────────────────────
        // Hotel Brisas del Mar = id 2
        DB::table('rooms')->insert([
            ['hotel_id'=>2, 'number'=>'101', 'type'=>'single', 'description'=>'Habitación sencilla con vista al jardín.',  'price_per_night'=>120000, 'capacity'=>1, 'status'=>'available',   'created_at'=>now(), 'updated_at'=>now()],
            ['hotel_id'=>2, 'number'=>'102', 'type'=>'double', 'description'=>'Habitación doble con cama queen y balcón.', 'price_per_night'=>180000, 'capacity'=>2, 'status'=>'available',   'created_at'=>now(), 'updated_at'=>now()],
            ['hotel_id'=>2, 'number'=>'201', 'type'=>'suite',  'description'=>'Suite con jacuzzi y vista al mar.',          'price_per_night'=>350000, 'capacity'=>2, 'status'=>'occupied',    'created_at'=>now(), 'updated_at'=>now()],
            ['hotel_id'=>2, 'number'=>'202', 'type'=>'family', 'description'=>'Habitación familiar, dos camas dobles.',     'price_per_night'=>250000, 'capacity'=>4, 'status'=>'available',   'created_at'=>now(), 'updated_at'=>now()],
        ]);

        // Hotel Montaña Azul = id 3
        DB::table('rooms')->insert([
            ['hotel_id'=>3, 'number'=>'A1', 'type'=>'double', 'description'=>'Vista a la montaña, cama king.',    'price_per_night'=>160000, 'capacity'=>2, 'status'=>'available',   'created_at'=>now(), 'updated_at'=>now()],
            ['hotel_id'=>3, 'number'=>'A2', 'type'=>'deluxe', 'description'=>'Suite deluxe con chimenea.',        'price_per_night'=>400000, 'capacity'=>2, 'status'=>'maintenance', 'created_at'=>now(), 'updated_at'=>now()],
            ['hotel_id'=>3, 'number'=>'B1', 'type'=>'family', 'description'=>'Cabaña familiar con terraza.',      'price_per_night'=>280000, 'capacity'=>5, 'status'=>'available',   'created_at'=>now(), 'updated_at'=>now()],
        ]);

        // ── Reservas de ejemplo ───────────────────────────────────────────
        DB::table('reservations')->insert([
            [
                'room_id'=>2, 'client_id'=>4,
                'check_in'=>now()->addDays(3)->format('Y-m-d'),
                'check_out'=>now()->addDays(6)->format('Y-m-d'),
                'guests'=>2, 'total_price'=>540000, 'status'=>'confirmed', 'notes'=>null,
                'created_at'=>now(), 'updated_at'=>now(),
            ],
            [
                'room_id'=>5, 'client_id'=>5,
                'check_in'=>now()->addDays(7)->format('Y-m-d'),
                'check_out'=>now()->addDays(10)->format('Y-m-d'),
                'guests'=>2, 'total_price'=>480000, 'status'=>'pending', 'notes'=>'Llegamos tarde, alrededor de las 11pm.',
                'created_at'=>now(), 'updated_at'=>now(),
            ],
            [
                'room_id'=>1, 'client_id'=>4,
                'check_in'=>now()->subDays(10)->format('Y-m-d'),
                'check_out'=>now()->subDays(8)->format('Y-m-d'),
                'guests'=>1, 'total_price'=>240000, 'status'=>'completed', 'notes'=>null,
                'created_at'=>now()->subDays(15), 'updated_at'=>now()->subDays(8),
            ],
        ]);
    }
}
