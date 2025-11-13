@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content">
            <header>
                <h2>Manage Payment Methods</h2>
            </header>

            @if (session('status'))
                <article role="alert" class="stack">
                    {{ session('status') }}
                </article>
            @endif

            @if ($errors->any())
                <article role="alert" class="stack">
                    <strong>There was a problem:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </article>
            @endif

            <section class="card dashboard-panel stack card--accent">
                <div class="stack">
                    <h2>Add Payment Method</h2><br>
                    <form method="POST" action="{{ route('dashboard.payment-methods.store') }}" class="form-grid form-grid--compact">
                        @csrf
                        <label for="payment-method-name">
                            Payment name
                            <input
                                id="payment-method-name"
                                name="label"
                                type="text"
                                placeholder="eSewa / Khalti / Bank"
                                value="{{ old('label') }}"
                                required
                            >
                        </label>
                        <label for="payment-method-unique">
                            Unique number
                            <input
                                id="payment-method-unique"
                                name="unique_number"
                                type="text"
                                placeholder="Unique identifier"
                                value="{{ old('unique_number') }}"
                                required
                            >
                        </label>
                        <label for="payment-method-limit">
                            Monthly limit (NPR)
                            <input
                                id="payment-method-limit"
                                name="monthly_limit"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('monthly_limit', 0) }}"
                                required
                            >
                        </label>
                        <div class="form-actions form-actions--row">
                            <button type="submit">Add method</button>
                        </div>
                    </form>
                </div>

                <div class="stack">
                    <h2>Edit Payment Methods</h2>
                    <br>
                    <div class="method-card-list">
                        @forelse ($paymentMethods as $method)
                            <article class="method-card">
                      

                                <div class="method-card__body">
                                    <form method="POST" action="{{ route('dashboard.payment-methods.update', $method) }}" class="method-card__form">
                                        @csrf
                                        @method('PUT')
                                        <label class="method-card__label" for="method-{{ $method->id }}">
                                            Payment name
                                            <input
                                                id="method-{{ $method->id }}"
                                                type="text"
                                                name="label"
                                                value="{{ $method->label }}"
                                                required>
                                        </label>
                                        <label class="method-card__label">
                                            Unique number
                                            <input
                                                type="text"
                                                name="unique_number"
                                                value="{{ $method->unique_number }}"
                                                required
                                            >
                                        </label>
                                        <label class="method-card__label">
                                            Monthly limit (NPR)
                                            <input
                                                type="number"
                                                name="monthly_limit"
                                                min="0"
                                                step="0.01"
                                                value="{{ $method->monthly_limit }}"
                                                required
                                            >
                                        </label>
                                        <button type="submit" class="pill-button pill-button--primary">Save</button>
                                    </form>

                                    <form method="POST" action="{{ route('dashboard.payment-methods.destroy', $method) }}" class="method-card__delete" onsubmit="return confirm('Remove {{ $method->label }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pill-button pill-button--danger">Delete</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <p class="helper-text">No payment methods yet. Add your first one above.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
@endpush
