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
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('admission_fee_amount', 10, 2)->default(0)->after('membership_plan_id');
            $table->decimal('monthly_amount', 10, 2)->default(0)->after('admission_fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['admission_fee_amount', 'monthly_amount']);
        });
    }
};
