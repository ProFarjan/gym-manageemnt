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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->constrained()->restrictOnDelete();

            $table->enum('type', ['admission', 'monthly', 'package', 'renewal', 'personal_training'])->default('monthly');
            $table->enum('method', ['manual', 'bkash', 'nagad'])->default('manual');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->string('transaction_reference')->nullable();

            $table->enum('status', ['completed', 'refunded'])->default('completed');
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->string('receipt_number')->unique()->nullable();
            $table->string('invoice_number')->unique()->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
