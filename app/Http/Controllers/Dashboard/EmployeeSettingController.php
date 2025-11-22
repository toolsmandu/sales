<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmployeeSettingController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $authUser = $request->user();
        abort_unless($authUser && $authUser->isAdmin(), 403);

        if (!$user->isEmployee()) {
            return back()->with('status', 'Settings can only be updated for employees.');
        }

        $validated = $request->validateWithBag('employeeSettings', [
            'daily_hours_quota' => ['required', 'integer', 'min:0'],
        ]);

        EmployeeSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'daily_hours_quota' => $validated['daily_hours_quota'],
            ],
        );

        return back()->with('status', 'Employee settings updated.');
    }
}
