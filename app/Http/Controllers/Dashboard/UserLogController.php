<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UserLogController extends Controller
{
    public function attendance(Request $request): View
    {
        return $this->renderSection($request, 'attendance');
    }

    public function tasks(Request $request): View
    {
        return $this->renderSection($request, 'tasks');
    }

    protected function renderSection(Request $request, string $section): View
    {
        $authUser = $request->user();
        abort_unless($authUser, 403);

        $now = Carbon::now();
        $month = (int) ($request->input('month') ?: $now->month);
        $year = (int) ($request->input('year') ?: $now->year);
        $month = max(1, min(12, $month));
        $year = $year > 0 ? $year : $now->year;

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = (clone $periodStart)->endOfMonth();
        $selectedEmployeeId = $request->integer('employee_id');

        $attendanceQuery = AttendanceLog::with(['user.employeeSetting'])
            ->forMonthYear($month, $year)
            ->orderByDesc('work_date');

        if ($authUser->isEmployee()) {
            $attendanceQuery->where('user_id', $authUser->id);
        } elseif ($selectedEmployeeId) {
            $attendanceQuery->where('user_id', $selectedEmployeeId);
        }

        $attendanceLogs = $attendanceQuery->get();

        $minutesByUser = AttendanceLog::forMonthYear($month, $year)
            ->selectRaw('user_id, SUM(total_minutes) as total_minutes')
            ->groupBy('user_id')
            ->pluck('total_minutes', 'user_id');

        $employees = $authUser->isAdmin()
            ? User::where('role', 'employee')->with('employeeSetting')->orderBy('name')->get()
            : collect([$authUser->loadMissing('employeeSetting')]);

        $summaries = $this->buildSummaries($employees, $minutesByUser);

        $tasks = $this->tasksForPeriod($authUser, $periodStart, $periodEnd, $selectedEmployeeId);
        $tasksForToday = $this->tasksForToday($authUser);
        $missedTasks = $this->missedTasks($tasks);
        $calendarDays = $this->buildTaskCalendar($tasks, $periodStart, $periodEnd);
        $calendarTasks = $this->tasksByDate($tasks, $periodStart, $periodEnd, $authUser);
        return view('dashboard.user-log-data', [
            'month' => $month,
            'year' => $year,
            'attendanceLogs' => $attendanceLogs,
            'summaries' => $summaries,
            'tasks' => $tasks,
            'tasksForToday' => $tasksForToday,
            'missedTasks' => $missedTasks,
            'calendarDays' => $calendarDays,
            'calendarTasks' => $calendarTasks,
            'employees' => $authUser->isAdmin() ? $employees : collect(),
            'selectedEmployeeId' => $selectedEmployeeId,
            'isAdmin' => $authUser->isAdmin(),
            'recurrenceOptions' => Task::recurrenceOptions(),
            'currentSection' => $section,
        ]);
    }

    protected function buildSummaries(Collection $employees, Collection $minutesByUser): Collection
    {
        return $employees->map(function (User $employee) use ($minutesByUser) {
            $workedMinutes = (int) ($minutesByUser[$employee->id] ?? 0);
            $workedHours = round($workedMinutes / 60, 2);
            $requiredHours = (int) ($employee->employeeSetting->monthly_hours_quota ?? 0);
            $hourlyRate = (float) ($employee->employeeSetting->hourly_rate ?? 0);
            $salary = round($workedHours * $hourlyRate, 2);
            $remaining = max($requiredHours - $workedHours, 0);
            $progress = $requiredHours > 0
                ? min(100, ($workedHours / $requiredHours) * 100)
                : ($workedHours > 0 ? 100 : 0);

            return [
                'user' => $employee,
                'worked_hours' => $workedHours,
                'required_hours' => $requiredHours,
                'remaining_hours' => round($remaining, 2),
                'salary' => $salary,
                'progress' => (int) round($progress),
            ];
        });
    }

    protected function tasksForPeriod(User $user, Carbon $start, Carbon $end, ?int $selectedEmployeeId): Collection
    {
        $tasksQuery = Task::with([
            'assignedUser',
            'completions' => fn ($query) => $query->whereBetween('completed_on', [$start->toDateString(), $end->toDateString()]),
        ])->where('is_active', true);

        if ($user->isEmployee()) {
            $tasksQuery->where('assigned_user_id', $user->id);
        } elseif ($selectedEmployeeId) {
            $tasksQuery->where('assigned_user_id', $selectedEmployeeId);
        }

        $tasksQuery->where(function ($query) use ($start, $end) {
            $query->where(function ($range) use ($start, $end) {
                $range->where(function ($startCondition) use ($end) {
                    $startCondition->whereNull('start_date')->orWhere('start_date', '<=', $end->toDateString());
                })->where(function ($endCondition) use ($start) {
                    $endCondition->whereNull('end_date')->orWhere('end_date', '>=', $start->toDateString());
                });
            })->orWhere(function ($once) use ($start, $end) {
                $once->whereNotNull('due_date')
                    ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()]);
            });
        });

        return $tasksQuery->orderByDesc('created_at')->get();
    }

    protected function tasksForToday(User $user): Collection
    {
        $today = Carbon::today();

        $tasksQuery = Task::with([
            'assignedUser',
            'completions' => fn ($query) => $query->whereDate('completed_on', $today->toDateString()),
        ])->where('is_active', true);

        if ($user->isEmployee()) {
            $tasksQuery->where('assigned_user_id', $user->id);
        }

        return $tasksQuery->get()
            ->filter(fn (Task $task) => $task->isDueOn($today))
            ->values();
    }

    protected function buildTaskCalendar(Collection $tasks, Carbon $start, Carbon $end): Collection
    {
        $days = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $count = $tasks->filter(function (Task $task) use ($cursor) {
                if (!$task->isDueOn($cursor)) {
                    return false;
                }

                if ($task->recurrence_type === Task::RECURRENCE_ONCE) {
                    $dueDate = $task->due_date ?? $cursor;

                    return !$task->wasCompletedOn($dueDate);
                }

                return !$task->wasCompletedOn($cursor);
            })->count();

            $days->push([
                'date' => $cursor->copy(),
                'count' => $count,
            ]);

            $cursor->addDay();
        }

        return $days;
    }

    protected function tasksByDate(Collection $tasks, Carbon $start, Carbon $end, User $viewer): Collection
    {
        $map = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateString = $cursor->toDateString();

            $dayTasks = $tasks->filter(fn (Task $task) => $task->isDueOn($cursor))->map(function (Task $task) use ($cursor, $viewer) {
                $isCompleted = $task->recurrence_type === Task::RECURRENCE_ONCE
                    ? ($task->due_date ? $task->wasCompletedOn($task->due_date) : false)
                    : $task->wasCompletedOn($cursor);

                return [
                    'id' => $task->getKey(),
                    'title' => $task->title,
                    'description' => $task->description,
                    'employee' => $task->assignedUser->name,
                    'employee_id' => $task->assigned_user_id,
                    'type' => ucfirst($task->recurrence_type),
                    'schedule' => $this->formatTaskSchedule($task),
                    'status' => $isCompleted ? 'Done' : 'Pending',
                    'is_complete' => $isCompleted,
                    'complete_url' => route('user-logs.tasks.complete', $task),
                    'can_complete' => $viewer->isAdmin() || $task->assigned_user_id === $viewer->id,
                ];
            })->sortBy('is_complete')->values();

            $map->put($dateString, $dayTasks);
            $cursor->addDay();
        }

        return $map;
    }

    protected function formatTaskSchedule(Task $task): string
    {
        if ($task->recurrence_type === Task::RECURRENCE_ONCE) {
            return 'Due '.optional($task->due_date)->format('M d, Y');
        }

        $start = optional($task->start_date)->format('M d, Y');

        if ($task->recurrence_type === Task::RECURRENCE_CUSTOM) {
            if ($task->custom_interval_days) {
                return trim(($start ? $start.' • ' : '').'Every '.$task->custom_interval_days.' day'.($task->custom_interval_days > 1 ? 's' : ''));
            }

            if (!empty($task->custom_weekdays)) {
                return trim(($start ? $start.' • ' : '').'On '.collect($task->custom_weekdays)->map(fn ($day) => ucfirst($day))->implode(', '));
            }
        }

        return $start ?? '—';
    }

    protected function missedTasks(Collection $tasks): Collection
    {
        $today = Carbon::today();

        return $tasks->filter(function (Task $task) use ($today) {
            if ($task->recurrence_type === Task::RECURRENCE_ONCE) {
                return $task->due_date
                    && $task->due_date->lt($today)
                    && !$task->wasCompletedOn($task->due_date);
            }

            $start = $task->start_date ?? $task->due_date;
            if (!$start || $start->gte($today)) {
                return false;
            }

            $cursor = (clone $today)->subDay();
            $iterations = 0;
            $maxIterations = 60;

            while ($cursor->gte($start) && $iterations < $maxIterations) {
                if ($task->isDueOn($cursor)) {
                    return !$task->wasCompletedOn($cursor);
                }
                $cursor->subDay();
                $iterations++;
            }

            return false;
        })->values();
    }
}
