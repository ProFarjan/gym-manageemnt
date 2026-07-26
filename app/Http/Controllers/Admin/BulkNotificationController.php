<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Notifications\BulkMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BulkNotificationController extends Controller
{
    public function create()
    {
        return view('admin.bulk-notifications.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_status' => ['required', 'in:all,pending,active,expired,closed'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['in:mail,sms'],
        ]);

        $members = Member::query()
            ->when($data['target_status'] !== 'all', fn ($q) => $q->where('status', $data['target_status']))
            ->get();

        Notification::send($members, new BulkMessageNotification($data['subject'], $data['body'], $data['channels']));

        return redirect()->route('admin.bulk-notifications.create')
            ->with('status', "Queued message to {$members->count()} member(s).");
    }
}
