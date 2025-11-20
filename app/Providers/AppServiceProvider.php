<?php

namespace App\Providers;

use App\Models\User;
use App\Services\UserNotificationService;
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
        View::composer('layouts.app', function ($view): void {
            $authUser = Auth::user();
            $session = session();

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
}
