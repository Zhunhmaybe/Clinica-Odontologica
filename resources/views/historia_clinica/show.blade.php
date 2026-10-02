@extends('layouts.nav-bar')

@section('title', 'Historia Clínica - ' . ($historia->paciente->nombres ?? ''))

@push('styles')
<link rel="stylesheet" href="{{ asset('build/assets/historia-clinica.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Header Banner --}}
    <div class="hc-header-banner">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2><i class="fas fa-file-medical-alt"></i> Historia Clínica Odontológica</h2>
                <div class="d-flex gap-2 mb-2">
                    <span class="badge bg-light text-dark">{{ $historia->numero_historia }}</span>
                    <span class="badge bg-{{ $historia->estado_historia === 'abierta' ? 'success' : 'secondary' }}">
                        {{ ucfirst($historia->estado_historia) }}
                    </span>
                </div>
                <p class="mb-0 opacity-90">
                    <i class="fas fa-user-md me-1"></i>
                    Dr. {{ $historia->profesional->nombre ?? 'No asignado' }} |
                    <i class="fas fa-calendar me-1 ms-2"></i>
                    {{ $historia->fecha_atencion ? $historia->fecha_atencion->format('d/m/Y') : '-' }}
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('historia_clinica.index') }}" class="hc-btn-action btn btn-light">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                @can('editar-historia')
                <a href="{{ route('historia_clinica.odontograma', $historia->id) }}" class="hc-btn-action hc-btn-warning">
                    <i class="fas fa-tooth"></i> Odontograma
                </a>
                @endcan
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 rounded-3 alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        {{-- COLUMNA IZQUIERDA (8 cols) --}}
        <div class="col-lg-8">

            {{-- Datos del Paciente --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-a" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-user-circle"></i> DATOS DEL PACIENTE
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="hc-info-box">
                            <div class="hc-info-box-icon icon-primary"><i class="fas fa-user"></i></div>
                            <div class="hc-info-box-content">
                                <small>Paciente</small>
                                <strong>{{ $historia->paciente->nombres ?? '' }} {{ $historia->paciente->apellidos ?? '' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="hc-info-box">
                            <div class="hc-info-box-icon icon-info"><i class="fas fa-id-card"></i></div>
                            <div class="hc-info-box-content">
                                <small>Cédula</small>
                                <strong>{{ $historia->paciente->cedula ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="hc-info-box">
                            <div class="hc-info-box-icon icon-success"><i class="fas fa-birthday-cake"></i></div>
                            <div class="hc-info-box-content">
                                <small>Edad</small>
                                <strong>{{ $historia->paciente->fecha_nacimiento ? $historia->paciente->fecha_nacimiento->age . ' años' : '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="hc-info-box">
                            <div class="hc-info-box-icon icon-warning"><i class="fas fa-phone"></i></div>
                            <div class="hc-info-box-content">
                                <small>Teléfono</small>
                                <strong>{{ $historia->paciente->telefono ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="hc-info-box">
                            <div class="hc-info-box-icon icon-danger"><i class="fas fa-envelope"></i></div>
                            <div class="hc-info-box-content">
                                <small>Email</small>
                                <strong>{{ $historia->paciente->email ?? 'No registrado' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Motivo de Consulta --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-b" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-comment-medical"></i> MOTIVO DE CONSULTA
                </div>
                <p class="mb-0">{{ $historia->motivo_consulta }}</p>
            </div>

            {{-- Enfermedad Actual --}}
            @if($historia->enfermedad_actual)
            <div class="hc-glass-card">
                <div class="hc-card-header section-b" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-disease"></i> ENFERMEDAD ACTUAL
                </div>
                <p class="mb-0">{{ $historia->enfermedad_actual }}</p>
            </div>
            @endif

            {{-- Antecedentes --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-d" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-heartbeat"></i> ANTECEDENTES MÉDICOS
                </div>
                <div class="mb-3">
                    @if($historia->cardiopatias)
                        <span class="hc-patologia-badge"><i class="fas fa-heart"></i> Cardiopatías</span>
                    @endif
                    @if($historia->diabetes)
                        <span class="hc-patologia-badge"><i class="fas fa-tint"></i> Diabetes</span>
                    @endif
                    @if($historia->hipertension)
                        <span class="hc-patologia-badge"><i class="fas fa-heartbeat"></i> Hipertensión</span>
                    @endif
                    @if($historia->tuberculosis)
                        <span class="hc-patologia-badge"><i class="fas fa-lungs"></i> Tuberculosis</span>
                    @endif
                    @if(!$historia->cardiopatias && !$historia->diabetes && !$historia->hipertension && !$historia->tuberculosis)
                        <span class="text-muted"><i class="fas fa-check-circle me-1"></i> Sin antecedentes patológicos registrados</span>
                    @endif
                </div>
                @if($historia->alergias)
                <p><strong>Alergias:</strong> {{ $historia->alergias }}</p>
                @endif
                @if($historia->antecedentes_otros)
                <p class="mb-0"><strong>Otros:</strong> {{ $historia->antecedentes_otros }}</p>
                @endif
            </div>

            {{-- Examen Estomatognático --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-g" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-teeth"></i> EXAMEN ESTOMATOGNÁTICO
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th class="bg-light">Labios</th><td>{{ $historia->labios ?? 'No evaluado' }}</td>
                            <th class="bg-light">Lengua</th><td>{{ $historia->lengua ?? 'No evaluado' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Paladar</th><td>{{ $historia->paladar ?? 'No evaluado' }}</td>
                            <th class="bg-light">Piso de Boca</th><td>{{ $historia->piso_boca ?? 'No evaluado' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Encías</th><td>{{ $historia->encias ?? 'No evaluado' }}</td>
                            <th class="bg-light">Carrillos</th><td>{{ $historia->carrillos ?? 'No evaluado' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Orofaringe</th><td>{{ $historia->orofaringe ?? 'No evaluado' }}</td>
                            <th class="bg-light">ATM</th><td>{{ $historia->atm ?? 'No evaluado' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Diagnósticos --}}
            <div class="hc-glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-diagnoses text-danger me-2"></i>Diagnósticos</h5>
                    @can('editar-historia')
                    <button class="btn btn-sm hc-btn-action hc-btn-danger" data-bs-toggle="modal" data-bs-target="#modalNuevoDiagnostico">
                        <i class="fas fa-plus me-1"></i> Agregar
                    </button>
                    @endcan
                </div>
                @if($historia->diagnosticos && $historia->diagnosticos->count() > 0)
                    @foreach($historia->diagnosticos as $diagnostico)
                    <div class="hc-treatment-item" style="border-left-color: var(--hc-danger);">
                        <div class="hc-treatment-header">
                            <h6>{{ ucfirst($diagnostico->tipo) }}</h6>
                            <small class="text-muted">{{ $diagnostico->created_at ? $diagnostico->created_at->format('d/m/Y') : '' }}</small>
                        </div>
                        <p class="mb-0">{{ $diagnostico->descripcion }}</p>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center mb-0"><i class="fas fa-info-circle me-2"></i>No hay diagnósticos registrados</p>
                @endif
            </div>

            {{-- Tratamientos --}}
            <div class="hc-glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-pills me-2" style="color: var(--hc-treatment);"></i>Tratamientos</h5>
                    @can('editar-historia')
                    <button class="btn btn-sm hc-btn-action hc-btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoTratamiento">
                        <i class="fas fa-plus me-1"></i> Agregar
                    </button>
                    @endcan
                </div>
                @if($historia->tratamientos && $historia->tratamientos->count() > 0)
                    @foreach($historia->tratamientos as $tratamiento)
                    <div class="hc-treatment-item">
                        <div class="hc-treatment-header">
                            <h6><i class="fas fa-calendar-check me-2 text-primary"></i>Sesión {{ $loop->iteration }} - {{ $tratamiento->fecha ? $tratamiento->fecha->format('d/m/Y') : '' }}</h6>
                            <small><i class="fas fa-user-md me-1"></i>{{ $tratamiento->firma_profesional }}</small>
                        </div>
                        <p class="mb-1"><strong>Procedimiento:</strong> {{ $tratamiento->procedimiento }}</p>
                        @if($tratamiento->prescripcion)
                        <p class="mb-0 text-muted"><strong>Prescripción:</strong> {{ $tratamiento->prescripcion }}</p>
                        @endif
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center mb-0"><i class="fas fa-info-circle me-2"></i>No hay tratamientos registrados</p>
                @endif
            </div>

        </div>

        {{-- COLUMNA DERECHA (4 cols) --}}
        <div class="col-lg-4">

            {{-- Constantes Vitales --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-f" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-stethoscope"></i> CONSTANTES VITALES
                </div>
                <div class="hc-vital-stat">
                    <i class="fas fa-thermometer-half text-danger"></i>
                    <div class="value">{{ $historia->temperatura ?? '-' }} °C</div>
                    <div class="label">Temperatura</div>
                </div>
                <div class="hc-vital-stat">
                    <i class="fas fa-heartbeat text-danger"></i>
                    <div class="value">{{ $historia->presion_arterial ?? '-' }}</div>
                    <div class="label">Presión Arterial (mmHg)</div>
                </div>
                <div class="hc-vital-stat">
                    <i class="fas fa-heart text-danger"></i>
                    <div class="value">{{ $historia->pulso ?? '-' }}</div>
                    <div class="label">Pulso (lpm)</div>
                </div>
                <div class="hc-vital-stat">
                    <i class="fas fa-lungs text-info"></i>
                    <div class="value">{{ $historia->frecuencia_respiratoria ?? '-' }}</div>
                    <div class="label">Frecuencia Respiratoria (rpm)</div>
                </div>
            </div>

            {{-- Observaciones --}}
            @if($historia->observaciones)
            <div class="hc-glass-card">
                <div class="hc-card-header section-obs" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-comment-medical"></i> OBSERVACIONES
                </div>
                <p class="mb-0">{{ $historia->observaciones }}</p>
            </div>
            @endif

        </div>
    </div>

</div>

{{-- Modal Nuevo Diagnóstico --}}
<div class="modal fade" id="modalNuevoDiagnostico" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--hc-danger) 0%, var(--hc-danger-light) 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-diagnoses me-2"></i>Agregar Diagnóstico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('historia_clinica.diagnostico.store', $historia->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="hc-form-label">Tipo</label>
                        <select name="tipo" class="form-select hc-form-select" required>
                            <option value="presuntivo">Presuntivo</option>
                            <option value="definitivo">Definitivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="hc-form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control hc-form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="hc-btn-action hc-btn-danger">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Nuevo Tratamiento --}}
<div class="modal fade" id="modalNuevoTratamiento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--hc-treatment) 0%, #7aa58f 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-pills me-2"></i>Agregar Tratamiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('historia_clinica.tratamiento.store', $historia->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="hc-form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control hc-form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="hc-form-label">Procedimiento</label>
                        <textarea name="procedimiento" class="form-control hc-form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="hc-form-label">Prescripción (opcional)</label>
                        <textarea name="prescripcion" class="form-control hc-form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="hc-btn-action hc-btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
