<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title')->nullable();
            $table->json('content_json'); // Rich text content and resource references
            $table->foreignId('cover_resource_id')->nullable()->constrained('resources');
            $table->json('tags')->nullable(); // Array of tags
            $table->json('history_json')->nullable(); // Auto-save history (last 20 versions)
            $table->timestamps();
            
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};