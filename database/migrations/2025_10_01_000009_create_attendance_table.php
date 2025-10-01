<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status',['present','absent','late','excused'])->default('present');
            $table->foreignId('marked_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['class_id','student_id','date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('attendance');
    }
};
