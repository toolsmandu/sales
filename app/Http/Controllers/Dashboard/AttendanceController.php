<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $openLog = AttendanceLog::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($openLog) {
            return back()->with('status', 'You already have an active work session.');
        }

        AttendanceLog::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'started_at' => Carbon::now(),
        ]);

        return back()->with('status', 'Attendance started. Have a productive day!');
    }

    public function end(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $log = AttendanceLog::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('work_date')
            ->first();

        if (!$log || !$log->started_at) {
            return back()->with('status', 'No active work session found.');
        }

        $endTime = Carbon::now();
        $totalMinutes = AttendanceLog::minutesBetween($log->started_at, $endTime);

        $log->update([
            'ended_at' => $endTime,
            'total_minutes' => $totalMinutes,
        ]);

        return back()->with('status', 'Great job! Work session recorded.');
    }
}
