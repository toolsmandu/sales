<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function updateRegistration(Request $request): RedirectResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'registration_enabled' => ['required', 'in:1,0'],
        ]);

        SiteSetting::set('registration_enabled', $validated['registration_enabled']);

        return back()->with(
            'status',
            $validated['registration_enabled'] === '1'
                ? 'User registration has been enabled.'
                : 'User registration has been disabled.'
        );
    }
}
