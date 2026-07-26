<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;

class SyncMembershipStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-membership-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire overdue Active members and Close Expired members unpaid for 3+ months';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = Member::where('status', 'active')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->get();

        foreach ($expired as $member) {
            $member->update(['status' => 'expired']);
        }

        $closed = Member::where('status', 'expired')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today()->subMonths(3))
            ->get();

        foreach ($closed as $member) {
            $member->update(['status' => 'closed', 'closed_at' => now()]);
        }

        $this->info(count($expired).' member(s) marked Expired, '.count($closed).' member(s) marked Closed.');

        return self::SUCCESS;
    }
}
