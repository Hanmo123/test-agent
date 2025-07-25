<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('work_id')->constrained('works');
            $table->timestamps();
            
            $table->unique(['user_id', 'work_id']);
            $table->index(['work_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};