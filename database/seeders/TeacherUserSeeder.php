<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'role_id'           => 2,
                'name'              => 'Estiak Ahmed Sazid',
                'email'             => 'estiakahmedsazid@puc.edu',
                'password'          => Hash::make('estiakahmedsazid'),
                'status'            => 'active',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'role_id'           => 2,
                'name'              => 'Syed Minhaz Hussain',
                'email'             => 'syedminhazhussain@puc.edu',
                'password'          => Hash::make('syedminhazhussain'),
                'status'            => 'active',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'role_id'           => 2,
                'name'              => 'Kingshuk Dhar',
                'email'             => 'kingshukdhar@puc.edu',
                'password'          => Hash::make('kingshukdhar'),
                'status'            => 'active',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'role_id'           => 2,
                'name'              => 'Md. Mehedi Hasan',
                'email'             => 'mdmehedihasan@puc.edu',
                'password'          => Hash::make('mdmehedihasan'),
                'status'            => 'active',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}