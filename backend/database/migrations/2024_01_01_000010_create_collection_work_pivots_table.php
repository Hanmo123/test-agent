<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_work_pivots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections');
            $table->foreignId('work_id')->constrained('works');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['collection_id', 'work_id']);
            $table->index(['collection_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_work_pivots');
    }
};