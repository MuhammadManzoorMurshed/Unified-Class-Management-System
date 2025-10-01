<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'role_name' => 'Admin',
                'permissions' => json_encode([
                    'users' => ['create','read','update','delete'],
                    'classes' => ['create','read','update','delete'],
                    'system' => ['manage']
                ]),
                'description' => 'System Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_name' => 'Teacher',
                'permissions' => json_encode([
                    'classes' => ['create','read','update'],
                    'assignments' => ['create','read','update','delete'],
                    'attendance' => ['create','read'],
                    'marks' => ['create','read','update']
                ]),
                'description' => 'Course Teacher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_name' => 'Student',
                'permissions' => json_encode([
                    'classes' => ['read'],
                    'assignments' => ['read','submit'],
                    'attendance' => ['read'],
                    'marks' => ['read']
                ]),
                'description' => 'Student User',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
