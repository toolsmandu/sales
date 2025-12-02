<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        $storedSchedule = json_decode(SiteSetting::value('work_schedule_table', '[]'), true);
        $rows = 7;
        $cols = 4;
        $workSchedule = array_fill(0, $rows, array_fill(0, $cols, ''));

        if (is_array($storedSchedule)) {
            foreach ($storedSchedule as $rowIndex => $row) {
                foreach ((array) $row as $colIndex => $value) {
                    if (isset($workSchedule[$rowIndex][$colIndex])) {
                        $workSchedule[$rowIndex][$colIndex] = $value ?? '';
                    }
                }
            }
        }

        $storedRules = json_decode(SiteSetting::value('work_schedule_rules', '[]'), true);
        $workScheduleRules = is_array($storedRules)
            ? collect($storedRules)
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->values()
                ->all()
            : [];

        return view('users.profile', [
            'user' => $user,
            'workSchedule' => $workSchedule,
            'workScheduleRules' => $workScheduleRules,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
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

        $user->fill($validated);
        $user->save();

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('status', 'Password updated successfully.');
    }
}
