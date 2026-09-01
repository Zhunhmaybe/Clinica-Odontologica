@extends('layouts.nav-bar')
@section('title', 'Registrar nuevo paciente')
@push('styles')
    @vite('resources/css/components/pacientes/create.css')
@endpush
@section('content')
<div class="page-title">Registrar nuevo paciente</div>
<div class="page-subtitle">Completa los datos del paciente para agregarlo al directorio.</div>

@if (session('success'))
<div class="alert alert-success text-center mb-3">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger mb-3">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="panel-card">

    <div class="panel-header">
        <h5>Formulario de Registro</h5>
        <a href="{{ route('pacientes.index') }}" class="btn-link-soft">Volver al directorio</a>
    </div>

    <form method="POST" action="{{ route('pacientes.store') }}">
        @csrf

        <div class="form-grid">

            <div>
                <div class="section-title">1. Datos del Paciente</div>

                <div class="mb-3">
                    <label for="cedula" class="form-label">Cédula / DNI</label>
                    <input type="text" class="form-control @error('cedula') is-invalid @enderror"
                        id="cedula" name="cedula" value="{{ old('cedula', request('cedula')) }}"
                        maxlength="10" required>

                    @error('cedula')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nombres" class="form-label">Nombres</label>
                    <input type="text" class="form-control @error('nombres') is-invalid @enderror"
                        id="nombres" name="nombres" value="{{ old('nombres') }}" maxlength="100"
                        required>
                    @error('nombres')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="apellidos" class="form-label">Apellidos</label>
                    <input type="text" class="form-control @error('apellidos') is-invalid @enderror"
                        id="apellidos" name="apellidos" value="{{ old('apellidos') }}" maxlength="100"
                        required>
                    @error('apellidos')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono / Celular</label>
                    <input type="text" class="form-control @error('telefono') is-invalid @enderror"
                        id="telefono" name="telefono" value="{{ old('telefono') }}" maxlength="10"
                        required>
                    @error('telefono')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico (opcional)</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" name="email" value="{{ old('email') }}"
                        placeholder="ejemplo@correo.com">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            <div>
                <div class="section-title">2. Información adicional</div>

                <div class="mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                    <input type="date"
                        class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                        id="fecha_nacimiento" name="fecha_nacimiento"
                        value="{{ old('fecha_nacimiento') }}" required>
                    @error('fecha_nacimiento')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección / Notas (opcional)</label>
                    <textarea class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion"
                        rows="4" placeholder="Ej: Calle / Sector / Referencias...">{{ old('direccion') }}</textarea>
                    @error('direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input @error('consentimiento_lopdp') is-invalid @enderror"
                        type="checkbox" id="consentimiento_lopdp" name="consentimiento_lopdp"
                        value="1" {{ old('consentimiento_lopdp') ? 'checked' : '' }} required>
                    <label class="form-check-label lopdp" for="consentimiento_lopdp">
                        El paciente acepta la <strong>política de tratamiento de datos (LOPDP)</strong>.
                    </label>
                    @error('consentimiento_lopdp')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('pacientes.index') }}" class="btn-link-soft">Cancelar</a>

            <button type="submit" class="btn-gold">
                Guardar Paciente
            </button>
        </div>

    </form>

</div>
@endsection
