<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained('works');
            $table->date('featured_date');
            $table->foreignId('selected_by_user_id')->constrained('users');
            $table->timestamps();
            
            $table->unique('featured_date');
            $table->index('featured_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_photos');
    }
};