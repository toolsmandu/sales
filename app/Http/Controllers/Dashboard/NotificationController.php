<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function hide(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_id' => ['required', 'string'],
        ]);

        $hidden = $request->session()->get('hidden_notifications', []);
        if (! is_array($hidden)) {
            $hidden = [];
        }
        $hidden[] = $validated['notification_id'];

        $request->session()->put('hidden_notifications', array_values(array_unique($hidden)));

        return response()->json(['status' => 'ok']);
    }

    public function snoozeOverdue(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => ['required', 'integer'],
        ]);

        $request->session()->put('overdue_tasks_next_reminder', Carbon::now()->addHours(3)->toIso8601String());

        return response()->json(['status' => 'ok']);
    }
}
