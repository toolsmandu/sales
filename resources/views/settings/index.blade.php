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

            <section class="card">
                <p class="helper-text">Settings will live here.</p>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
@endpush
