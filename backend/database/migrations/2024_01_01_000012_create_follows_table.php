<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_user_id')->constrained('users');
            $table->foreignId('followed_user_id')->constrained('users');
            $table->timestamps();
            
            $table->unique(['follower_user_id', 'followed_user_id']);
            $table->index(['followed_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};