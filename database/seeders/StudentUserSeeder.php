<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'role_id'           => 3, // Student (RolesTableSeeder এ id=3)
            'name'              => 'Regular Student',
            'email'             => 'student@ucms.edu',
            'password'          => Hash::make('password123'),
            'status'            => 'active',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}