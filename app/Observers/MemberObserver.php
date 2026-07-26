<?php

namespace App\Observers;

use App\Jobs\SyncMemberToZKTeco;
use App\Models\Member;
use App\Models\ZKTecoSyncLog;
use App\Notifications\RegistrationConfirmation;

class MemberObserver
{
    public function created(Member $member): void
    {
        if ($member->status === 'active') {
            $this->queueSync($member, 'create_user');
        }

        $member->notify(new RegistrationConfirmation);
    }

    public function updated(Member $member): void
    {
        if ($member->wasChanged('status')) {
            $previous = $member->getOriginal('status');
            $current = $member->status;

            match (true) {
                $current === 'active' && $previous === 'pending' => $this->queueSync($member, 'create_user'),
                $current === 'active' => $this->queueSync($member, 'update_user'),
                $current === 'expired' => $this->queueSync($member, 'disable_user'),
                $current === 'closed' => $this->queueSync($member, 'delete_user'),
                default => null,
            };

            return;
        }

        if ($member->status === 'active' && $member->wasChanged(['full_name', 'photo_path'])) {
            $this->queueSync($member, 'update_user');
        }
    }

    private function queueSync(Member $member, string $action): void
    {
        $log = ZKTecoSyncLog::create([
            'member_id' => $member->id,
            'action' => $action,
            'status' => 'pending',
        ]);

        SyncMemberToZKTeco::dispatch($log->id);
    }
}
