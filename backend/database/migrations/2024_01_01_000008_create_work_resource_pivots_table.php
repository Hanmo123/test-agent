<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_resource_pivots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained('works');
            $table->foreignId('resource_id')->constrained('resources');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['work_id', 'resource_id']);
            $table->index(['work_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_resource_pivots');
    }
};