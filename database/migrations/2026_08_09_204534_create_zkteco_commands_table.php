<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zkteco_commands', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['create_user', 'update_user', 'delete_user', 'list_users']);
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'sent', 'completed', 'failed'])->default('pending');
            $table->json('result')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zkteco_commands');
    }
};
