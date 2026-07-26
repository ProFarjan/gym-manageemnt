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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('admission_id')->unique();
            $table->string('full_name');
            $table->string('mobile_number');
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('nid_number')->nullable();
            $table->string('nid_image_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('emergency_contact')->nullable();

            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->string('blood_group')->nullable();
            $table->string('fitness_goal')->nullable();
            $table->text('medical_conditions')->nullable();

            $table->foreignId('membership_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'active', 'expired', 'closed'])->default('pending');
            $table->enum('registration_type', ['admin', 'online'])->default('admin');
            $table->date('admission_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('zkteco_user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
