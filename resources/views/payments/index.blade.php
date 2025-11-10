@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')


    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
@endpush
