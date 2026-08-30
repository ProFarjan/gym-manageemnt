<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Locker rent is deliberately its own payment type, not reused
     * "admission" — PaymentRecorder::record() treats every MEMBERSHIP_TYPES
     * entry as a membership billing cycle (activates a Pending member,
     * extends due_date, etc.); a locker payment must never trigger any of
     * that, the same way the existing (currently unused) "personal_training"
     * type already doesn't.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN type ENUM('admission', 'monthly', 'package', 'renewal', 'personal_training', 'locker') NOT NULL DEFAULT 'monthly'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN type ENUM('admission', 'monthly', 'package', 'renewal', 'personal_training') NOT NULL DEFAULT 'monthly'");
    }
};
