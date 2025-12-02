<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $actingUser */
        $actingUser = $request->user();

        abort_unless($actingUser->role === 'admin', 403);

        $teamMembers = User::query()
            ->with('employeeSetting')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $activeAttendanceLogs = AttendanceLog::query()
            ->whereNull('ended_at')
            ->whereIn('user_id', $teamMembers->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $employeeToEdit = null;
        $editId = $request->integer('edit');

        if ($editId) {
            $employeeToEdit = User::query()
                ->with('employeeSetting')
                ->where('role', 'employee')
                ->find($editId);

            if (! $employeeToEdit) {
                abort(404);
            }
        }

        $storedSchedule = json_decode(SiteSetting::value('work_schedule_table', '[]'), true);
        $workSchedule = array_fill(0, 7, array_fill(0, 4, ''));

        if (is_array($storedSchedule)) {
            foreach ($storedSchedule as $rowIndex => $row) {
                foreach ($row as $colIndex => $value) {
                    if (isset($workSchedule[$rowIndex][$colIndex])) {
                        $workSchedule[$rowIndex][$colIndex] = $value ?? '';
                    }
                }
            }
        }

        return view('users.manage', [
            'teamMembers' => $teamMembers,
            'employeeToEdit' => $employeeToEdit,
            'activeAttendanceLogs' => $activeAttendanceLogs,
            'workSchedule' => $workSchedule,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        /** @var User $actingUser */
        $actingUser = $request->user();

        abort_unless($actingUser->role === 'admin', 403);
        abort_unless($user->role === 'employee', 403);

        $validated = $request->validateWithBag('employeeUpdate', [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->getKey()),
            ],
        ]);

        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill($validated)->save();

        return redirect()
            ->route('dashboard.users.index', ['edit' => $user->getKey()])
            ->with('status', 'Employee profile updated successfully.');
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        /** @var User $actingUser */
        $actingUser = $request->user();

        abort_unless($actingUser->role === 'admin', 403);
        abort_unless($user->role === 'employee', 403);

        $validated = $request->validateWithBag('employeePassword', [
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->route('dashboard.users.index', ['edit' => $user->getKey()])
            ->with('status', 'Employee password updated successfully.');
    }

}
