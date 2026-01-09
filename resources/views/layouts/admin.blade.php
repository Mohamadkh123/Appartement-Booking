<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('messages.dashboard')) - Admin</title>

    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @endif

    <style>
        html, body {
            height: 100%;
            overflow: hidden;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            {{ app()->getLocale() == 'ar' ? 'right: 0;' : 'left: 0;' }}
            width: 250px;
            height: 100vh;
            background: #212529;
            color: white;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #343a40;
            color: #ffffff;
        }

        .main-content {
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 250px;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar">
    <h4 class="text-center mb-3">AdminPanel</h4>

    <!-- Language Switcher -->
    <div class="px-3 mb-3 text-center">
        @if(app()->getLocale() == 'en')
            <a href="/lang/ar" class="btn btn-sm btn-outline-light w-100">العربية</a>
        @else
            <a href="/lang/en" class="btn btn-sm btn-outline-light w-100">English</a>
        @endif
    </div>

    <hr class="text-secondary">

    <a href="/admin/dashboard" class="{{ Request::is('admin/dashboard') ? 'active' : '' }}">
        {{ __('messages.dashboard') }}
    </a>

    <a href="/admin/users" class="{{ Request::is('admin/users') ? 'active' : '' }}">
        {{ __('messages.users') }}
    </a>

    <a href="/admin/users/pending" class="{{ Request::is('admin/users/pending') ? 'active' : '' }}">
        {{ __('messages.pending_users') }}
    </a>

    <!-- Logout -->
    <div class="mt-auto p-3">
        <form action="/logout" method="POST">
            @csrf
            <button class="btn btn-danger w-100">
                {{ __('messages.logout') }}
            </button>
        </form>
    </div>
</nav>

<!-- Main Content -->
<main class="main-content">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
