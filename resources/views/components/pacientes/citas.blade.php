@extends('layouts.nav-bar')
@section('title', 'Citas del Paciente')

@push('styles')
    @vite(['resources/css/components/pacientes/index.css'])
@endpush

@section('content')
<div class="w-100 py-2">
    <!-- ENCABEZADO -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0b4f79;">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Citas del Paciente
            </h4>
            <span class="text-muted fs-6">
                <strong>{{ $paciente->nombres }} {{ $paciente->apellidos }}</strong> &mdash; Cédula: {{ $paciente->cedula }}
            </span>
        </div>

        <a href="{{ route('pacientes.index', ['paciente' => $paciente->id]) }}"
           class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Volver al Paciente
        </a>
    </div>

    <!-- PANEL PRINCIPAL CON LISTA DE CITAS -->
    <div class="pacientes-panel" style="min-height: auto;">
        @if ($paciente->citas->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                <h5 class="fw-semibold">Este paciente no tiene citas registradas.</h5>
                <p class="small text-muted mb-4">Puedes agendar una nueva cita en el módulo general de citas.</p>
                @can('crear-citas')
                <a href="{{ route('citas.index') }}" class="btn btn-gold">
                    <i class="fas fa-plus-circle me-1"></i> Ir a Agendar Cita
                </a>
                @endcan
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">
                    Total de citas registradas: <strong>{{ $paciente->citas->count() }}</strong>
                </span>
                @can('crear-citas')
                <a href="{{ route('citas.index') }}" class="btn btn-sm btn-gold">
                    <i class="fas fa-plus me-1"></i> Nueva Cita
                </a>
                @endcan
            </div>

            @foreach ($paciente->citas as $cita)
                <div class="cita-card">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-tooth text-primary me-2"></i>{{ $cita->especialidad->nombre ?? 'Odontología General' }}
                        </h6>
                        <span class="badge-estado estado-{{ $cita->estado }}">
                            <i class="fas fa-circle fa-2xs me-1"></i>{{ ucfirst($cita->estado) }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <small class="text-muted fw-semibold d-block">Doctor / Profesional</small>
                            <span class="fw-medium text-dark">
                                <i class="fas fa-user-md me-1 text-secondary"></i>{{ $cita->doctor->nombre ?? 'Por asignar' }}
                            </span>
                        </div>

                        <div class="col-12 col-md-4">
                            <small class="text-muted fw-semibold d-block">Fecha y Hora</small>
                            <span class="fw-medium text-dark">
                                <i class="fas fa-clock me-1 text-secondary"></i>{{ \Carbon\Carbon::parse($cita->fecha_inicio)->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <div class="col-12 col-md-4">
                            <small class="text-muted fw-semibold d-block">Motivo de Consulta</small>
                            <span class="text-muted">
                                {{ $cita->motivo ?? 'Consulta general' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
