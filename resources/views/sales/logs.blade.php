@php
    $pageTitle = 'Orders Change Logs';
@endphp

@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            <section class="card stack">
                <div class="card__header" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <h1 style="margin: 0;">{{ $pageTitle }}</h1>
                    </div>
                    <form method="GET" action="{{ route('orders.logs') }}" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: nowrap;">
                        <input
                            type="text"
                            id="order-log-search"
                            name="search"
                            placeholder="Enter Order ID (e.g., TM123)"
                            value="{{ $search ?? '' }}"
                            style="min-width: 220px;"
                            aria-label="Order ID"
                        >
                        <button type="submit" class="ghost-button">Search</button>
                        @if (!empty($search))
                            <a class="ghost-button ghost-button--secondary" href="{{ route('orders.logs') }}">Reset</a>
                        @endif
                    </form>
                </div>

                @unless($hasTable)
                    <div class="alert alert--warning">
                        <p style="margin: 0;">The log table is missing. Please run the migration to enable order edit logs.</p>
                    </div>
                @endunless

                @if ($hasTable)
                    @if ($logs->isEmpty())
                        <p class="helper-text">No order edits recorded yet.</p>
                    @else
                        <div class="table-wrapper">
                            <table class="sales-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Order ID</th>
                                        <th scope="col">Changed By</th>
                                        <th scope="col">Changes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logs as $log)
                                        @php
                                            $orderId = $log->sale?->serial_number ?? 'Unknown';
                                            $actor = $log->actor?->name ?? 'Unknown';
                                            $changeText = trim((string) $log->message);
                                            $timestamp = $log->created_at
                                                ? $log->created_at->copy()->timezone($timezone ?? 'Asia/Kathmandu')->format('Y-m-d g:i A')
                                                : '—';
                                            $orderLink = route('orders.index', ['search' => $orderId]);
                                        @endphp
                                        <tr>
                                            <td>{{ $timestamp }}</td>
                                            <td><a href="{{ $orderLink }}">{{ $orderId }}</a></td>
                                            <td>{{ $actor }}</td>
                                            <td>{{ $changeText }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $logs->links() }}
                    @endif
                @endif
            </section>
        </section>
    </div>
@endsection
