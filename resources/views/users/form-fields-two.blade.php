{{-- @csrf --}}

<div class="row">
    <!-- Nombre Completo -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nombre Completo *</label>
        <input type="text" name="full_name"
               class="form-control @error('full_name') is-invalid @enderror"
               value="{{ old('full_name', $user->full_name ?? '') }}"
               placeholder="Ej: Carlos Pérez">
        @error('full_name')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Nombre de Usuario -->
    <div class="col-md-6 mb-3" hidden>
        <label class="form-label fw-bold">Nombre de Usuario *</label>
        <input type="text" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name ?? '') }}"
               placeholder="Ej: cperez123">
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
               value="{{ old('email', $user->email ?? '') }}"
               placeholder="Ej: ejemplo@gmail.com">
        @error('email')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Password -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">
            Contraseña {{ isset($user) ? '' : '*' }}
        </label>
        <input type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               placeholder="{{ isset($user) ? 'Sólo si desea cambiar...' : 'Mínimo 6 caracteres' }}">
        @error('password')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="row">
    <!-- Empresa -->
    <div class="col-md-6 mb-3" readonly>
        <label class="form-label fw-bold">Empresa</label>
        <input type="text" name="company"
               class="form-control @error('company') is-invalid @enderror"
               value="{{ old('company', $user->company ?? '') }}"
               placeholder="Ej: Transportes Bolivia SRL" readonly>
        @error('company')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Teléfono -->
    <div class="col-md-6 mb-3 ">
        <label class="form-label fw-bold">Teléfono</label>
        <input type="text" name="phone_number"
               class="form-control @error('phone_number') is-invalid @enderror"
               value="{{ old('phone_number', $user->phone_number ?? '') }}"
               placeholder="Ej: 71234567">
        @error('phone_number')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<hr class="my-4">

<!-- Rol -->
<div class="mb-4" hidden>
    <label class="form-label fw-bold">Rol del Usuario *</label>
    <select name="role" class="form-select @error('role') is-invalid @enderror">
        <option value="">Seleccione un rol...</option>

        @foreach($roles as $role)
            <option value="{{ $role }}"
                {{ old('role', isset($user) && $user->roles->first() ? $user->roles->first()->name : '') == $role ? 'selected' : '' }}>
                {{ ucfirst($role) }}
            </option>
        @endforeach
    </select>
    @error('role')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
