<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;

class AttendanceController extends Controller
{
    public function checkIn(Member $member)
    {
        if ($member->status !== 'active') {
            return back()->withErrors(['attendance' => 'Only Active members can be checked in.']);
        }

        if ($member->openAttendance()) {
            return back()->withErrors(['attendance' => 'This member is already checked in.']);
        }

        $member->attendances()->create([
            'check_in' => now(),
            'source' => 'manual',
        ]);

        return back()->with('status', 'Checked in.');
    }

    public function checkOut(Member $member)
    {
        $attendance = $member->openAttendance();

        if (! $attendance) {
            return back()->withErrors(['attendance' => 'This member is not currently checked in.']);
        }

        $attendance->check_out = now();
        $attendance->duration_minutes = $attendance->check_in->diffInMinutes($attendance->check_out);
        $attendance->save();

        return back()->with('status', 'Checked out.');
    }
}
