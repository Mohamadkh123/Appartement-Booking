<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        /* Added flexbox to sidebar to push logout to the bottom */
        .sidebar {
            min-height: 100vh;
            background: #212529;
            color: white;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
        }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #343a40; color: white; }
        .logout-section { margin-top: auto; padding: 20px; border-top: 1px solid #343a40; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <div>
                <h4 class="text-center">AdminPanel</h4>
                <hr>
                <a href="/admin/dashboard" class="{{ Request::is('admin/dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="/admin/users" class="{{ Request::is('admin/users') ? 'active' : '' }}">All Users</a>
                <a href="/admin/users/pending" class="{{ Request::is('admin/users/pending') ? 'active' : '' }}">Pending Users</a>
            </div>

            <!-- Logout Button at the bottom -->
            <div class="logout-section">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
