<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('marks_obtained',5,2);
            $table->string('grade',5)->nullable();
            $table->foreignId('entered_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['exam_id','student_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('marks');
    }
};
