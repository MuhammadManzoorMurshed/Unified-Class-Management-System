<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // 'class.join', 'class.removeMember' ইত্যাদি
            $table->string('entity_type'); // 'Class', 'Enrollment'
            $table->unsignedBigInteger('entity_id'); // ক্লাস বা এনরোলমেন্ট ID
            $table->json('meta')->nullable(); // অতিরিক্ত ডেটা
            $table->unsignedBigInteger('user_id'); // ইউজারের ID
            $table->string('ip_address'); // ইউজারের আইপি অ্যাড্রেস
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};