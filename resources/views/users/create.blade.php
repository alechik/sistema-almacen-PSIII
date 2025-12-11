@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Registrar Usuario</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuarios</a></li>
                        <li class="breadcrumb-item active">Nuevo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Datos del Usuario</h3>
                </div>
                <div class="card-body">

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Nombre Completo -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre Completo *</label>
                                <input type="text" name="full_name"
                                    class="form-control @error('full_name') is-invalid @enderror"
                                    value="{{ old('full_name') }}" placeholder="Ej: Carlos Pérez">
                                @error('full_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Nombre de Usuario -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre de Usuario *</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Ej: cperez123">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo Electrónico *</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="Ej: ejemplo@gmail.com">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contraseña *</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Mínimo 6 caracteres">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Empresa -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Empresa</label>
                                <input type="text" name="company"
                                    class="form-control @error('company') is-invalid @enderror"
                                    value="{{ old('company', auth()->user()->company) }}" readonly>
                                @error('company')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Teléfono</label>
                                <input type="text" name="phone_number"
                                    class="form-control @error('phone_number') is-invalid @enderror"
                                    value="{{ old('phone_number') }}" placeholder="Ej: 71234567">
                                @error('phone_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Rol -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Rol del Usuario *</label>
                            <select name="role"
                                    class="form-select @error('role') is-invalid @enderror">
                                <option value="">Seleccione un rol...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Usuario
                            </button>
                        </div>

                    </form>

                </div>


            </div>

        </div>
    </div>

</main>

@endsection
