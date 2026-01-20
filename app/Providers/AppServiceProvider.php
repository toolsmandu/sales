<?php

namespace App\Providers;

use App\Models\AttendanceLog;
use App\Models\User;
use App\Services\UserNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->syncPublicAsset('logo.png');
        $this->syncPublicAsset('fav.ico');

        View::composer('layouts.app', function ($view): void {
            $authUser = Auth::user();
            $session = session();

            if ($authUser && $authUser->isEmployee()) {
                $authUser->loadMissing('employeeSetting');
                $dailyHours = (int) ($authUser->employeeSetting->daily_hours_quota ?? 0);
                if ($dailyHours > 0) {
                    $activeLog = AttendanceLog::where('user_id', $authUser->id)
                        ->whereNull('ended_at')
                        ->latest('started_at')
                        ->first();
                    if ($activeLog && $activeLog->started_at) {
                        $now = Carbon::now('Asia/Kathmandu');
                        $minutes = AttendanceLog::minutesBetween($activeLog->started_at, $now);
                        if ($minutes >= $dailyHours * 60) {
                            $activeLog->update([
                                'ended_at' => $now,
                                'total_minutes' => $minutes,
                            ]);
                        }
                    }
                }
            }

            $view->with('headerNotifications', UserNotificationService::buildFor($authUser, $session));

            $impersonationEmployees = ($authUser && $authUser->isAdmin())
                ? User::query()
                    ->where('role', 'employee')
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : collect();

            $view->with('impersonationEmployees', $impersonationEmployees);
            $view->with('isImpersonating', $session->has('impersonator_id'));
            $view->with('impersonatorName', $session->get('impersonator_name'));
        });
    }

    /**
     * Ensure a root-level asset is available under public/ for serving.
     */
    protected function syncPublicAsset(string $filename): void
    {
        $source = base_path($filename);
        $target = public_path($filename);

        if (! file_exists($source)) {
            return;
        }

        $needsCopy = ! file_exists($target) || filemtime($source) > filemtime($target);

        if ($needsCopy) {
            @copy($source, $target);
        }
    }
}
