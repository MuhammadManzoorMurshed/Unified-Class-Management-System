<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('title',200);
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->dateTime('deadline');
            $table->decimal('max_marks',5,2)->default(100);
            $table->enum('assignment_type',['Homework','Assignment','Lab Report','Project Proposal','Project Report','Project','Thesis']);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('assignments');
    }
};
