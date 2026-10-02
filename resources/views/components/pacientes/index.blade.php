@extends('layouts.nav-bar')
@section('title', 'Directorio de Pacientes')

@push('styles')
    @vite(['resources/css/components/pacientes/index.css'])
@endpush

@section('content')
<div class="w-100">
    <h4 class="pacientes-title">Directorio de Pacientes</h4>

    <div class="row g-4 w-100 m-0">
        <!-- COLUMNA IZQUIERDA: LISTADO DE PACIENTES -->
        <div class="col-12 col-lg-4 ps-0 pe-lg-3 pe-0">
            <div class="pacientes-panel">
                <input type="text" 
                       id="buscarPacienteInput"
                       class="paciente-search-input" 
                       placeholder="Buscar por nombre o cédula">

                @if ($pacientes->isEmpty())
                    <div class="text-center mt-5">
                        <p class="text-muted mb-4">No hay pacientes registrados</p>
                        <a href="{{ route('pacientes.create') }}" class="btn btn-gold">
                            Crear Paciente
                        </a>
                    </div>
                @else
                    <div class="paciente-lista-container" id="listaPacientesContainer">
                        @foreach ($pacientes as $p)
                            <a href="{{ route('pacientes.index', ['paciente' => $p->id]) }}"
                               class="paciente-item {{ optional($pacienteSeleccionado)->id === $p->id ? 'active' : '' }}"
                               data-nombre="{{ strtolower($p->nombres . ' ' . $p->apellidos) }}"
                               data-cedula="{{ $p->cedula }}">
                                <div class="avatar">
                                    {{ strtoupper(substr($p->nombres, 0, 1)) }}
                                </div>
                                <div class="text-truncate">
                                    <strong>{{ $p->nombres }} {{ $p->apellidos }}</strong><br>
                                    <small>ID: {{ $p->cedula }}</small>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="text-center mt-3 pt-3 border-top">
                        <a href="{{ route('pacientes.create') }}" class="btn btn-gold w-100">
                            + Crear Paciente
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- COLUMNA DERECHA: INFORMACIÓN DETALLADA -->
        <div class="col-12 col-lg-8 pe-0 ps-lg-3 ps-0">
            <div class="pacientes-panel">
                @if (!$pacienteSeleccionado)
                    <div class="paciente-empty-state">
                        Selecciona un paciente del listado
                    </div>
                @else
                    <h5 class="fw-bold mb-4" style="color: #1f2937;">Información del Paciente</h5>

                    <div class="card bg-light border-0 mb-4 p-3 rounded-4 shadow-sm">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-stethoscope me-1"></i> Acciones Clínicas
                        </h6>

                        @can('ver-historia')
                        <a href="{{ route('historia_clinica.create', ['paciente_id' => $pacienteSeleccionado->id]) }}"
                           class="btn btn-success w-100 mb-2 py-2 shadow-sm text-white fw-bold">
                            <i class="fas fa-plus-circle me-2"></i> Nueva Historia Clínica
                        </a>
                        @endcan

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('pacientes.citas', $pacienteSeleccionado->id) }}"
                               class="btn btn-outline-primary flex-grow-1 bg-white">
                                📅 Ver Citas
                            </a>

                            <a href="{{ route('pacientes.historia', $pacienteSeleccionado->id) }}"
                               class="btn btn-outline-secondary flex-grow-1 bg-white">
                                📋 Ver Historiales
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('pacientes.update', $pacienteSeleccionado->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold small">Cédula</label>
                            <input class="form-control paciente-form-control bg-light" disabled value="{{ $pacienteSeleccionado->cedula }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold small">Nombre Completo</label>
                            <input class="form-control paciente-form-control bg-light"
                                   value="{{ $pacienteSeleccionado->nombres }} {{ $pacienteSeleccionado->apellidos }}"
                                   disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold small">Teléfono</label>
                            <input class="form-control paciente-form-control" name="telefono"
                                   value="{{ $pacienteSeleccionado->telefono }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold small">Correo Electrónico</label>
                            <input class="form-control paciente-form-control" name="email"
                                   value="{{ $pacienteSeleccionado->email }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-semibold small">Dirección / Notas</label>
                            <textarea class="form-control paciente-form-control" name="direccion" rows="3">{{ $pacienteSeleccionado->direccion }}</textarea>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('pacientes.index') }}" class="btn btn-light rounded-pill px-4 me-2">
                                Cancelar
                            </a>

                            <button type="submit" class="btn btn-gold">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Filtro instantáneo de pacientes en vivo
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('buscarPacienteInput');
        const container = document.getElementById('listaPacientesContainer');
        if (!input || !container) return;

        input.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const items = container.querySelectorAll('.paciente-item');

            items.forEach(function(item) {
                const nombre = item.getAttribute('data-nombre') || '';
                const cedula = item.getAttribute('data-cedula') || '';
                if (nombre.includes(query) || cedula.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection