<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relationship_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relationship_id')->constrained('relationships');
            $table->string('invitee_email');
            $table->foreignId('invitee_user_id')->nullable()->constrained('users');
            $table->enum('role', ['collaborator', 'viewer']);
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired']);
            $table->string('token')->unique();
            $table->timestamp('expired_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relationship_invitations');
    }
};