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
    <div class="col-md-6 mb-3">
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
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Empresa</label>
        <input type="text" name="company"
               class="form-control @error('company') is-invalid @enderror"
               value="{{ old('company', $user->company ?? '') }}"
               placeholder="Ej: Transportes Bolivia SRL">
        @error('company')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Teléfono -->
    <div class="col-md-6 mb-3">
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

<div class="row">
    <!-- Rol -->
    <div class="col-md-6 mb-3">
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
    <!-- Estado -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Estado *</label>
        <select name="estado" 
                class="form-select @error('estado') is-invalid @enderror" 
                required>
    
            <option value="">-- Seleccione un estado --</option>
    
            <option value="ACTIVO" 
                {{ old('estado', $user->estado) == 'ACTIVO' ? 'selected' : '' }}>
                ACTIVO
            </option>
    
            <option value="PENDIENTE" 
                {{ old('estado', $user->estado) == 'PENDIENTE' ? 'selected' : '' }}>
                PENDIENTE
            </option>
    
            <option value="NO ACTIVO" 
                {{ old('estado', $user->estado) == 'NO ACTIVO' ? 'selected' : '' }}>
                NO ACTIVO
            </option>
        </select>
    
        @error('estado')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>
