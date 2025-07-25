<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('cover_resource_id')->nullable()->constrained('resources');
            $table->boolean('is_public')->default(true);
            $table->integer('subscribers_count')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['is_public', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};