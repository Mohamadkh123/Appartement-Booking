<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.login_panel') }}</title>

    <!-- Conditional Bootstrap for RTL Support -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @endif

    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; flex-direction: column; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 10px; }
        .lang-switcher { margin-bottom: 20px; }
    </style>
</head>
<body>

<!-- Language Switcher -->
<div class="lang-switcher">
    @if(app()->getLocale() == 'en')
        <a href="/lang/ar" class="btn btn-sm btn-outline-secondary">العربية</a>
    @else
        <a href="/lang/en" class="btn btn-sm btn-outline-secondary">English</a>
    @endif
</div>

<div class="card shadow login-card">
    <div class="card-body">
        <h3 class="text-center mb-4">{{ __('messages.admin_login') }}</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">{{ __('messages.email_address') }}</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ __('messages.password') }}</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">{{ __('messages.sign_in') }}</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
