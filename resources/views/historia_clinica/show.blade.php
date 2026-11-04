@extends('layouts.nav-bar')

@section('title', 'Historia Clínica HCU-033 - ' . ($historia->paciente->nombres ?? ''))

@push('styles')
    @vite(['resources/css/historia-clinica.css'])
@endpush

@section('content')
@php
    $anamnesis = $historia->anamnesis;
    $vitales = $historia->signosVitales->first();
    $estoma = $historia->examenEstomatognatico;
    $indicadores = $historia->indicadoresSalud;
    $antPer = $anamnesis->antecedentes_personales ?? ['no_refiere' => true, 'items' => []];
    $antFam = $anamnesis->antecedentes_familiares ?? ['no_refiere' => true, 'items' => []];
@endphp

<div class="container-fluid py-4">

    {{-- Header Banner HCU-033 --}}
    <div class="hc-header-banner">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <div class="d-flex gap-2 mb-2 align-items-center flex-wrap">
                    <span class="badge bg-dark">SNS-MSP / HCU-form.033 / {{ $historia->schema_version ?? '2021' }}</span>
                    <span class="badge bg-light text-dark">HCU Nº: {{ $historia->numero_historia }}</span>
                    <span class="badge bg-{{ $historia->estado === 'abierta' ? 'success' : ($historia->estado === 'borrador' ? 'warning text-dark' : 'secondary') }}">
                        {{ strtoupper($historia->estado ?? 'ABIERTA') }}
                    </span>
                    @if($historia->embarazada)
                    <span class="badge bg-danger"><i class="fas fa-baby me-1"></i> Gestante (Vigencia: 9 meses)</span>
                    @else
                    <span class="badge bg-light text-dark"><i class="fas fa-calendar-alt me-1 text-primary"></i> Vigencia: 365 días</span>
                    @endif
                </div>
                <h2><i class="fas fa-file-medical-alt"></i> Expediente Clínico Odontológico</h2>
                <p class="mb-0 opacity-90">
                    <i class="fas fa-user-md me-1"></i>
                    Dr(a). {{ $historia->profesional->nombre ?? 'No asignado' }} {{ $historia->profesional->apellido ?? '' }}
                    ({{ $historia->codigo_profesional ?? 'MSP' }}) |
                    <i class="fas fa-calendar me-1 ms-2"></i>
                    Apertura: {{ $historia->fecha_atencion ? $historia->fecha_atencion->format('d/m/Y') : '-' }}
                    {{ $historia->hora_apertura ? 'a las ' . $historia->hora_apertura : '' }} |
                    Vigencia hasta: <strong>{{ $historia->vigente_hasta ? $historia->vigente_hasta->format('d/m/Y') : '-' }}</strong>
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('historia_clinica.index', ['paciente_id' => $historia->paciente_id]) }}" class="hc-btn-action btn btn-light">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                @can('editar-historia')
                <a href="{{ route('historia_clinica.odontograma', $historia->id) }}" class="hc-btn-action hc-btn-warning">
                    <i class="fas fa-tooth"></i> Editor Odontograma
                </a>
                @endcan
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 rounded-3 alert-dismissible fade show shadow-sm">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        {{-- COLUMNA IZQUIERDA (8 cols) --}}
        <div class="col-lg-8">

            {{-- BLOQUE A: Datos del Establecimiento y Paciente --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-a" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-user-circle"></i> A. DATOS DEL ESTABLECIMIENTO Y PACIENTE
                </div>
                <div class="row mb-2 small text-muted border-bottom pb-2">
                    <div class="col-md-5"><strong>Institución:</strong> {{ $historia->institucion_sistema }}</div>
                    <div class="col-md-5"><strong>Establecimiento:</strong> {{ $historia->establecimiento_salud }}</div>
                    <div class="col-md-2"><strong>Hoja Nº:</strong> {{ $historia->numero_hoja }}</div>
                </div>
                <div class="row mt-3">
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
                                <small>Cédula / Archivo</small>
                                <strong>{{ $historia->paciente->cedula ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="hc-info-box">
                            <div class="hc-info-box-icon icon-success"><i class="fas fa-birthday-cake"></i></div>
                            <div class="hc-info-box-content">
                                <small>Edad al Abrir</small>
                                <strong>{{ $historia->edad_al_abrir ?? ($historia->paciente->fecha_nacimiento ? $historia->paciente->fecha_nacimiento->age : '-') }} Años</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUES B y C: Motivo de Consulta y Enfermedad Actual --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-b" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-comment-medical"></i> B y C. MOTIVO DE CONSULTA Y ENFERMEDAD ACTUAL
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold text-primary mb-1">B. Motivo de Consulta:</h6>
                    <p class="mb-0 p-2 bg-light rounded-3 fst-italic">"{{ $anamnesis->motivo_consulta ?? 'No registrado' }}"</p>
                </div>
                <div>
                    <h6 class="fw-bold text-primary mb-1">C. Enfermedad o Problema Actual:</h6>
                    <p class="mb-2">{{ $anamnesis->enfermedad_actual ?? 'NA' }}</p>
                    @if(!empty($anamnesis->enfermedad_actual_estructurada))
                        @php $est = array_filter($anamnesis->enfermedad_actual_estructurada); @endphp
                        @if(!empty($est))
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach($est as $k => $v)
                            <span class="badge bg-light text-dark border"><strong>{{ ucfirst($k) }}:</strong> {{ $v }}</span>
                            @endforeach
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- BLOQUES D y E: Antecedentes Personales y Familiares (JSONB) --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-d" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-heartbeat"></i> D y E. ANTECEDENTES PATOLÓGICOS PERSONALES Y FAMILIARES
                </div>
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold mb-2">D. Antecedentes Personales</h6>
                        @if(!empty($antPer['no_refiere']) || empty($antPer['items']))
                            <span class="text-muted"><i class="fas fa-check-circle text-success me-1"></i> No refiere antecedentes personales</span>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($antPer['items'] as $it)
                                <li class="list-group-item px-0 py-1 bg-transparent">
                                    <span class="hc-patologia-badge">{{ $it['codigo'] }}. {{ $it['etiqueta'] }}</span>
                                    <small class="d-block text-muted ms-2">{{ $it['descripcion'] }}</small>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2">E. Antecedentes Familiares</h6>
                        @if(!empty($antFam['no_refiere']) || empty($antFam['items']))
                            <span class="text-muted"><i class="fas fa-check-circle text-success me-1"></i> No refiere antecedentes familiares</span>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($antFam['items'] as $it)
                                <li class="list-group-item px-0 py-1 bg-transparent">
                                    <span class="badge bg-warning text-dark">{{ $it['codigo'] }}. {{ $it['etiqueta'] }}</span>
                                    <small class="d-block text-muted ms-2">{{ $it['descripcion'] }}</small>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- BLOQUE G: Examen del Sistema Estomatognático (JSONB) --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-g" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-teeth"></i> G. EXAMEN DEL SISTEMA ESTOMATOGNÁTICO
                </div>
                @if(!$estoma || $estoma->sin_patologia || empty($estoma->hallazgos))
                    <p class="mb-0 text-success fw-semibold">
                        <i class="fas fa-check-circle me-1"></i> Sin patología aparente en las 12 regiones estomatognáticas.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">Código</th>
                                    <th style="width: 220px;">Región Anatómica</th>
                                    <th>Descripción de la Patología</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estoma->hallazgos as $h)
                                <tr>
                                    <td class="fw-bold text-center">{{ $h['codigo'] }}</td>
                                    <td class="fw-semibold">{{ $h['region'] }}</td>
                                    <td>{{ $h['descripcion'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- BLOQUES L y M: Exámenes Complementarios --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-a" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-x-ray"></i> L y M. PLAN DE DIAGNÓSTICO Y EXÁMENES COMPLEMENTARIOS
                </div>
                @if($historia->examenes && $historia->examenes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Pedido / Indicación (L)</th>
                                    <th>Informe / Resultado (M)</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historia->examenes as $ex)
                                <tr>
                                    <td class="fw-semibold">{{ $ex->nombre_examen ?? $ex->tipo }}</td>
                                    <td>{{ $ex->pedido_descripcion ?? '-' }}</td>
                                    <td>{{ $ex->informe_resultado ?? 'Pendiente' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $ex->estado === 'informado' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($ex->estado) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i> No se solicitaron exámenes complementarios.</p>
                @endif
            </div>

            {{-- BLOQUE N: Diagnósticos CIE-10 --}}
            <div class="hc-glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-diagnoses text-danger me-2"></i>N. Diagnósticos Odontológicos (CIE-10)</h5>
                    @can('editar-historia')
                    <button class="btn btn-sm hc-btn-action hc-btn-danger" data-bs-toggle="modal" data-bs-target="#modalNuevoDiagnostico">
                        <i class="fas fa-plus me-1"></i> Agregar CIE-10
                    </button>
                    @endcan
                </div>
                @if($historia->diagnosticos && $historia->diagnosticos->count() > 0)
                    @foreach($historia->diagnosticos as $diagnostico)
                    <div class="hc-treatment-item" style="border-left-color: var(--hc-danger);">
                        <div class="hc-treatment-header">
                            <div>
                                @if($diagnostico->cie10_codigo)
                                <span class="badge bg-dark me-1">CIE-10: {{ $diagnostico->cie10_codigo }}</span>
                                @endif
                                <span class="badge bg-{{ $diagnostico->tipo === 'DEF' ? 'danger' : 'warning text-dark' }}">
                                    {{ $diagnostico->tipo === 'DEF' ? 'DEF (Definitivo)' : 'PRE (Presuntivo)' }}
                                </span>
                                @if($diagnostico->pieza_dental)
                                <span class="badge bg-light text-dark border ms-1">Pieza {{ $diagnostico->pieza_dental }}</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $diagnostico->created_at ? $diagnostico->created_at->format('d/m/Y') : '' }}</small>
                        </div>
                        <p class="mb-0 fw-semibold">{{ $diagnostico->descripcion }}</p>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center mb-0"><i class="fas fa-info-circle me-2"></i>No hay diagnósticos CIE-10 registrados</p>
                @endif
            </div>

            {{-- BLOQUE P: Sesiones de Tratamiento --}}
            <div class="hc-glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-notes-medical me-2" style="color: var(--hc-treatment);"></i>P. Sesiones de Tratamiento y Prescripciones</h5>
                    @can('editar-historia')
                    <button class="btn btn-sm hc-btn-action hc-btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoTratamiento">
                        <i class="fas fa-plus me-1"></i> Nueva Sesión
                    </button>
                    @endcan
                </div>
                @if($historia->sesionesTratamiento && $historia->sesionesTratamiento->count() > 0)
                    @foreach($historia->sesionesTratamiento as $sesion)
                    <div class="hc-treatment-item">
                        <div class="hc-treatment-header">
                            <h6>
                                <i class="fas fa-calendar-check me-2 text-primary"></i>
                                Sesión #{{ $sesion->numero_sesion }} &mdash; {{ $sesion->fecha ? $sesion->fecha->format('d/m/Y') : '' }}
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-user-md me-1"></i>{{ $sesion->firma_profesional }} ({{ $sesion->codigo_profesional ?? 'MSP' }})
                            </small>
                        </div>
                        @if($sesion->diagnosticos_complicaciones)
                        <p class="mb-1 small"><strong>Diagnósticos / Complicaciones:</strong> {{ $sesion->diagnosticos_complicaciones }}</p>
                        @endif
                        <p class="mb-1"><strong>Procedimientos:</strong> {{ $sesion->procedimientos }}</p>
                        @if($sesion->prescripciones)
                        <p class="mb-0 text-muted"><strong>Prescripciones:</strong> {{ $sesion->prescripciones }}</p>
                        @endif
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center mb-0"><i class="fas fa-info-circle me-2"></i>No hay sesiones de tratamiento registradas</p>
                @endif
            </div>

        </div>

        {{-- COLUMNA DERECHA (4 cols) --}}
        <div class="col-lg-4">

            {{-- BLOQUE F: Constantes Vitales --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-f" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-stethoscope"></i> F. CONSTANTES VITALES
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="hc-vital-stat mb-0">
                            <i class="fas fa-heartbeat text-danger"></i>
                            <div class="value">{{ $vitales->presion_arterial ?? '-' }}</div>
                            <div class="label">P. Arterial (mmHg)</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hc-vital-stat mb-0">
                            <i class="fas fa-heart text-danger"></i>
                            <div class="value">{{ $vitales->pulso ?? '-' }}</div>
                            <div class="label">Pulso (lpm)</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hc-vital-stat mb-0">
                            <i class="fas fa-thermometer-half text-warning"></i>
                            <div class="value">{{ $vitales->temperatura ?? '-' }} °C</div>
                            <div class="label">Temperatura</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hc-vital-stat mb-0">
                            <i class="fas fa-lungs text-info"></i>
                            <div class="value">{{ $vitales->frecuencia_respiratoria ?? '-' }}</div>
                            <div class="label">Frec. Resp. (rpm)</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUES I y J: Resumen de Indicadores e Índices CPO-ceo --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-b" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-chart-pie"></i> I y J. INDICADORES E ÍNDICES CPO-ceo
                </div>
                @if($indicadores)
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-primary">Índice CPO-D (Adultos)</span>
                            <span class="badge bg-primary">Total: {{ $indicadores->cpo_total }}</span>
                        </div>
                        <div class="d-flex justify-content-around text-center small border-top pt-2">
                            <div><strong>C:</strong> {{ $indicadores->cpo_c }}</div>
                            <div><strong>P:</strong> {{ $indicadores->cpo_p }}</div>
                            <div><strong>O:</strong> {{ $indicadores->cpo_o }}</div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-secondary">Índice ceo-d (Temporales)</span>
                            <span class="badge bg-secondary">Total: {{ $indicadores->ceo_total }}</span>
                        </div>
                        <div class="d-flex justify-content-around text-center small border-top pt-2">
                            <div><strong>c:</strong> {{ $indicadores->ceo_c }}</div>
                            <div><strong>e:</strong> {{ $indicadores->ceo_e }}</div>
                            <div><strong>o:</strong> {{ $indicadores->ceo_o }}</div>
                        </div>
                    </div>

                    <div class="small">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Promedio Placa / Cálculo / Gingivitis:</span>
                            <strong>{{ $indicadores->placa_total }} / {{ $indicadores->calculo_total }} / {{ $indicadores->gingivitis_total }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Enfermedad Periodontal:</span>
                            <strong class="text-capitalize">{{ $indicadores->enfermedad_periodontal ?? 'Sana' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span>Maloclusión:</span>
                            <strong class="text-uppercase">{{ str_replace('_', ' ', $indicadores->maloclusion ?? 'Normal') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span>Fluorosis:</span>
                            <strong class="text-capitalize">{{ $indicadores->fluorosis ?? 'Normal' }}</strong>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">Sin indicadores registrados.</p>
                @endif
            </div>

            {{-- Resumen de Hallazgos del Odontograma (Bloque H) --}}
            <div class="hc-glass-card">
                <div class="hc-card-header section-a" style="border-radius: 12px; margin: -24px -24px 20px -24px;">
                    <i class="fas fa-tooth"></i> H. HALLAZGOS EN ODONTOGRAMA
                </div>
                @if($historia->odontogramaRegistros && $historia->odontogramaRegistros->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($historia->odontogramaRegistros->groupBy('pieza') as $pieza => $regs)
                            @php $ult = $regs->last(); @endphp
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center bg-transparent">
                                <div>
                                    <strong>Pieza {{ $pieza }}</strong>
                                    <small class="text-muted d-block">
                                        Caras: {{ $regs->pluck('superficie')->filter()->implode(', ') ?: 'Completa' }}
                                    </small>
                                </div>
                                <span class="badge bg-{{ $ult->tipo_registro === 'patologia' ? 'danger' : 'primary' }}">
                                    {{ $ult->hallazgo }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 small">Todas las piezas registradas como sanas.</p>
                @endif
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

{{-- Modal Nuevo Diagnóstico CIE-10 --}}
<div class="modal fade" id="modalNuevoDiagnostico" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--hc-danger) 0%, var(--hc-danger-light) 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-diagnoses me-2"></i>Agregar Diagnóstico CIE-10</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('historia_clinica.diagnostico.store', $historia->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="hc-form-label">Código CIE-10 Odontológico</label>
                        <select name="cie10_codigo" class="form-select hc-form-select"
                                onchange="const opt=this.options[this.selectedIndex]; if(opt.dataset.nombre) document.getElementById('modal_diag_desc').value=opt.dataset.nombre;">
                            <option value="">-- Seleccione CIE-10 --</option>
                            @foreach($cie10Catalogo ?? [] as $cie)
                            <option value="{{ $cie->codigo }}" data-nombre="{{ $cie->nombre }}">
                                {{ $cie->codigo }} - {{ $cie->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="hc-form-label">Tipo *</label>
                            <select name="tipo" class="form-select hc-form-select" required>
                                <option value="PRE">PRE (Presuntivo)</option>
                                <option value="DEF">DEF (Definitivo)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="hc-form-label">Pieza Dental (opcional)</label>
                            <input type="number" name="pieza_dental" class="form-control hc-form-control" placeholder="Ej: 16">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="hc-form-label">Descripción del Diagnóstico *</label>
                        <textarea name="descripcion" id="modal_diag_desc" class="form-control hc-form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="hc-btn-action hc-btn-danger">Guardar Diagnóstico</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Nueva Sesión de Tratamiento (Bloque P) --}}
<div class="modal fade" id="modalNuevoTratamiento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--hc-treatment) 0%, #7aa58f 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-notes-medical me-2"></i>Registrar Sesión de Tratamiento (Bloque P)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('historia_clinica.tratamiento.store', $historia->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="hc-form-label">Fecha de la Sesión *</label>
                        <input type="date" name="fecha" class="form-control hc-form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="hc-form-label">Diagnósticos y Complicaciones</label>
                        <input type="text" name="diagnosticos_complicaciones" class="form-control hc-form-control" placeholder="Sin complicaciones">
                    </div>
                    <div class="mb-3">
                        <label class="hc-form-label">Procedimientos Realizados *</label>
                        <textarea name="procedimiento" class="form-control hc-form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="hc-form-label">Prescripciones (Fármacos, dosis, indicaciones)</label>
                        <textarea name="prescripcion" class="form-control hc-form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="hc-btn-action hc-btn-success">Firmar y Guardar Sesión</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
