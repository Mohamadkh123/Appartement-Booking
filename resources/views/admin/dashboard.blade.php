@extends('layouts.admin')

@section('title', __('messages.dashboard'))

@section('content')
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ __('messages.dashboard') }}</h1>
    </div>

    <div class="row">
        @foreach($stats as $label => $value)
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted small">
                            {{ __('messages.' . $label) }}
                        </h6>
                        <h3>{{ $value }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
