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
        Schema::table('zkteco_commands', function (Blueprint $table) {
            // Links a Local-Service-mode command back to the ZKTecoSyncLog
            // row that queued it (Member status changes -> MemberObserver ->
            // ZKTecoSyncLog -> here), so ZKTecoSyncController::sync() can
            // reflect the Windows service's reported result back onto that
            // log once it comes in. Null for commands queued directly by an
            // admin from Settings > ZKTeco (no originating sync log).
            $table->foreignId('zkteco_sync_log_id')->nullable()->after('id')->constrained('zkteco_sync_logs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zkteco_commands', function (Blueprint $table) {
            $table->dropConstrainedForeignId('zkteco_sync_log_id');
        });
    }
};
