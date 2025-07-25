<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('relationship_id')->constrained('relationships');
            $table->string('filename');
            $table->string('oss_key'); // OSS object key
            $table->enum('type', ['image', 'video'])->default('image');
            $table->json('exif_json')->nullable(); // EXIF data
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->bigInteger('size'); // File size in bytes
            $table->string('mime_type');
            $table->json('processed_sizes')->nullable(); // thumbnail, display, hd, original URLs
            $table->boolean('processing_complete')->default(false);
            $table->boolean('hide_exif')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['relationship_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};