@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            @if (session('status'))
                <article role="status">
                    {{ session('status') }}
                </article>
            @endif

            <section class="card stack">
                <h2>Team Management</h2>

                @if ($teamMembers->isEmpty())
                    <p class="helper-text">No team members available yet.</p>
                @else
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Position</th>
                                    <th scope="col">Daily Work Hours</th>
                                    <th scope="col">Work Session</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teamMembers as $teamMember)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $teamMember->name }}</td>
                                        <td>{{ $teamMember->email }}</td>
                                        <td>{{ ucfirst($teamMember->role ?? '—') }}</td>
                                        <td>
                                            @if ($teamMember->employeeSetting)
                                                {{ $teamMember->employeeSetting->daily_hours_quota ?? '—' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if ($teamMember->role === 'employee')
                                                @php
                                                    $activeLog = $activeAttendanceLogs->get($teamMember->id);
                                                    $activeSince = $activeLog?->started_at
                                                        ? $activeLog->started_at
                                                        ->timezone(config('app.timezone'))
                                                        ->format('M d, Y g:i A')
                                                    : null;
                                            @endphp
                                            <div class="stack">
                                                
                                                <form method="POST" class="table-actions">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="ghost-button work-session-button"
                                                        formaction="{{ route('dashboard.users.attendance.start', $teamMember) }}"
                                                        {{ $activeLog ? 'disabled' : '' }}
                                                    >
                                                        Start
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        class="ghost-button work-session-button"
                                                        formaction="{{ route('dashboard.users.attendance.end', $teamMember) }}"
                                                        {{ $activeLog ? '' : 'disabled' }}
                                                    >
                                                        Stop
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($teamMember->role === 'employee')
                                                <a
                                                    class="ghost-button"
                                                        href="{{ route('dashboard.users.index', ['edit' => $teamMember->id]) }}"
                                                        aria-label="Manage {{ $teamMember->name }}"
                                                    >
                                                        Manage
                                                    </a>
                                                @else
                                                    <span class="muted">—</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="card stack">
                <h2>Work Schedule</h2>
                <form method="POST" action="{{ route('dashboard.settings.work-schedule') }}">
                    @csrf
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    @for ($col = 0; $col < 4; $col++)
                                        <th scope="col">
                                            <input
                                                type="text"
                                                name="work_schedule[0][{{ $col }}]"
                                                value="{{ $workSchedule[0][$col] ?? '' }}"
                                                placeholder="Header {{ $col + 1 }}"
                                            >
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @for ($row = 1; $row < 5; $row++)
                                    <tr>
                                        @for ($col = 0; $col < 4; $col++)
                                            <td>
                                                <input
                                                    type="text"
                                                    name="work_schedule[{{ $row }}][{{ $col }}]"
                                                    value="{{ $workSchedule[$row][$col] ?? '' }}"
                                                    placeholder="Row {{ $row }} col {{ $col + 1 }}"
                                                >
                                            </td>
                                        @endfor
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    @error('work_schedule')
                        <small role="alert">{{ $message }}</small>
                    @enderror
                    @error('work_schedule.*')
                        <small role="alert">{{ $message }}</small>
                    @enderror
                    @error('work_schedule.*.*')
                        <small role="alert">{{ $message }}</small>
                    @enderror

                    <div class="form-actions">
                        <button type="submit">Save Schedule</button>
                    </div>
                </form>
            </section>

            <section class="card stack">
                <h2>Manage Employee</h2>

                @if (! $employeeToEdit)
                    <p class="helper-text">Select an employee from the list above to edit their profile or reset their password.</p>
                @else
                    <div class="stack">
                        <h3>{{ $employeeToEdit->name }}</h3>

                        <form method="POST" action="{{ route('dashboard.users.update', $employeeToEdit) }}" class="form-grid form-grid--compact">
                            @csrf
                            @method('PUT')

                            <label for="employee-name">
                                Name
                                <input
                                    type="text"
                                    id="employee-name"
                                    name="name"
                                    value="{{ old('name', $employeeToEdit->name) }}"
                                    required
                                    autocomplete="name"
                                >
                            </label>
                            @error('name', 'employeeUpdate')
                                <small role="alert">{{ $message }}</small>
                            @enderror

                            <label for="employee-email">
                                Email
                                <input
                                    type="email"
                                    id="employee-email"
                                    name="email"
                                    value="{{ old('email', $employeeToEdit->email) }}"
                                    required
                                    autocomplete="email"
                                >
                            </label>
                            @error('email', 'employeeUpdate')
                                <small role="alert">{{ $message }}</small>
                            @enderror

                            <div class="form-actions">
                                <button type="submit">Update Profile</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('dashboard.users.password.update', $employeeToEdit) }}" class="form-grid form-grid--compact">
                            @csrf
                            @method('PUT')

                            <label for="employee-password">
                                New password
                                <input
                                    type="password"
                                    id="employee-password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                >
                            </label>
                            @error('password', 'employeePassword')
                                <small role="alert">{{ $message }}</small>
                            @enderror

                            <label for="employee-password-confirmation">
                                Confirm Password
                                <input
                                    type="password"
                                    id="employee-password-confirmation"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                >
                            </label>
                            @error('password_confirmation', 'employeePassword')
                                <small role="alert">{{ $message }}</small>
                            @enderror

                            <div class="form-actions">
                                <button type="submit" class="button">Update Password</button>
                            </div>
                        </form>

                        <div class="stack">
                            <h4>Employee Settings</h4>
                            <form method="POST" action="{{ route('user-logs.settings.store', $employeeToEdit) }}" class="form-grid form-grid--compact">
                                @csrf

                                <label for="employee-daily-hours">
                                    Daily hours quota
                                    <input
                                        type="number"
                                        id="employee-daily-hours"
                                        name="daily_hours_quota"
                                        min="0"
                                        value="{{ old('daily_hours_quota', optional($employeeToEdit->employeeSetting)->daily_hours_quota ?? 0) }}"
                                        required
                                    >
                                </label>
                                @error('daily_hours_quota', 'employeeSettings')
                                    <small role="alert">{{ $message }}</small>
                                @enderror

                                <div class="form-actions">
                                    <button type="submit">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </section>

        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
@endpush
