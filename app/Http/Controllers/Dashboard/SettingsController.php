<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->role === 'admin', 403);

        $registrationEnabled = SiteSetting::bool('registration_enabled', true);

        return view('settings.index', [
            'registrationEnabled' => $registrationEnabled,
        ]);
    }
}
