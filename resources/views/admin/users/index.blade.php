@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        {{-- If $title is passed from controller as a string key like 'all_users' --}}
        <h1 class="h2">{{ __('messages.' . $title) }}</h1>
    </div>

    <div class="table-responsive bg-white shadow-sm p-3">
        <table class="table table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.email') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->first_name ." ". $user->last_name}}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->status == 'active' ? 'bg-success' : ($user->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                            {{-- Localized Status --}}
                            {{ __('messages.' . $user->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            @if($user->status == 'pending')
                                <form action="/admin/users/{{ $user->id }}/approve" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">
                                        {{ __('messages.approve') }}
                                    </button>
                                </form>
                                <form action="/admin/users/{{ $user->id }}/reject" method="POST" onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">
                                        {{ __('messages.reject') }}
                                    </button>
                                </form>
                            @endif

                            @if($user->status == 'active')
                                <!-- Wallet Deposit Button -->
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#depositModal{{ $user->id }}">
                                    {{ __('messages.deposit') }}
                                </button>
                            @endif
                        </div>

                        <!-- Deposit Modal -->
                        <div class="modal fade" id="depositModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="/admin/wallet/deposit/{{ $user->id }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                {{-- Using parameters in translation --}}
                                                {{ __('messages.deposit_title', ['name' => $user->first_name]) }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('messages.amount') }} ($)</label>
                                                <input type="number" name="amount" step="0.01" class="form-control" required placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                {{ __('messages.close') }}
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                {{ __('messages.confirm_deposit') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{-- Ensure pagination uses bootstrap and respects locale --}}
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
@endsection
