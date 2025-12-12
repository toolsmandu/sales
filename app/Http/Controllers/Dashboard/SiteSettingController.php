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

    public function updateWorkSchedule(Request $request): RedirectResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'work_schedule' => ['required', 'array', 'size:7'],
            'work_schedule.*' => ['required', 'array', 'size:4'],
            'work_schedule.*.*' => ['nullable', 'string', 'max:255'],
            'work_schedule_rules' => ['nullable', 'string', 'max:2000'],
        ]);

        $schedule = array_map(
            fn (array $row) => array_map(static fn ($cell) => $cell ?? '', $row),
            $validated['work_schedule'],
        );

        $rules = collect(preg_split("/\r\n|\r|\n/", $validated['work_schedule_rules'] ?? ''))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        SiteSetting::set('work_schedule_table', json_encode($schedule));
        SiteSetting::set('work_schedule_rules', json_encode($rules));

        return back()->with('status', 'Work schedule saved.');
    }

    public function editLogin(Request $request)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403);
        }

        return view('dashboard.login-settings', [
            'loginContent' => \App\Support\LoginContent::current(),
        ]);
    }

    public function updateLogin(Request $request): RedirectResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'badge' => ['required', 'string', 'max:150'],
            'brand_accent' => ['nullable', 'string', 'max:80'],
            'headline_prefix' => ['required', 'string', 'max:150'],
            'headline_accent' => ['required', 'string', 'max:150'],
            'headline_suffix' => ['required', 'string', 'max:150'],
            'lead' => ['required', 'string', 'max:400'],
            'perks' => ['nullable', 'string', 'max:600'],
            'card_title' => ['required', 'string', 'max:180'],
        ]);

        $currentContent = \App\Support\LoginContent::current();

        $logoPath = $currentContent['logo_path'] ?? null;

        $perks = array_values(array_filter(array_map(
            static fn ($line) => trim((string) $line),
            preg_split("/\r\n|\r|\n/", $validated['perks'] ?? '') ?: []
        )));

        $content = [
            'badge' => $validated['badge'],
            'brand_accent' => $validated['brand_accent'] ?? ($currentContent['brand_accent'] ?? null),
            'headline_prefix' => $validated['headline_prefix'],
            'headline_accent' => $validated['headline_accent'],
            'headline_suffix' => $validated['headline_suffix'],
            'lead' => $validated['lead'],
            'perks' => $perks,
            'card_title' => $validated['card_title'],
            'logo_path' => $logoPath,
        ];

        SiteSetting::set('login_content', json_encode($content));

        return back()->with('status', 'Login page content updated.');
    }
}
