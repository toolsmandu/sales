@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .stack {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .filters__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.8rem;
        }

        .filters__grid--compact {
            grid-template-columns: repeat(auto-fit, minmax(160px, auto));
            align-items: end;
        }

        .filters__grid--compact .filters__actions {
            align-self: center;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }

        .summary-card {
            border: 1px solid rgba(79, 70, 229, 0.18);
            border-radius: 1.2rem;
            padding: 1.4rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.35), transparent 45%), linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(124, 58, 237, 0.1));
            box-shadow: 0 18px 32px rgba(79, 70, 229, 0.15);
            position: relative;
            overflow: hidden;
        }

        .summary-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 55%);
            pointer-events: none;
        }

        .summary-card__header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .summary-card__title {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .summary-card__title span {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(15, 23, 42, 0.6);
        }

        .summary-card__title h3 {
            margin: 0;
            font-size: 1.3rem;
        }

        .summary-card__badge {
            align-self: flex-start;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(15, 23, 42, 0.08);
        }

        .summary-card__progress {
            min-width: 120px;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            text-align: right;
        }

        .summary-card__progress-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #312e81;
        }

        .summary-card__progress small {
            font-size: 0.8rem;
            color: rgba(15, 23, 42, 0.65);
        }

        .summary-card__progress-track {
            width: 160px;
            height: 6px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.45);
            margin-left: auto;
            position: relative;
            overflow: hidden;
        }

        .summary-card__progress-fill {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #4f46e5, #3b82f6);
            width: var(--progress, 0%);
            border-radius: inherit;
        }

        .summary-card__metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.75rem;
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .summary-card__metric {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            padding: 0.75rem;
            border-radius: 0.9rem;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(79, 70, 229, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .summary-card__metric p {
            margin: 0;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(15, 23, 42, 0.55);
        }

        .summary-card__metric strong {
            display: block;
            font-size: 1.1rem;
            margin-top: 0.2rem;
        }

        .summary-card__metric small {
            display: block;
            margin-top: 0.1rem;
            font-size: 0.78rem;
            color: rgba(15, 23, 42, 0.6);
        }

        .summary-card__metric-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #1d4ed8;
            background: rgba(59, 130, 246, 0.15);
        }

        .summary-card__metric-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .progress-circle {
            --progress: 0%;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background:
                conic-gradient(
                    rgba(79, 70, 229, 0.9) var(--progress),
                    rgba(226, 232, 240, 0.9) var(--progress)
                );
            display: grid;
            place-items: center;
            margin: 0 auto;
            position: relative;
        }

        .progress-circle span {
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #fff;
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .progress-banner {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
            padding: 1rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(79, 70, 229, 0.2);
            background: rgba(79, 70, 229, 0.05);
        }

        .progress-banner__details {
            flex: 1;
            min-width: 220px;
            display: grid;
            gap: 0.35rem;
        }

        .progress-banner__details h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .progress-banner__details p {
            margin: 0;
            color: rgba(15, 23, 42, 0.7);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.75rem;
        }

        .stat-pill {
            padding: 1rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(99, 102, 241, 0.25);
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.08), rgba(96, 165, 250, 0.12));
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            position: relative;
            overflow: hidden;
        }

        .stat-pill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.25), transparent 55%);
            pointer-events: none;
        }

        .stat-pill dt {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(15, 23, 42, 0.6);
        }

        .stat-pill dd {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: #0f172a;
        }

        .stat-pill small {
            display: block;
            font-size: 0.8rem;
            margin-top: 0.2rem;
            color: rgba(15, 23, 42, 0.6);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        table th,
        table td {
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            text-align: left;
        }

        table thead th {
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.78rem;
            color: rgba(15, 23, 42, 0.6);
        }

        .attendance-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.05);
            color: rgba(15, 23, 42, 0.65);
        }

        .attendance-status svg {
            width: 1rem;
            height: 1rem;
        }

        .attendance-status--complete {
            background: rgba(34, 197, 94, 0.15);
            color: #15803d;
        }

        .attendance-status--incomplete {
            background: rgba(248, 113, 113, 0.2);
            color: #b91c1c;
        }

        .tasks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }

        .task-pill {
            padding: 0.5rem 0.75rem;
            border-radius: 0.65rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(79, 70, 229, 0.05);
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .task-status {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .task-status.is-complete {
            color: #15803d;
        }

        .task-status.is-pending {
            color: #b45309;
        }

        .task-calendar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.65rem;
            margin-bottom: 1.25rem;
        }

        .task-calendar__cell {
            width: 100%;
            text-align: left;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.85rem;
            padding: 0.75rem;
            display: grid;
            gap: 0.3rem;
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            cursor: pointer;
        }

        .task-calendar__cell.has-tasks {
            border-color: rgba(79, 70, 229, 0.4);
            background: rgba(79, 70, 229, 0.05);
        }

        .task-calendar__cell:focus-visible,
        .task-calendar__cell.is-selected {
            outline: 2px solid rgba(79, 70, 229, 0.6);
            outline-offset: 2px;
        }

        .task-calendar__date {
            font-weight: 600;
            color: #0f172a;
        }

        .task-calendar__weekday {
            font-size: 0.85rem;
            color: rgba(15, 23, 42, 0.6);
        }

        .task-calendar__badge {
            justify-self: start;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            background: rgba(79, 70, 229, 0.15);
            color: #4c1d95;
            font-weight: 600;
        }

        .task-calendar__check {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.15);
            color: #15803d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .task-calendar__check svg {
            width: 1rem;
            height: 1rem;
        }

        .task-calendar__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .task-calendar__filters {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .task-calendar__filters select {
            border-radius: 0.5rem;
            border: 1px solid rgba(15, 23, 42, 0.2);
            padding: 0.3rem 0.6rem;
        }

        .task-form {
            display: grid;
            gap: 0.75rem;
        }

        .task-form__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .task-delete-form {
            margin: 0;
        }

        .icon-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: none;
            background: rgba(79, 70, 229, 0.12);
            color: rgba(79, 70, 229, 0.95);
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .icon-button svg {
            width: 22px;
            height: 22px;
        }

        .icon-button:hover,
        .icon-button:focus-visible {
            background: rgba(79, 70, 229, 0.2);
            color: rgba(49, 46, 129, 0.95);
            transform: translateY(-1px);
        }

        .icon-button--primary {
            background: rgba(79, 70, 229, 0.12);
            color: rgba(79, 70, 229, 0.95);
        }

        .icon-button--danger {
            background: rgba(220, 38, 38, 0.12);
            color: rgba(220, 38, 38, 0.88);
        }

        .icon-button--danger:hover,
        .icon-button--danger:focus-visible {
            background: rgba(220, 38, 38, 0.2);
            color: rgba(153, 27, 27, 0.95);
        }
    </style>
@endpush

@php
    $now = \Carbon\Carbon::now();
    $user = auth()->user();
    $timezone = 'Asia/Kathmandu';
    $timezoneLabel = 'GMT+5:45';
    $formatTime = function ($dateTime) use ($timezone) {
        return $dateTime ? $dateTime->copy()->setTimezone($timezone)->format('g:i A') : '—';
    };
    $currentSection = $currentSection ?? 'attendance';
    $monthOptions = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
    @endphp

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            @if (session('status'))
                <article role="alert" class="card">
                    {{ session('status') }}
                </article>
            @endif

            @if ($errors->any())
                <article role="alert" class="card">
                    <strong>There was a problem:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </article>
            @endif

      

            @if ($currentSection === 'attendance')
                

              

                <section class="card stack">
                    <h2>Attendance Logs</h2>
                      <form method="GET" class="stack">
                    <div class="filters__grid {{ $isAdmin ? '' : 'filters__grid--compact' }}">
                        <label>
                            Month
                            <select name="month">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected($m === $month)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Year
                            <select name="year">
                                @foreach (range($now->year - 2, $now->year + 2) as $y)
                                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </label>
                        @if ($isAdmin)
                            <label>
                                Employee
                                <select name="employee_id">
                                    <option value="">All employees</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected($employee->id === $selectedEmployeeId)>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div>
                                <button type="submit">Apply filters</button>
                            </div>
                        @else
                            <div class="filters__actions">
                                <button type="submit">Apply filters</button>
                            </div>
                        @endif
                    </div>
                </form>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    @if ($isAdmin)
                                        <th>Employee</th>
                                    @endif
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Total (hrs)</th>
                                    <th>Completion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($attendanceLogs as $log)
                                    @php
                                        $dailyQuota = $log->user->employeeSetting->daily_hours_quota ?? 0;
                                        $completionMet = $dailyQuota > 0 ? $log->total_hours >= $dailyQuota : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $log->work_date->format('D, M d, Y') }}</td>
                                        @if ($isAdmin)
                                            <td>{{ $log->user->name }}</td>
                                        @endif
                                        <td>{{ $formatTime($log->started_at) }}</td>
                                        <td>{{ $formatTime($log->ended_at) }}</td>
                                        <td>{{ number_format($log->total_hours, 2) }}</td>
                                        <td>
                                            @if (!is_null($completionMet))
                                                <span
                                                    class="attendance-status attendance-status--{{ $completionMet ? 'complete' : 'incomplete' }}"
                                                    title="{{ $completionMet ? 'Daily quota met' : 'Below daily quota' }}"
                                                >
                                                    @if ($completionMet)
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12" />
                                                        </svg>
                                                    @else
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="18" y1="6" x2="6" y2="18" />
                                                            <line x1="6" y1="6" x2="18" y2="18" />
                                                        </svg>
                                                    @endif
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 6 : 5 }}">No attendance records for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @else
                @if ($isAdmin)
                    <section class="card stack">
                        <h2>Create New Task</h2>
                        <form method="POST" action="{{ route('user-logs.tasks.store') }}" class="task-form">
                            @csrf
                            <div class="task-form__grid">
                                <label>
                                    Job Title
                                    <input type="text" name="title" required>
                                </label>
                                <label>
                                    Employee Name
                                    <select name="assigned_user_id" required>
                                        <option value="">Select</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>
                                    Interval
                                    <select name="recurrence_type" required data-recurrence-select>
                                        @foreach ($recurrenceOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>
                                    Start date
                                    <input type="date" name="start_date" required>
                                </label>
                            </div>
                            <div class="task-form__custom-days" data-custom-days hidden>
                                <label>
                                    Repeat every
                                    <input type="number" name="custom_interval_days" min="1" placeholder="e.g. 3">
                                    <small>days</small>
                                </label>
                                @error('custom_interval_days')
                                    <small role="alert">{{ $message }}</small>
                                @enderror
                            </div>
                            <label>
                                Remarks
                                <textarea name="description" rows="3" placeholder="You can write task description (optional)"></textarea>
                            </label>
                            <button type="submit">Create task</button>
                        </form>
                    </section>
                @endif

                <section class="card stack">
                    @php
                        $todayDate = \Carbon\Carbon::today();
                        $pendingToday = $tasksForToday->filter(fn ($task) => !$task->wasCompletedOn($todayDate));
                        $completedToday = $tasksForToday->filter(fn ($task) => $task->wasCompletedOn($todayDate));
                    @endphp
                    <div class="tasks-grid">
                        <div>
                            <h2>Pending Today</h2>
                             <br>
                            @forelse ($pendingToday as $task)
                                <div class="task-pill">
                                    <p><strong>Task:</strong> {{ $task->title }}</p>
                                    @if ($task->description)
                                        <p><strong>Remarks:</strong> {{ $task->description }}</p>
                                    @endif
                                    <p><strong>Employee:</strong> {{ $task->assignedUser->name }}</p>
                                    <span class="task-status is-pending">Pending</span>
                                    @if ($isAdmin || $task->assigned_user_id === $user->id)
                                        <form method="POST" action="{{ route('user-logs.tasks.complete', $task) }}">
                                            @csrf
                                            <button type="submit" class="icon-button icon-button--primary" aria-label="Mark task as done">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p>No pending tasks for today.</p>
                            @endforelse
                        </div>
                        <div>
                            <h2>Completed Today</h2>
                            <br>
                            @forelse ($completedToday as $task)
                                @php
                                    $todayCompletion = $task->completions->sortByDesc('completed_on')->first();
                                @endphp
                                <div class="task-pill">
                                    <p><strong>Task:</strong> {{ $task->title }}</p>
                                    @if ($task->description)
                                        <p><strong>Remarks:</strong> {{ $task->description }}</p>
                                    @endif
                                    <p><strong>Employee:</strong> {{ $task->assignedUser->name }}</p>
                                    <span class="task-status is-complete">Completed</span>
                                    @if ($todayCompletion)
                                        <small>Finished {{ $todayCompletion->completed_on->format('M d, Y') }}</small>
                                    @endif
                                </div>
                            @empty
                                <p>No tasks completed yet today.</p>
                            @endforelse
                        </div>
                    </div>

                    @if (!empty($calendarDays) && $calendarDays->count())
                        <section class="card stack">
                            <div class="task-calendar__header">
                                <h2>Task Calendar</h2>
                                <form
                                    method="GET"
                                    action="{{ route('user-logs.' . $currentSection) }}"
                                    class="task-calendar__filters"
                                >
                                    <label>
                                        Month
                                        <select name="month" onchange="this.form.submit()">
                                            @foreach ($monthOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($value === $month)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>
                                        Year
                                        <select name="year" onchange="this.form.submit()">
                                            @foreach (range($now->year - 2, $now->year + 2) as $y)
                                                <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    @if ($selectedEmployeeId)
                                        <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}">
                                    @endif
                                </form>
                            </div>
                            <div class="task-calendar" aria-label="Task calendar">
                                @foreach ($calendarDays as $calendarDay)
                                    <button
                                        type="button"
                                        class="task-calendar__cell {{ $calendarDay['count'] ? 'has-tasks' : '' }}"
                                        data-calendar-date="{{ $calendarDay['date']->toDateString() }}"
                                    >
                                        <span class="task-calendar__date">{{ $calendarDay['date']->format('M d') }}</span>
                                        <span class="task-calendar__weekday">{{ $calendarDay['date']->format('D') }}</span>
                                        @if ($calendarDay['count'] > 0)
                                            <span class="task-calendar__badge">
                                                {{ $calendarDay['count'] }} {{ \Illuminate\Support\Str::plural('task', $calendarDay['count']) }}
                                            </span>
                                        @else
                                            <span class="task-calendar__check" title="No tasks due">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            </span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="card stack">
                        <div class="task-calendar__header">
                            <h2>Tasks on <span data-selected-date>select a day</span></h2>
                        </div>
                        <p data-task-empty>Click on a day above to see its tasks.</p>
                        <div class="table-wrapper" data-task-table-wrapper hidden>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Schedule</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody data-task-table></tbody>
                            </table>
                        </div>
                    </section>
                </section>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var recurrenceSelect = document.querySelector('[data-recurrence-select]');
            var customDays = document.querySelector('[data-custom-days]');

            if (recurrenceSelect && customDays) {
                var toggleCustomDays = function () {
                    var isCustom = recurrenceSelect.value === 'custom';
                    customDays.hidden = !isCustom;
                };

                toggleCustomDays();
                recurrenceSelect.addEventListener('change', toggleCustomDays);
            }

            (function () {
                var calendarData = @json($calendarTasks ?? []);
                var csrfToken = @json(csrf_token());
                var calendarCells = document.querySelectorAll('[data-calendar-date]');
                var tableWrapper = document.querySelector('[data-task-table-wrapper]');
                var tableBody = document.querySelector('[data-task-table]');
                var emptyState = document.querySelector('[data-task-empty]');
                var selectedDateLabel = document.querySelector('[data-selected-date]');
                var activeCell = null;

                if (!calendarCells.length || !tableBody) {
                    return;
                }

                var escapeHtml = function (str) {
                    if (!str) return '';
                    return str
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                };

                var formatLabel = function (dateString) {
                    var date = new Date(dateString + 'T00:00:00');
                    return date.toLocaleDateString('en-US', {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric',
                    });
                };

                var renderTasksForDate = function (dateString, cell) {
                    if (activeCell) {
                        activeCell.classList.remove('is-selected');
                    }
                    activeCell = cell;
                    cell.classList.add('is-selected');

                    var tasks = calendarData[dateString] || [];
                    selectedDateLabel.textContent = formatLabel(dateString);

                    if (!tasks.length) {
                        tableWrapper.hidden = true;
                        tableBody.innerHTML = '';
                        emptyState.textContent = 'No tasks due on this day.';
                        return;
                    }

                    var rows = tasks.map(function (task) {
                        var remarks = task.description
                            ? '<p><strong>Remarks:</strong> ' + escapeHtml(task.description) + '</p>'
                            : '';
                        var statusClass = task.is_complete ? 'is-complete' : 'is-pending';
                        var actionHtml = '';
                        if (!task.is_complete && task.can_complete) {
                            actionHtml = '' +
                                '<form method="POST" action="' + escapeHtml(task.complete_url) + '" class="calendar-complete-form">' +
                                    '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                                    '<button type="submit" class="icon-button icon-button--primary" aria-label="Mark task as done">' +
                                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
                                            '<polyline points="20 6 9 17 4 12" />' +
                                        '</svg>' +
                                    '</button>' +
                                '</form>';
                        } else if (task.is_complete) {
                            actionHtml = '<span class="task-status is-complete">Done</span>';
                        } else {
                            actionHtml = '—';
                        }

                        return (
                            '<tr>' +
                                '<td>' +
                                    '<p><strong>Task:</strong> ' + escapeHtml(task.title) + '</p>' +
                                    remarks +
                                '</td>' +
                                '<td>' + escapeHtml(task.employee) + '</td>' +
                                '<td>' + escapeHtml(task.type) + '</td>' +
                                '<td>' + escapeHtml(task.schedule) + '</td>' +
                                '<td class="task-status ' + statusClass + '">' + escapeHtml(task.status) + '</td>' +
                                '<td>' + actionHtml + '</td>' +
                            '</tr>'
                        );
                    }).join('');

                    emptyState.textContent = '';
                    tableWrapper.hidden = false;
                    tableBody.innerHTML = rows;
                };

                calendarCells.forEach(function (cell) {
                    cell.addEventListener('click', function () {
                        renderTasksForDate(cell.getAttribute('data-calendar-date'), cell);
                    });
                });
            }());
        });
    </script>
@endpush
