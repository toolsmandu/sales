<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\RecordProduct;
use App\Models\Sale;
use App\Models\StockKey;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserNotificationService
{
    public static function buildFor(?User $user, $session = null): array
    {
        if (! $user) {
            return self::emptyPayload();
        }

        if ($user->isAdmin()) {
            return self::buildForAdmin($user, $session);
        }

        if ($user->isEmployee()) {
            return self::buildForEmployee($user, $session);
        }

        return self::emptyPayload();
    }

    protected static function buildForEmployee(User $user, $session = null): array
    {
        $user->loadMissing('employeeSetting');

        $today = Carbon::today();
        $historyStart = $today->copy()->subDays(90)->toDateString();

        $tasks = Task::query()
            ->with(['completions' => fn ($query) => $query->where('completed_on', '>=', $historyStart)])
            ->where('is_active', true)
            ->where('assigned_user_id', $user->id)
            ->get();

        $pendingTodayCount = self::pendingTasksForDate($tasks, $today)->count();
        $overdueCount = self::missedTasks($tasks, $today)->count();
        $shiftWarning = self::shiftWarning($user);

        $items = [];
        $taskLink = route('user-logs.tasks');
        $hidden = $session?->get('hidden_notifications', []) ?? [];

        $todayKey = $today->toDateString();

        if ($pendingTodayCount > 0 && ! in_array('employee_today_' . $todayKey, $hidden, true)) {
            $items[] = [
                'type' => 'today',
                'id' => 'employee_today_' . $todayKey,
                'title' => 'Today\'s tasks',
                'message' => 'You have ' . $pendingTodayCount . ' pending task' . ($pendingTodayCount > 1 ? 's' : '') . ' for today.',
                'link' => $taskLink,
            ];
        }

        if ($overdueCount > 0 && ! in_array('employee_overdue_' . $todayKey, $hidden, true)) {
            $items[] = [
                'type' => 'overdue',
                'id' => 'employee_overdue_' . $todayKey,
                'title' => 'Pending tasks from past',
                'message' => 'You have ' . $overdueCount . ' tasks pending to do.',
                'link' => $taskLink,
            ];
        }

        if ($shiftWarning && ! in_array('shift_' . $todayKey, $hidden, true)) {
            $items[] = [
                'type' => 'shift',
                'id' => 'shift_' . $todayKey,
                'title' => 'Shift reminder',
                'message' => $shiftWarning['message'],
                'current_time' => $shiftWarning['current_time'],
            ];
        }

        $items = array_merge($items, self::recordExpiryNotifications($hidden));

        return [
            'items' => $items,
            'count' => count($items),
        ];
    }

    protected static function buildForAdmin(User $user, $session = null): array
    {
        $today = Carbon::today();
        $timezone = 'Asia/Kathmandu';

        $items = [];
        $hidden = $session?->get('hidden_notifications', []) ?? [];

        $todaySales = Sale::query()
            ->whereDate('created_at', $today->toDateString())
            ->sum('sales_amount');

        $todayKey = $today->toDateString();

        if (! in_array('sales_today_' . $todayKey, $hidden, true)) {
            $items[] = [
                'type' => 'sales',
                'id' => 'sales_today_' . $todayKey,
                'title' => 'Today\'s Sales',
                'message' => 'You have sold total of Rs. ' . number_format((float) $todaySales, 2) . ' today.',
            ];
        }

        $attendanceLogs = AttendanceLog::query()
            ->with('user:id,name')
            ->whereDate('work_date', $today->toDateString())
            ->get();

        foreach ($attendanceLogs as $log) {
            if ($log->started_at) {
                $id = 'attendance_start_' . $log->id;
                if (! in_array($id, $hidden, true)) {
                    $items[] = [
                        'type' => 'employee_start',
                        'id' => $id,
                        'title' => 'Work started',
                        'message' => $log->user->name . ' started work at ' . self::formatTime($log->started_at, $timezone) . '.',
                    ];
                }
            }

            if ($log->ended_at) {
                $id = 'attendance_end_' . $log->id;
                if (! in_array($id, $hidden, true)) {
                    $items[] = [
                        'type' => 'employee_end',
                        'id' => $id,
                        'title' => 'Work ended',
                        'message' => $log->user->name . ' ended work at ' . self::formatTime($log->ended_at, $timezone) . '.',
                    ];
                }
            }
        }

        $stockViews = StockKey::query()
            ->with(['product:id,name', 'viewedBy:id,name'])
            ->whereDate('viewed_at', $today->toDateString())
            ->latest('viewed_at')
            ->get();

        foreach ($stockViews as $view) {
            $viewer = $view->viewedBy?->name ?? 'An employee';
            $product = $view->product->name ?? 'a product';
            $key = $view->activation_key ?? 'N/A';
            $id = 'stock_view_' . $view->id;
            if (! in_array($id, $hidden, true)) {
                $items[] = [
                    'type' => 'stock_view',
                    'id' => $id,
                    'title' => 'Stock key viewed',
                    'message' => "{$viewer} has viewed the {$product}'s key: {$key}",
                ];
            }
        }

        $nextReminderKey = 'overdue_tasks_next_reminder';
        $now = Carbon::now();
        $nextReminderAt = $session?->get($nextReminderKey)
            ? Carbon::parse($session->get($nextReminderKey))
            : null;

        $overdueList = self::overdueTasksByEmployee($today);
        $shouldShowOverdue = ! ($nextReminderAt && $now->lt($nextReminderAt));

        if ($shouldShowOverdue) {
            foreach ($overdueList as $overdue) {
                $items[] = [
                    'type' => 'employee_overdue',
                    'id' => 'admin_overdue_' . $overdue['employee']->id,
                    'title' => 'Pending employee tasks',
                    'message' => "{$overdue['employee']->name} hasn't completed {$overdue['count']} task" . ($overdue['count'] > 1 ? 's' : '') . ' in time. Please tell them to complete the task ASAP.',
                    'employee_id' => $overdue['employee']->id,
                ];
            }
        }

        $items = array_merge($items, self::recordExpiryNotifications($hidden));

        return [
            'items' => $items,
            'count' => count($items),
        ];
    }

    protected static function emptyPayload(): array
    {
        return [
            'items' => [],
            'count' => 0,
        ];
    }

    protected static function pendingTasksForDate(Collection $tasks, Carbon $date): Collection
    {
        return $tasks->filter(function (Task $task) use ($date) {
            if (! $task->isDueOn($date)) {
                return false;
            }

            $checkDate = $task->recurrence_type === Task::RECURRENCE_ONCE
                ? ($task->due_date ?? $date)
                : $date;

            return ! $task->wasCompletedOn($checkDate);
        })->values();
    }

    protected static function missedTasks(Collection $tasks, Carbon $today): Collection
    {
        return $tasks->filter(function (Task $task) use ($today) {
            if ($task->recurrence_type === Task::RECURRENCE_ONCE) {
                return $task->due_date
                    && $task->due_date->lt($today)
                    && ! $task->wasCompletedOn($task->due_date);
            }

            $start = $task->start_date ?? $task->due_date;

            if (! $start || $start->gte($today)) {
                return false;
            }

            $cursor = (clone $today)->subDay();
            $iterations = 0;
            $maxIterations = 60;

            while ($cursor->gte($start) && $iterations < $maxIterations) {
                if ($task->isDueOn($cursor)) {
                    return ! $task->wasCompletedOn($cursor);
                }

                $cursor->subDay();
                $iterations++;
            }

            return false;
        })->values();
    }

    protected static function shiftWarning(User $user): ?array
    {
        $dailyHours = (float) ($user->employeeSetting->daily_hours_quota ?? 0);

        if ($dailyHours <= 0) {
            return null;
        }

        $today = Carbon::today();

        $attendanceLog = AttendanceLog::query()
            ->where('user_id', $user->id)
            ->whereDate('work_date', $today->toDateString())
            ->orderBy('started_at')
            ->first();

        if (! $attendanceLog || ! $attendanceLog->started_at) {
            return null;
        }

        $start = Carbon::parse($attendanceLog->started_at);
        $shiftEnd = $start->copy()->addHours($dailyHours);
        $warningStart = $shiftEnd->copy()->subMinutes(15);
        $warningEnd = $shiftEnd->copy()->addHours(2);
        $now = Carbon::now();

        if ($now->lt($warningStart) || $now->gt($warningEnd)) {
            return null;
        }

        $timezone = 'Asia/Kathmandu';

        return [
            'message' => 'Your shift is about to end in 15 minutes.',
            'current_time' => Carbon::now($timezone)->format('M d, Y g:i A'),
        ];
    }

    protected static function formatTime($timestamp, string $timezone): string
    {
        if (! $timestamp) {
            return '—';
        }

        return Carbon::parse($timestamp)->setTimezone($timezone)->format('M d, Y g:i A');
    }

    protected static function overdueTasksByEmployee(Carbon $today): array
    {
        $results = [];
        $historyStart = $today->copy()->subDays(90)->toDateString();

        $employees = User::query()
            ->where('role', 'employee')
            ->orderBy('name')
            ->get();

        foreach ($employees as $employee) {
            $tasks = Task::query()
                ->with(['completions' => fn ($query) => $query->where('completed_on', '>=', $historyStart)])
                ->where('is_active', true)
                ->where('assigned_user_id', $employee->id)
                ->get();

            $count = self::missedTasks($tasks, $today)->count();

            if ($count > 0) {
                $results[] = [
                    'employee' => $employee,
                    'count' => $count,
                ];
            }
        }

        return $results;
    }

    protected static function recordExpiryNotifications(array $hidden = []): array
    {
        $notifications = [];
        $today = Carbon::today('Asia/Kathmandu');

        $products = RecordProduct::query()
            ->orderBy('name')
            ->get(['id', 'name', 'table_name']);

        foreach ($products as $product) {
            if (! Schema::hasTable($product->table_name)) {
                continue;
            }

            $rows = DB::table($product->table_name)
                ->select(['id', 'product', 'email', 'phone', 'purchase_date', 'expiry'])
                ->whereNotNull('purchase_date')
                ->whereNotNull('expiry')
                ->get();

            foreach ($rows as $row) {
                $remaining = self::calculateRemainingDays($row, $today);
                if ($remaining !== -1) {
                    continue;
                }

                $notificationId = 'record_expiry_' . $product->table_name . '_' . $row->id;
                if (in_array($notificationId, $hidden, true)) {
                    continue;
                }

                $productName = trim((string) ($row->product ?? $product->name));
                $period = is_numeric($row->expiry) ? ((int) $row->expiry . ' Days') : 'N/A';
                $messageLines = [
                    'Subscription of ' . ($productName !== '' ? $productName : 'Unknown product') . ' has just expired.',
                    'Email: ' . ($row->email ?: 'N/A'),
                    'Phone: ' . ($row->phone ?: 'N/A'),
                    'Purchased Period: ' . $period,
                ];

                $notifications[] = [
                    'type' => 'record_expiry',
                    'id' => $notificationId,
                    'title' => 'Subscription expired',
                    'message' => implode(PHP_EOL, $messageLines),
                    'link' => route('sheet.index', [
                        'product' => $product->id,
                        'highlight' => $product->table_name . ':' . $row->id,
                    ]),
                ];
            }
        }

        return $notifications;
    }

    protected static function calculateRemainingDays(object $row, Carbon $today): ?int
    {
        $expiryDays = is_numeric($row->expiry) ? (int) $row->expiry : null;
        if ($expiryDays === null || empty($row->purchase_date)) {
            return null;
        }

        try {
            $purchaseDate = Carbon::parse($row->purchase_date, 'Asia/Kathmandu')->startOfDay();
        } catch (\Exception) {
            return null;
        }

        $expiryDate = $purchaseDate->copy()->addDays($expiryDays);

        return $today->diffInDays($expiryDate, false);
    }
}
