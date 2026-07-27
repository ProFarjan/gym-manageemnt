<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AttendanceHistoryController extends Controller
{
    public function index()
    {
        $member = Auth::guard('member')->user();

        $attendances = $member->attendances()->latest('check_in')->paginate(15);

        return view('member.attendance', compact('attendances'));
    }
}
