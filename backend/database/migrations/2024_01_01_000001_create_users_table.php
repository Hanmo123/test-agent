<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('logto_id')->unique()->index();
            $table->string('nickname')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar')->nullable();
            $table->integer('level')->default(0); // 0: unverified, 1: basic, X: advanced
            $table->integer('likes_received')->default(0);
            $table->integer('followers_count')->default(0);
            $table->integer('following_count')->default(0);
            $table->enum('visibility', ['public', 'followers_only', 'private'])->default('public');
            $table->boolean('is_student')->default(false);
            $table->timestamp('student_verified_at')->nullable();
            $table->boolean('is_member')->default(false);
            $table->timestamp('member_expires_at')->nullable();
            $table->json('preferences')->nullable(); // watermark, theme, notifications
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};