<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type', ['membership', 'shutter_time']);
            $table->integer('amount'); // Amount in cents
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded']);
            $table->string('stripe_session_id')->nullable();
            $table->string('payment_method')->nullable(); // 'wechat', 'alipay', 'apple'
            $table->json('metadata')->nullable(); // Additional order data
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};