<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type',50);
            $table->integer('version')->default(1);
            $table->foreignId('parent_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->integer('download_count')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('resources');
    }
};
