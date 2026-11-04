@extends('layouts.nav-bar')
@section('title', 'Registrar Nueva Cita')

@push('styles')
    @vite('resources/css/components/citas/create.css')
@endpush

@section('content')
<div class="citas-container">

    <!-- Encabezado -->
    <div class="mb-4">
        <h4 class="fw-bold mb-1 text-dark">Registrar nueva cita</h4>
        <p class="text-muted mb-0" style="font-size: 14.5px;">Aquí puedes gestionar tus próximas citas.</p>
    </div>

    {{-- Alertas del sistema --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <!-- PANEL 1: BUSCAR CLIENTE -->
    <div class="panel mb-4">
        <h5 class="fw-bold mb-3 text-dark">Buscar Cliente</h5>
        
        <div class="row g-2 align-items-center">
            <div class="col-md-8 col-sm-12 search-wrapper">
                <input type="text"
                       id="inputBuscarPaciente"
                       class="form-control"
                       placeholder="Ingrese la cédula del cliente"
                       value="{{ optional($paciente)->cedula ?? request('cedula') }}"
                       autocomplete="off">
                
                {{-- Contenedor de sugerencias en vivo --}}
                <div id="sugerenciasDropdown" class="suggestions-dropdown d-none"></div>
            </div>

            <div class="col-md-2 col-6">
                <button type="button" id="btnBuscar" class="btn btn-gold w-100">
                    Buscar
                </button>
            </div>

            <div class="col-md-2 col-6">
                <a href="{{ route('pacientes.create') }}" class="btn btn-outline-create w-100">
                    Crear
                </a>
            </div>
        </div>

        {{-- Mensaje de estado de búsqueda --}}
        <div id="mensajeBusqueda" class="mt-2 d-none"></div>
    </div>

    <!-- PANEL 2: FORMULARIO DE CITA (DOS COLUMNAS) -->
    <div class="panel">
        <form method="POST" action="{{ route('citas.store') }}" id="formCita">
            @csrf

            <!-- Campo oculto del ID del paciente seleccionado -->
            <input type="hidden" name="paciente_id" id="paciente_id" value="{{ optional($paciente)->id }}" required>

            <div class="row g-4">
                <!-- COLUMNA 1: 1. DATOS DEL PACIENTE -->
                <div class="col-lg-6 col-md-12">
                    <span class="section-title">1. Datos del Paciente</span>

                    <div class="mb-3">
                        <label for="campo_cedula" class="form-label">Cédula / DNI</label>
                        <input type="text"
                               id="campo_cedula"
                               class="form-control patient-field"
                               value="{{ optional($paciente)->cedula }}"
                               placeholder="Cédula del paciente"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label for="campo_nombre" class="form-label">Nombre y Apellido</label>
                        <input type="text"
                               id="campo_nombre"
                               class="form-control patient-field"
                               value="{{ $paciente ? trim($paciente->nombres . ' ' . $paciente->apellidos) : '' }}"
                               placeholder="Nombre completo"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label for="campo_telefono" class="form-label">Teléfono</label>
                        <input type="text"
                               id="campo_telefono"
                               class="form-control patient-field"
                               value="{{ optional($paciente)->telefono }}"
                               placeholder="Teléfono"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label for="campo_email" class="form-label">Correo Electrónico</label>
                        <input type="email"
                               id="campo_email"
                               class="form-control patient-field"
                               value="{{ optional($paciente)->email }}"
                               placeholder="Correo electrónico"
                               readonly>
                    </div>
                </div>

                <!-- COLUMNA 2: 2. DETALLES DE LA CITA -->
                <div class="col-lg-6 col-md-12">
                    <span class="section-title">2. Detalles de la Cita</span>

                    <div class="mb-3">
                        <label for="especialidad_id" class="form-label">Especialidad</label>
                        <select name="especialidad_id" id="especialidad_id" class="form-select @error('especialidad_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Seleccione una especialidad</option>
                            @forelse ($especialidades as $e)
                                <option value="{{ $e->id }}" {{ old('especialidad_id') == $e->id ? 'selected' : '' }}>
                                    {{ $e->nombre }}
                                </option>
                            @empty
                                <option value="" disabled>No hay especialidades registradas (agréguelas en Gestión)</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="doctor_id" class="form-label">Doctor Asignado</label>
                        <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Seleccione un doctor</option>
                            @forelse ($doctores as $d)
                                <option value="{{ $d->id }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nombre }}
                                </option>
                            @empty
                                <option value="" disabled>No hay doctores disponibles (asigne el rol doctor en Gestión)</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha y Hora Inicio <span class="text-danger">*</span></label>
                        <input type="datetime-local"
                               name="fecha_inicio"
                               id="fecha_inicio"
                               class="form-control @error('fecha_inicio') is-invalid @enderror"
                               value="{{ old('fecha_inicio') }}"
                               required>
                        @error('fecha_inicio')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none" id="fechaErrorJs">La fecha y hora de la cita debe ser posterior a la fecha y hora actual.</div>
                        <small class="text-muted" style="font-size: 11.5px;">Debe ser posterior a la fecha y hora actual.</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="motivo" class="form-label mb-0">Motivo de Consulta</label>
                            <span class="text-muted" id="motivoCounter" style="font-size: 11px;">0/500</span>
                        </div>
                        <textarea name="motivo"
                                  id="motivo"
                                  class="form-control @error('motivo') is-invalid @enderror"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Describa el motivo de la consulta (solo letras y números)...">{{ old('motivo') }}</textarea>
                        @error('motivo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none" id="motivoErrorJs">El motivo solo permite letras y números, sin caracteres especiales.</div>
                        <small class="text-muted" style="font-size: 11.5px;">Solo letras y números permitidos (sin caracteres especiales).</small>
                    </div>
                </div>
            </div>

            <!-- Botones inferiores -->
            <div class="text-end mt-4 pt-2">
                <a href="{{ route('citas.index') }}" class="btn btn-outline-secondary px-4 me-2 rounded-pill" style="font-weight: 500;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-gold">
                    Confirmar y Agendar
                </button>
            </div>
        </form>
    </div>

</div>

<!-- SCRIPT DE BÚSQUEDA EN TIEMPO REAL Y AUTOCOMPLETADO -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar = document.getElementById('inputBuscarPaciente');
    const dropdown = document.getElementById('sugerenciasDropdown');
    const btnBuscar = document.getElementById('btnBuscar');
    const mensajeBusqueda = document.getElementById('mensajeBusqueda');

    const campoId = document.getElementById('paciente_id');
    const campoCedula = document.getElementById('campo_cedula');
    const campoNombre = document.getElementById('campo_nombre');
    const campoTelefono = document.getElementById('campo_telefono');
    const campoEmail = document.getElementById('campo_email');
    const formCita = document.getElementById('formCita');

    let debounceTimer = null;

    function seleccionarPaciente(paciente) {
        campoId.value = paciente.id;
        campoCedula.value = paciente.cedula || '';
        campoNombre.value = paciente.nombre_completo || (paciente.nombres + ' ' + (paciente.apellidos || '')).trim();
        campoTelefono.value = paciente.telefono || 'Sin teléfono';
        campoEmail.value = paciente.email || 'Sin correo';

        inputBuscar.value = paciente.cedula || paciente.nombre_completo;
        dropdown.classList.add('d-none');
        dropdown.innerHTML = '';

        mensajeBusqueda.className = 'mt-2 alert alert-success py-1 px-3 mb-0 d-inline-block small';
        mensajeBusqueda.innerHTML = '✓ Paciente seleccionado: <strong>' + campoNombre.value + '</strong>';
        mensajeBusqueda.classList.remove('d-none');
    }

    async function buscarPacientes(query, autoSelectIfSingle = false) {
        const q = query.trim();
        if (q.length === 0) {
            dropdown.classList.add('d-none');
            dropdown.innerHTML = '';
            return;
        }

        try {
            const url = "{{ route('citas.buscar_pacientes') }}?q=" + encodeURIComponent(q);
            const res = await fetch(url);
            const data = await res.json();

            if (autoSelectIfSingle && data.length === 1) {
                seleccionarPaciente(data[0]);
                return;
            }

            if (data.length === 0) {
                dropdown.innerHTML = `
                    <div class="p-3 text-center text-muted small">
                        <div>No se encontraron pacientes que coincidan con <strong>"${q}"</strong></div>
                        <a href="{{ route('pacientes.create') }}" class="btn btn-sm btn-outline-primary mt-2 rounded-pill">
                            + Crear Nuevo Paciente
                        </a>
                    </div>
                `;
                dropdown.classList.remove('d-none');
            } else {
                dropdown.innerHTML = data.map(p => `
                    <div class="suggestion-item" data-patient='${JSON.stringify(p).replace(/'/g, "&apos;")}'>
                        <div>
                            <span class="badge-cedula me-2">${p.cedula}</span>
                            <span class="patient-main">${p.nombre_completo}</span>
                        </div>
                        <div class="patient-sub text-end">
                            ${p.telefono ? '<span>📞 ' + p.telefono + '</span>' : ''}
                        </div>
                    </div>
                `).join('');
                dropdown.classList.remove('d-none');

                // Asignar eventos de click a cada item
                dropdown.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const paciente = JSON.parse(this.getAttribute('data-patient'));
                        seleccionarPaciente(paciente);
                    });
                });
            }
        } catch (error) {
            console.error('Error buscando pacientes:', error);
        }
    }

    // Evento de escritura con debounce
    inputBuscar.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = this.value;
        debounceTimer = setTimeout(() => {
            buscarPacientes(query, false);
        }, 220);
    });

    // Click en botón buscar
    btnBuscar.addEventListener('click', function () {
        buscarPacientes(inputBuscar.value, true);
    });

    // Enter en input de búsqueda
    inputBuscar.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarPacientes(inputBuscar.value, true);
        }
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function (e) {
        if (!inputBuscar.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('d-none');
        }
    });

    // Validación Fecha y Hora: Mayor a la fecha y hora actual
    const fechaInput = document.getElementById('fecha_inicio');
    const fechaErrorJs = document.getElementById('fechaErrorJs');

    function padZero(num) {
        return num.toString().padStart(2, '0');
    }

    function getFechaMinimaISO() {
        const now = new Date();
        now.setMinutes(now.getMinutes() + 1); // Al menos 1 minuto en el futuro
        const anio = now.getFullYear();
        const mes = padZero(now.getMonth() + 1);
        const dia = padZero(now.getDate());
        const horas = padZero(now.getHours());
        const minutos = padZero(now.getMinutes());
        return `${anio}-${mes}-${dia}T${horas}:${minutos}`;
    }

    if (fechaInput) {
        fechaInput.min = getFechaMinimaISO();

        fechaInput.addEventListener('focus', function () {
            this.min = getFechaMinimaISO();
        });

        fechaInput.addEventListener('change', function () {
            if (this.value) {
                const seleccion = new Date(this.value);
                const ahora = new Date();
                if (seleccion <= ahora) {
                    this.classList.add('is-invalid');
                    if (fechaErrorJs) {
                        fechaErrorJs.classList.remove('d-none');
                        fechaErrorJs.classList.add('d-block');
                    }
                } else {
                    this.classList.remove('is-invalid');
                    if (fechaErrorJs) {
                        fechaErrorJs.classList.remove('d-block');
                        fechaErrorJs.classList.add('d-none');
                    }
                }
            }
        });
    }

    // Auto-adaptar campo de motivo y bloquear caracteres especiales (solo letras, números y espacios)
    const motivoTextarea = document.getElementById('motivo');
    const motivoCounter = document.getElementById('motivoCounter');
    const motivoErrorJs = document.getElementById('motivoErrorJs');

    if (motivoTextarea) {
        function limpiarMotivo(element) {
            // Verificar si contenía caracteres especiales
            const contieneEspeciales = /[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s]/.test(element.value);
            if (contieneEspeciales) {
                if (motivoErrorJs) {
                    motivoErrorJs.classList.remove('d-none');
                    motivoErrorJs.classList.add('d-block');
                }
                setTimeout(() => {
                    if (motivoErrorJs) {
                        motivoErrorJs.classList.remove('d-block');
                        motivoErrorJs.classList.add('d-none');
                    }
                }, 2500);
            }

            // Remover caracteres especiales, permitiendo letras (con tildes, diéresis y ñ), números y espacios
            const limpio = element.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
            if (element.value !== limpio) {
                element.value = limpio;
            }

            if (motivoCounter) {
                motivoCounter.textContent = `${element.value.length}/500`;
            }

            element.style.height = 'auto';
            element.style.height = Math.max(80, element.scrollHeight + 4) + 'px';
        }

        motivoTextarea.addEventListener('input', function () {
            limpiarMotivo(this);
        });

        motivoTextarea.addEventListener('paste', function () {
            setTimeout(() => limpiarMotivo(this), 10);
        });

        // Inicializar si ya tiene valor
        limpiarMotivo(motivoTextarea);
    }

    // Validación antes de enviar el formulario
    formCita.addEventListener('submit', function (e) {
        if (!campoId.value || campoId.value === '') {
            e.preventDefault();
            alert('Por favor busque y seleccione un paciente antes de agendar la cita.');
            inputBuscar.focus();
            inputBuscar.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (fechaInput && fechaInput.value) {
            const fechaSeleccionada = new Date(fechaInput.value);
            const fechaActual = new Date();
            if (fechaSeleccionada <= fechaActual) {
                e.preventDefault();
                fechaInput.classList.add('is-invalid');
                if (fechaErrorJs) {
                    fechaErrorJs.classList.remove('d-none');
                    fechaErrorJs.classList.add('d-block');
                }
                alert('La fecha y hora de la cita debe ser posterior a la fecha y hora actual.');
                fechaInput.focus();
                return;
            }
        }

        if (motivoTextarea && motivoTextarea.value) {
            if (/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s]/.test(motivoTextarea.value)) {
                e.preventDefault();
                motivoTextarea.classList.add('is-invalid');
                if (motivoErrorJs) {
                    motivoErrorJs.classList.remove('d-none');
                    motivoErrorJs.classList.add('d-block');
                }
                alert('El motivo de la consulta solo puede contener letras y números.');
                motivoTextarea.focus();
                return;
            }
        }
    });
});
</script>
@endsection