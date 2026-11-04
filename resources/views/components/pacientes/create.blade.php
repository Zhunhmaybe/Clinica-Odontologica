@extends('layouts.nav-bar')
@section('title', 'Registrar Nuevo Paciente')

@push('styles')
    @vite('resources/css/components/pacientes/create.css')
@endpush

@section('content')
<div class="pacientes-container">

    <!-- Encabezado de la página -->
    <div class="mb-3">
        <h4 class="page-title">Registrar nuevo paciente</h4>
        <p class="page-subtitle">Completa los datos del paciente para agregarlo al directorio.</p>
    </div>

    {{-- Mensajes de estado --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <!-- TARJETA DEL FORMULARIO -->
    <div class="panel-card">

        <div class="panel-header">
            <h5>Formulario de Registro</h5>
            <a href="{{ route('pacientes.index') }}" class="btn-back-link">
                <i class="fas fa-arrow-left me-1"></i> Volver al directorio
            </a>
        </div>

        <form method="POST" action="{{ route('pacientes.store') }}" id="formRegistrarPaciente" novalidate>
            @csrf

            <div class="row g-4">

                <!-- COLUMNA 1: 1. DATOS DEL PACIENTE -->
                <div class="col-lg-6 col-md-12">
                    <span class="section-title">1. Datos del Paciente</span>

                    <!-- CÉDULA -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="cedula" class="form-label mb-0">Cédula / DNI <span class="text-danger">*</span></label>
                            <span class="input-counter" id="cedulaCounter">0/10</span>
                        </div>
                        <input type="text"
                               class="form-control @error('cedula') is-invalid @enderror"
                               id="cedula"
                               name="cedula"
                               value="{{ old('cedula', request('cedula')) }}"
                               placeholder="Ingrese los 10 dígitos numéricos"
                               maxlength="10"
                               minlength="10"
                               inputmode="numeric"
                               required
                               autofocus>
                        <div id="cedulaValidationMsg" class="small mt-1" style="display: none;"></div>
                        @error('cedula')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- NOMBRES -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="nombres" class="form-label mb-0">Nombres <span class="text-danger">*</span></label>
                            <span class="input-counter" id="nombresCounter">0/100</span>
                        </div>
                        <input type="text"
                               class="form-control @error('nombres') is-invalid @enderror"
                               id="nombres"
                               name="nombres"
                               value="{{ old('nombres') }}"
                               placeholder="Nombres del paciente"
                               maxlength="100"
                               required>
                        @error('nombres')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- APELLIDOS -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="apellidos" class="form-label mb-0">Apellidos <span class="text-danger">*</span></label>
                            <span class="input-counter" id="apellidosCounter">0/100</span>
                        </div>
                        <input type="text"
                               class="form-control @error('apellidos') is-invalid @enderror"
                               id="apellidos"
                               name="apellidos"
                               value="{{ old('apellidos') }}"
                               placeholder="Apellidos del paciente"
                               maxlength="100"
                               required>
                        @error('apellidos')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- TELÉFONO -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="telefono" class="form-label mb-0">Teléfono / Celular <span class="text-danger">*</span></label>
                            <span class="input-counter" id="telefonoCounter">0/10</span>
                        </div>
                        <input type="text"
                               class="form-control @error('telefono') is-invalid @enderror"
                               id="telefono"
                               name="telefono"
                               value="{{ old('telefono') }}"
                               placeholder="Ej. 0987654321"
                               maxlength="10"
                               inputmode="numeric"
                               required>
                        @error('telefono')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- CORREO ELECTRÓNICO -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="email" class="form-label mb-0">Correo Electrónico (Opcional)</label>
                            <span class="input-counter" id="emailCounter">0/100</span>
                        </div>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="ejemplo@gmail.com, usuario@outlook.com..."
                               maxlength="100">
                        <small class="input-hint">@gmail.com, @hotmail.com, @outlook.com, etc</small>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- COLUMNA 2: 2. INFORMACIÓN ADICIONAL -->
                <div class="col-lg-6 col-md-12">
                    <span class="section-title">2. Información Adicional</span>

                    <!-- FECHA DE NACIMIENTO -->
                    <div class="mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                               id="fecha_nacimiento"
                               name="fecha_nacimiento"
                               value="{{ old('fecha_nacimiento') }}"
                               max="{{ date('Y-m-d', strtotime('-1 year')) }}"
                               required>
                        @error('fecha_nacimiento')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- DIRECCIÓN / NOTAS (EXTENDIBLE Y AUTO-ADAPTABLE) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="direccion" class="form-label mb-0">Dirección / Notas (Opcional)</label>
                            <span class="input-counter" id="direccionCounter">0/1000</span>
                        </div>
                        <textarea class="form-control @error('direccion') is-invalid @enderror"
                                  id="direccion"
                                  name="direccion"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Ej: Calle Principal y Secundaria #123, Sector Norte... Observaciones clínicas o datos del paciente...">{{ old('direccion') }}</textarea>
                        @error('direccion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tarjeta de Consentimiento LOPDP -->
                    <div class="lopdp-card">
                        <div class="form-check mb-0">
                            <input class="form-check-input @error('consentimiento_lopdp') is-invalid @enderror"
                                   type="checkbox"
                                   id="consentimiento_lopdp"
                                   name="consentimiento_lopdp"
                                   value="1"
                                   {{ old('consentimiento_lopdp') ? 'checked' : '' }}
                                   required>
                            <label class="form-check-label lopdp-text" for="consentimiento_lopdp">
                                El paciente acepta la <strong>política de tratamiento de datos personales (LOPDP)</strong>. <span class="text-danger">*</span>
                            </label>
                        </div>
                        @error('consentimiento_lopdp')
                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            <!-- Botones de Acción -->
            <div class="actions">
                <a href="{{ route('pacientes.index') }}" class="btn-outline-cancel">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-gold" id="btnGuardarPaciente">
                    <i class="fas fa-save me-2"></i>Guardar Paciente
                </button>
            </div>

        </form>

    </div>

</div>

<!-- SCRIPTS DE VALIDACIÓN INTERACTIVA EN TIEMPO REAL -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cedulaInput = document.getElementById('cedula');
    const cedulaCounter = document.getElementById('cedulaCounter');
    const cedulaMsg = document.getElementById('cedulaValidationMsg');

    const nombresInput = document.getElementById('nombres');
    const nombresCounter = document.getElementById('nombresCounter');

    const apellidosInput = document.getElementById('apellidos');
    const apellidosCounter = document.getElementById('apellidosCounter');

    const telefonoInput = document.getElementById('telefono');
    const telefonoCounter = document.getElementById('telefonoCounter');

    const emailInput = document.getElementById('email');
    const emailCounter = document.getElementById('emailCounter');

    const direccionInput = document.getElementById('direccion');
    const direccionCounter = document.getElementById('direccionCounter');

    const fechaNacInput = document.getElementById('fecha_nacimiento');

    // 1. CÉDULA ECUATORIANA: Solo números y algoritmo Módulo 10 en vivo
    function validarCedulaEcuatorianaJS(cedula) {
        if (!/^\d{10}$/.test(cedula)) return false;
        const prov = parseInt(cedula.substring(0, 2), 10);
        if ((prov < 1 || prov > 24) && prov !== 30) return false;
        if (parseInt(cedula[2], 10) >= 6) return false;

        const coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        for (let i = 0; i < 9; i++) {
            let prod = parseInt(cedula[i], 10) * coef[i];
            suma += (prod >= 10) ? (prod - 9) : prod;
        }
        const residuo = suma % 10;
        const digitoVerificador = (residuo === 0) ? 0 : (10 - residuo);
        return digitoVerificador === parseInt(cedula[9], 10);
    }

    if (cedulaInput) {
        cedulaInput.addEventListener('input', function () {
            // Solo dígitos numéricos
            this.value = this.value.replace(/\D/g, '');
            if (cedulaCounter) cedulaCounter.textContent = `${this.value.length}/10`;

            if (this.value.length === 10) {
                if (validarCedulaEcuatorianaJS(this.value)) {
                    cedulaMsg.style.display = 'block';
                    cedulaMsg.className = 'small mt-1 text-success fw-semibold';
                    cedulaMsg.innerHTML = '<i class="fas fa-check-circle me-1"></i> Cédula ecuatoriana válida.';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    cedulaMsg.style.display = 'block';
                    cedulaMsg.className = 'small mt-1 text-danger fw-semibold';
                    cedulaMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Cédula no válida según el registro ecuatoriano.';
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            } else if (this.value.length > 0) {
                cedulaMsg.style.display = 'block';
                cedulaMsg.className = 'small mt-1 text-muted';
                cedulaMsg.innerHTML = `Faltan ${10 - this.value.length} dígitos para completar la cédula.`;
                this.classList.remove('is-valid');
            } else {
                cedulaMsg.style.display = 'none';
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
        // Disparar en carga si hay valor previo
        if (cedulaInput.value) cedulaInput.dispatchEvent(new Event('input'));
    }

    // 2. NOMBRES: Solo letras y espacios (máximo 100 caracteres)
    if (nombresInput) {
        nombresInput.addEventListener('input', function () {
            // Reemplazar números y caracteres especiales, permitiendo acentos y ñ
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            if (nombresCounter) nombresCounter.textContent = `${this.value.length}/100`;
        });
        if (nombresCounter && nombresInput.value) nombresCounter.textContent = `${nombresInput.value.length}/100`;
    }

    // 3. APELLIDOS: Solo letras y espacios (máximo 100 caracteres)
    if (apellidosInput) {
        apellidosInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            if (apellidosCounter) apellidosCounter.textContent = `${this.value.length}/100`;
        });
        if (apellidosCounter && apellidosInput.value) apellidosCounter.textContent = `${apellidosInput.value.length}/100`;
    }

    // 4. TELÉFONO: Solo números y máximo 10 dígitos
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            if (telefonoCounter) telefonoCounter.textContent = `${this.value.length}/10`;
        });
        if (telefonoCounter && telefonoInput.value) telefonoCounter.textContent = `${telefonoInput.value.length}/10`;
    }

    // 5. CORREO ELECTRÓNICO: Contador y validación de extensiones normales
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            if (emailCounter) emailCounter.textContent = `${this.value.length}/100`;
        });
        if (emailCounter && emailInput.value) emailCounter.textContent = `${emailInput.value.length}/100`;
    }

    // 6. DIRECCIÓN / NOTAS: Auto-adaptable y extendible al escribir
    if (direccionInput) {
        function autoExpandTextarea(element) {
            element.style.height = 'auto';
            element.style.height = Math.max(95, element.scrollHeight + 4) + 'px';
        }

        direccionInput.addEventListener('input', function () {
            autoExpandTextarea(this);
            if (direccionCounter) direccionCounter.textContent = `${this.value.length}/1000`;
        });

        // Ejecutar al cargar la página
        autoExpandTextarea(direccionInput);
        if (direccionCounter && direccionInput.value) direccionCounter.textContent = `${direccionInput.value.length}/1000`;
    }

    // 7. FECHA DE NACIMIENTO: Validación de mínimo 1 año de edad
    if (fechaNacInput) {
        fechaNacInput.addEventListener('change', function () {
            if (!this.value) return;
            const fechaNac = new Date(this.value + 'T00:00:00');
            const hoy = new Date();
            const unAnoAtras = new Date(hoy.getFullYear() - 1, hoy.getMonth(), hoy.getDate());

            if (fechaNac > unAnoAtras) {
                this.classList.add('is-invalid');
                this.value = '';
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // Validación general al enviar formulario
    const form = document.getElementById('formRegistrarPaciente');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (cedulaInput && cedulaInput.value && !validarCedulaEcuatorianaJS(cedulaInput.value)) {
                e.preventDefault();
                cedulaInput.focus();
                alert('Por favor ingrese una cédula ecuatoriana válida de 10 dígitos.');
                return false;
            }
        });
    }
});
</script>
@endsection
