<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->enum('exam_type',['CT','Midterm','Final','Quiz','Viva','Lab Performance','Presentation']);
            $table->string('title',150);
            $table->date('exam_date');
            $table->decimal('total_marks',5,2);
            $table->decimal('weightage',3,2)->default(1.0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('exams');
    }
};
