<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'role_id'           => 1, // Admin (RolesTableSeeder এ id=1 ধরে নিচ্ছি)
            'name'              => 'System Administrator',
            'email'             => 'admin@ucms.edu',
            'password'          => Hash::make('password123'), // default password
            'status'            => 'active',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
