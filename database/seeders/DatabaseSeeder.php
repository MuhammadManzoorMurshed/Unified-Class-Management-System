<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // রোল → অ্যাডমিন → (ঐচ্ছিক) স্যাম্পল ইউজার
        $this->call([
            RolesTableSeeder::class,
            AdminUserSeeder::class,
            SampleUsersSeeder::class, // চাইলে এটা বাদও দিতে পারেন
        ]);

        // যদি পুরনো factory-based Test User রাখতে চান, এখানে রাখতে পারেন
        // \App\Models\User::factory()->create([
        //     'name'  => 'Test User',
        //     'email' => 'test@example.com',
        //     'role_id' => 3, // Student
        // ]);
    }
}
