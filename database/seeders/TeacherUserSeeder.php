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
            'role_id'           => 2, // Teacher (RolesTableSeeder এ id=2)
            'name'              => 'Course Teacher',
            'email'             => 'teacher@ucms.edu',
            'password'          => Hash::make('password123'),
            'status'            => 'active',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}