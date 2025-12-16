<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} | Login</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- AdminLTE + Bootstrap -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body class="login-page bg-body-secondary">

<div class="login-box">
    <div class="login-logo">
        <b>Sistema</b> Almacén
    </div>

    <div class="card">
        <div class="card-body login-card-body">

            <p class="login-box-msg">Iniciar sesión</p>

            {{-- Estado de sesión --}}
            @if (session('status'))
                <div class="alert alert-success mb-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="input-group mb-3">
                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Correo electrónico"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                    <div class="input-group-text">
                        <span class="bi bi-envelope"></span>
                    </div>

                    @error('email')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="input-group mb-3">
                    <input
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Contraseña"
                        required
                    >
                    <div class="input-group-text">
                        <span class="bi bi-lock-fill"></span>
                    </div>

                    @error('password')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="row mb-3">
                    <div class="col-8">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="remember">
                                Recordarme
                            </label>
                        </div>
                    </div>

                    <div class="col-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Ingresar
                        </button>
                    </div>
                </div>
            </form>

            {{-- Forgot password --}}
            @if (Route::has('password.request'))
                <p class="mb-1">
                    <a href="{{ route('password.request') }}">
                        Olvidé mi contraseña
                    </a>
                </p>
            @endif

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/adminlte.js') }}"></script>

</body>
</html>
