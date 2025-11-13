@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">

            @if (session('status'))
                <article role="alert">
                    {{ session('status') }}
                </article>
            @endif

            @if ($errors->any())
                <article role="alert">
                    <strong>There was a problem:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </article>
            @endif
            <section class="card dashboard-panel card--accent">
                                <header>
                    <h2>Withdraw Fund</h2>
                </header>
                <form
                    id="withdraw-form"
                    method="POST"
                    action="{{ route('dashboard.withdrawals.store') }}"
                    class="form-grid form-grid--compact">
                    @csrf
               
                        <label for="withdraw-date">
                        Date
                        <input
                            type="date"
                            id="withdraw-date"
                            name="date"
                            value="{{ old('date', now()->format('Y-m-d')) }}"
                            required>
                    </label>

                    <label for="withdraw-method">
                        Method
                        <select
                            id="withdraw-method"
                            name="payment_method"
                            @disabled($paymentMethods->isEmpty())
                            required>
                            <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>Select method</option>
                            @foreach ($paymentMethods as $method)
                                @php
                                    $availableBalance = property_exists($method, 'available_balance')
                                        ? $method->available_balance
                                        : $method->balance;
                                @endphp
                                <option value="{{ $method->slug }}" @selected(old('payment_method') === $method->slug)>
                                    {{ $method->label }} (Balance: Rs {{ number_format($availableBalance, 0) }})
                                </option>
                            @endforeach
                        </select>


                    </label>

                    @if ($paymentMethods->isEmpty())
                        <p class="helper-text">
                            Add a payment method first before recording withdrawals.
                        </p>
                    @endif

                    <label for="withdraw-amount">
                        Amount
                        <input
                            type="number"
                            id="withdraw-amount"
                            name="amount"
                            min="0"
                            step="0.01"
                            value="{{ old('amount') }}"
                            placeholder="Rs."
                            required>
                    </label>

                    <label for="withdraw-note">
                        Remarks
                        <input
                            type="text"
                            id="withdraw-note"
                            name="note"
                            value="{{ old('note') }}"
                            placeholder="Write note or reference">
                    </label>



                    <div class="form-actions form-actions--row">
                        <button type="submit" {{ $paymentMethods->isEmpty() ? 'disabled' : '' }}>
                            Withdraw
                        </button>
                    </div>
                </form>
            </section>

            <section class="card dashboard-panel card--accent">
                <header>
                    <h2>Recent Withdrawals</h2>
                </header>

                <div class="table-wrapper">
                    <table class="table-striped">
                        
                        <thead>
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Date</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Remarks</th>
                                <th scope="col">Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($withdrawals as $withdrawal)
                                <tr>
                                    @php
                                        $withdrawRecordedAt = $withdrawal->created_at?->timezone('Asia/Kathmandu');
                                        $serialNumber = $withdrawal->sale?->serial_number ?? ('TM' . $withdrawal->id);
                                    @endphp
                                    <td>{{ $serialNumber }}</td>
                                    <td>
                                        @if ($withdrawRecordedAt)
                                            {{ $withdrawRecordedAt->format('Y-M-d h:i A') }}
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="amount-chip amount-chip--expense">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M6 8l6 8 6-8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Rs {{ number_format($withdrawal->amount, 0) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($withdrawal->phone)
                                            {{ $withdrawal->phone }}
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($withdrawal->paymentMethod)
                                            <span class="badge badge--method">
                                                {{ $withdrawal->paymentMethod->label }}
                                            </span>
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <p class="helper-text">No withdrawals recorded yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php
                    $totalWithdrawals = $withdrawals->total();
                    $currentPage = $withdrawals->currentPage();
                    $lastPage = $withdrawals->lastPage();
                    $start = $totalWithdrawals === 0 ? 0 : ($withdrawals->perPage() * ($currentPage - 1)) + 1;
                    $end = $totalWithdrawals === 0 ? 0 : min($start + $withdrawals->count() - 1, $totalWithdrawals);
                @endphp

                <div class="table-controls">
                    <form method="GET" class="table-controls__page-size table-controls__page-size--compact">
                        @foreach (request()->except('per_page', 'page') as $param => $value)
                            @if (!is_array($value))
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="withdrawals-per-page">
                            
                            <select
                                id="withdrawals-per-page"
                                name="per_page"
                                onchange="this.form.submit()">
                                @foreach ([25, 50, 100] as $option)
                                    <option value="{{ $option }}" @selected($perPage === $option)>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            
                        </label>
                    </form>
                    <div class="table-controls__pagination">
                        <a
                            class="ghost-button"
                            href="{{ $withdrawals->previousPageUrl() ? route('payments.withdraw', array_merge(request()->query(), ['page' => $currentPage - 1])) : '#' }}"
                            @class(['is-disabled' => !$withdrawals->previousPageUrl()])
                            aria-disabled="{{ $withdrawals->previousPageUrl() ? 'false' : 'true' }}">
                            Previous
                        </a>
                        <span class="helper-text">
                            @if ($totalWithdrawals === 0)
                                No withdrawals to display
                            @else
                                Showing {{ $start }}-{{ $end }} of {{ $totalWithdrawals }} (Page {{ $currentPage }} of {{ $lastPage }})
                            @endif
                        </span>
                        <a
                            class="ghost-button"
                            href="{{ $withdrawals->nextPageUrl() ? route('payments.withdraw', array_merge(request()->query(), ['page' => $currentPage + 1])) : '#' }}"
                            @class(['is-disabled' => !$withdrawals->nextPageUrl()])
                            aria-disabled="{{ $withdrawals->nextPageUrl() ? 'false' : 'true' }}">
                            Next
                        </a>
                    </div>
                </div>
            </section>
        </section>
    </div>
@endsection
