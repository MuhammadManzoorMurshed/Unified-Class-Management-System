<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('subject', 255);
            $table->string('semester', 50);
            $table->year('year');
            $table->boolean('is_active')->default(true);
            $table->integer('max_students')->default(50);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('classes');
    }
};
