<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} | Registro</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- AdminLTE + Bootstrap -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body class="register-page bg-body-secondary">

<div class="register-box">
    <div class="card card-outline card-primary">

        <div class="card-header text-center">
            <h1 class="mb-0"><b>Sistema</b> Almacén</h1>
        </div>

        <div class="card-body register-card-body">
            <p class="register-box-msg">Registrar nueva cuenta</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                {{-- Full Name --}}
                <div class="input-group mb-3">
                    <input
                        type="text"
                        name="full_name"
                        class="form-control @error('full_name') is-invalid @enderror"
                        placeholder="Nombre completo"
                        value="{{ old('full_name') }}"
                        required
                    >
                    <div class="input-group-text">
                        <span class="bi bi-person"></span>
                    </div>

                    @error('full_name')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Username --}}
                <div class="input-group mb-3">
                    <input
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="Nombre de usuario"
                        value="{{ old('name') }}"
                        required
                    >
                    <div class="input-group-text">
                        <span class="bi bi-person-badge"></span>
                    </div>

                    @error('name')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="input-group mb-3">
                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Correo electrónico"
                        value="{{ old('email') }}"
                        required
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

                {{-- Confirm Password --}}
                <div class="input-group mb-3">
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        placeholder="Confirmar contraseña"
                        required
                    >
                    <div class="input-group-text">
                        <span class="bi bi-lock"></span>
                    </div>

                    @error('password_confirmation')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Company --}}
                <div class="input-group mb-3">
                    <input
                        type="text"
                        name="company"
                        class="form-control @error('company') is-invalid @enderror"
                        placeholder="Empresa"
                        value="{{ old('company') }}"
                        required
                    >
                    <div class="input-group-text">
                        <span class="bi bi-building"></span>
                    </div>

                    @error('company')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Phone Number --}}
                <div class="input-group mb-4">
                    <input
                        type="text"
                        name="phone_number"
                        class="form-control @error('phone_number') is-invalid @enderror"
                        placeholder="Teléfono"
                        value="{{ old('phone_number') }}"
                        required
                    >
                    <div class="input-group-text">
                        <span class="bi bi-telephone"></span>
                    </div>

                    @error('phone_number')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-8">
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            Ya tengo una cuenta
                        </a>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Registrar
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/adminlte.js') }}"></script>

</body>
</html>
