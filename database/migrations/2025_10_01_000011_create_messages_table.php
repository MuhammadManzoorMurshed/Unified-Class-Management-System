<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->enum('message_scope',['private','class','system'])->default('private');
            $table->text('content');
            $table->enum('message_type',['Text','File','Link'])->default('Text');
            $table->string('file_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('messages');
    }
};
