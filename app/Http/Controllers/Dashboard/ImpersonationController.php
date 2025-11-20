<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ImpersonationController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        /** @var User|null $admin */
        $admin = $request->user();
        abort_unless($admin && $admin->isAdmin(), 403);

        abort_if($request->session()->has('impersonator_id'), 400, 'Already impersonating a user.');

        $validated = $request->validate([
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'employee')),
            ],
        ]);

        $employee = User::query()
            ->where('role', 'employee')
            ->findOrFail($validated['employee_id']);

        $request->session()->put('impersonator_id', $admin->getKey());
        $request->session()->put('impersonator_name', $admin->name);
        Auth::login($employee);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', "You are now impersonating {$employee->name}.");
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');
        $request->session()->forget('impersonator_name');

        abort_if(! $impersonatorId, 400, 'No impersonation in progress.');

        $admin = User::query()->find($impersonatorId);
        abort_unless($admin, 404);

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Returned to your admin account.');
    }
}
