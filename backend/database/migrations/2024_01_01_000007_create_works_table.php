<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained('drafts');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('relationship_id')->constrained('relationships');
            $table->enum('status', ['pending_review', 'published', 'rejected']);
            $table->string('title');
            $table->text('content');
            $table->json('tags')->nullable();
            $table->foreignId('cover_resource_id')->nullable()->constrained('resources');
            $table->integer('likes_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->json('moderation_result')->nullable(); // Content moderation results
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'published_at']);
            $table->index(['user_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};