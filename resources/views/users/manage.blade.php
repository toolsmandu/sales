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
                    </div>
                @endif
            </section>

        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
@endpush
