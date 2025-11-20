<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $message = $this->startWorkSession($user)
            ? 'Attendance started. Have a productive day!'
            : 'You already have an active work session.';

        return back()->with('status', $message);
    }

    public function end(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $message = $this->endWorkSession($user)
            ? 'Great job! Work session recorded.'
            : 'No active work session found.';

        return back()->with('status', $message);
    }

    public function startForEmployee(Request $request, User $user): RedirectResponse
    {
        $this->authorizeEmployeeManagement($request, $user);

        $message = $this->startWorkSession($user)
            ? "{$user->name}'s attendance has been started."
            : "{$user->name} already has an active work session.";

        return redirect()
            ->route('dashboard.users.index', ['edit' => $user->getKey()])
            ->with('status', $message);
    }

    public function endForEmployee(Request $request, User $user): RedirectResponse
    {
        $this->authorizeEmployeeManagement($request, $user);

        $message = $this->endWorkSession($user)
            ? "{$user->name}'s work session has been recorded."
            : "{$user->name} does not have an active work session.";

        return redirect()
            ->route('dashboard.users.index', ['edit' => $user->getKey()])
            ->with('status', $message);
    }

    private function authorizeEmployeeManagement(Request $request, User $user): void
    {
        /** @var User|null $actingUser */
        $actingUser = $request->user();

        abort_unless($actingUser && $actingUser->isAdmin(), 403);
        abort_unless($user->isEmployee(), 403);
    }

    private function startWorkSession(User $user): bool
    {
        if ($this->activeAttendanceLog($user)) {
            return false;
        }

        AttendanceLog::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'started_at' => Carbon::now(),
        ]);

        return true;
    }

    private function endWorkSession(User $user): bool
    {
        $log = $this->activeAttendanceLog($user);

        if (!$log || !$log->started_at) {
            return false;
        }

        $endTime = Carbon::now();
        $totalMinutes = AttendanceLog::minutesBetween($log->started_at, $endTime);

        $log->update([
            'ended_at' => $endTime,
            'total_minutes' => $totalMinutes,
        ]);

        return true;
    }

    private function activeAttendanceLog(User $user): ?AttendanceLog
    {
        return AttendanceLog::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }
}
