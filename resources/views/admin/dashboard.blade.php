@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard Statistics</h1>
    </div>

    <div class="row">
        @foreach($stats as $label => $value)
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small font-weight-bold">{{ str_replace('_', ' ', $label) }}</h6>
                        <h3 class="mb-0">
                            {{ is_numeric($value) && $label == 'total_revenue' ? '$'.number_format($value, 2) : $value }}
                        </h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
