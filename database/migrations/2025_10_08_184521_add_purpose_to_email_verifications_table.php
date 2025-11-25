<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->enum('purpose', ['email_verify', 'password_reset'])
                  ->default('email_verify')
                  ->after('is_used');
        });
    }

    public function down(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};
