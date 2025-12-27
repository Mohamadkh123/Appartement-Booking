@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
    </div>

    <div class="table-responsive bg-white shadow-sm p-3">
        <table class="table table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->first_name ." ". $user->last_name}}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                    <span class="badge {{ $user->status == 'active' ? 'bg-success' : ($user->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                        {{ ucfirst($user->status) }}
                    </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            @if($user->status == 'pending')
                                <form action="/admin/users/{{ $user->id }}/approve" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Approve</button>
                                </form>
                                <form action="/admin/users/{{ $user->id }}/reject" method="POST" onsubmit="return confirm('Delete this user?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @endif

                            <!-- Wallet Deposit Button (Triggers Modal) -->
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#depositModal{{ $user->id }}">
                                Deposit
                            </button>
                        </div>

                        <!-- Deposit Modal -->
                        <div class="modal fade" id="depositModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="/admin/wallet/deposit/{{ $user->id }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Deposit to {{ $user->name }}'s Wallet</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Amount ($)</label>
                                                <input type="number" name="amount" step="0.01" class="form-control" required placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Confirm Deposit</button>
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
        {{ $users->links() }}
    </div>
@endsection
