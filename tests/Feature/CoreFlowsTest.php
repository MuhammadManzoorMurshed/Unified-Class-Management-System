<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CoreFlowsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * প্রতিটি টেস্টের আগে:
     *  - roles টেবিল seed হবে (Admin/Teacher/Student)
     *  - JWT secret সেট হবে যাতে jwt-auth ক্র্যাশ না করে
     */
    protected function setUp(): void
    {
        parent::setUp();

        // RolesTableSeeder রান করি (Admin/Teacher/Student)
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\RolesTableSeeder::class,
        ]);

        // টেস্ট env এর জন্য JWT secret ফোর্স করে দিচ্ছি
        config(['jwt.secret' => '12345678901234567890123456789012']);
    }

    /**
     * 1) Logged in user home page এ 200 পায়
     */
    public function test_home_page_works_for_logged_in_user()
    {
        /** @var \App\Models\User $user */
        
        $user = User::factory()->create([
            'role_id' => 2, // Teacher
        ]);

        // '/' না, main app shell route hit করবো
        $response = $this->actingAs($user)->get('/classes');

        $response->assertStatus(200);
    }


    /**
     * 2) Login API ঠিকমতো কাজ করে
     */
    public function test_login_api_returns_token_for_valid_credentials()
    {
        $password = 'secret123';

        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt($password),
            'role_id'  => 2, // Teacher
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    /**
     * 3) Student role ক্লাস তৈরি করতে পারে না
     */
    public function test_student_cannot_create_class()
    {
        /** @var \App\Models\User $student */
        
        $student = User::factory()->create([
            'role_id' => 3, // Student
        ]);

        // Classes টেবিলের স্কিমা অনুযায়ী payload
        $payload = [
            'name'         => 'Test Class',
            'code'         => 'ABC123',
            'subject'      => 'Test Subject',
            'semester'     => 'Spring',
            'year'         => 2025,
            'max_students' => 50,
        ];

        // এই রুটে সাধারণত auth:api + jwt guard ইউজ করা হচ্ছে, তাই guard='api'
        $response = $this->actingAs($student, 'api')
            ->postJson('/api/v1/classes', $payload);

        // Student হলে 403 পাওয়া উচিত
        $response->assertStatus(403);
    }

    /**
     * 4) Student শুধু নিজের enrolled class দেখে
     */
    public function test_student_sees_only_enrolled_classes()
    {
        // একজন Teacher, দুইজন Student
        $teacher = User::factory()->create([
            'role_id' => 2, // Teacher
        ]);

        $student = User::factory()->create(['role_id' => 3]);
        $other   = User::factory()->create(['role_id' => 3]);

        // Factory ছাড়া সরাসরি DB দিয়ে ক্লাস তৈরি করলাম
        // (ClassesFactory নেই বলে আগের এরর হচ্ছিল)
        $classAId = DB::table('classes')->insertGetId([
            'teacher_id'   => $teacher->id,
            'name'         => 'Class A',
            'code'         => 'CLASSA',
            'subject'      => 'Subject A',
            'semester'     => 'Spring',
            'year'         => 2025,
            'is_active'    => true,
            'max_students' => 50,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $classBId = DB::table('classes')->insertGetId([
            'teacher_id'   => $teacher->id,
            'name'         => 'Class B',
            'code'         => 'CLASSB',
            'subject'      => 'Subject B',
            'semester'     => 'Spring',
            'year'         => 2025,
            'is_active'    => true,
            'max_students' => 50,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Enrollments টেবিলে relation সেট করছি
        DB::table('enrollments')->insert([
            'user_id'        => $student->id,
            'class_id'       => $classAId,
            'status'         => 'active',
            'enrollment_date' => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('enrollments')->insert([
            'user_id'        => $other->id,
            'class_id'       => $classBId,
            'status'         => 'active',
            'enrollment_date' => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        
        /** @var \App\Models\User $student */
        
        // Student হিসেবে লগইন (api guard, কারণ রুটে auth:api থাকার কথা)
        $response = $this->actingAs($student, 'api')
            ->getJson('/api/v1/my-classes');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $classAId])   // নিজের ক্লাস
            ->assertJsonMissing(['id' => $classBId]);   // অন্যজনের ক্লাস আসবে না
    }
}