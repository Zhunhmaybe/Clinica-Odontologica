@extends('layouts.nav-bar')

@section('title', 'Nueva Historia Clínica HCU-033')

@push('styles')
    @vite(['resources/css/historia-clinica.css'])
@endpush

@section('content')
@php
    $esEdicion = isset($historia) && $historia->id;
    $anamnesis = $esEdicion ? $historia->anamnesis : null;
    $enfEstruct = $anamnesis->enfermedad_actual_estructurada ?? [];
    $antPerData = $anamnesis->antecedentes_personales ?? ['no_refiere' => true, 'items' => []];
    $antFamData = $anamnesis->antecedentes_familiares ?? ['no_refiere' => true, 'items' => []];
    $antPerChecks = collect($antPerData['items'] ?? [])->pluck('descripcion', 'codigo')->toArray();
    $antFamChecks = collect($antFamData['items'] ?? [])->pluck('descripcion', 'codigo')->toArray();

    $vitales = $esEdicion ? $historia->signosVitales->first() : null;
    $estoma = $esEdicion ? $historia->examenEstomatognatico : null;
    $estomaChecks = $estoma ? collect($estoma->hallazgos ?? [])->pluck('descripcion', 'codigo')->toArray() : [];

    $indicadores = $esEdicion ? $historia->indicadoresSalud : null;
    $higieneGuardada = $indicadores ? collect($indicadores->higiene_oral['piezas_evaluadas'] ?? [])->keyBy('grupo')->toArray() : [];

    $examenesGuardados = ($esEdicion && $historia->examenes) ? $historia->examenes : collect();
    $diagnosticosGuardados = ($esEdicion && $historia->diagnosticos) ? $historia->diagnosticos : collect();
    $primeraSesion = $esEdicion ? $historia->sesionesTratamiento->where('numero_sesion', 1)->first() : null;
@endphp
<div class="container-fluid py-4">

    {{-- Header MSP HCU-form.033/2021 --}}
    <div class="hc-header-banner">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="msp-code-badge">SNS-MSP / HCU-form.033 / 2021</span>
                    @if($esEdicion)
                    <span class="badge bg-warning text-dark fw-bold">
                        <i class="fas fa-edit me-1"></i> EDITANDO BORRADOR ({{ $historia->numero_historia }})
                    </span>
                    @endif
                    <span class="badge bg-light text-dark" id="badge-vigencia">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i> Vigencia: 365 días
                    </span>
                </div>
                <h2 class="mb-1">
                    <i class="fas fa-file-medical"></i>
                    {{ $esEdicion ? 'Continuar Borrador de Historia Clínica Única' : 'Apertura de Historia Clínica Única de Odontología' }}
                </h2>
                <p class="mb-0 opacity-90">
                    Paciente: <strong>{{ $paciente->nombres }} {{ $paciente->apellidos }}</strong>
                    &bull; Cédula: <strong>{{ $paciente->cedula }}</strong>
                    &bull; Edad: <strong>{{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age . ' años' : 'No registrada' }}</strong>
                </p>
            </div>
            <div class="d-flex gap-2">
                @if($esEdicion)
                <a href="{{ route('historia_clinica.show', $historia->id) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fas fa-eye me-1"></i> Ver Vista Previa
                </a>
                @endif
                <a href="{{ route('historia_clinica.index', ['paciente_id' => $paciente->id]) }}" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Historial
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 rounded-3 shadow-sm">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Por favor corrija los siguientes campos:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 rounded-3 shadow-sm">
        <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Stepper Visual de 6 Pasos (Bloques A - P) --}}
    <div class="hc-stepper" id="stepper-nav">
        <div class="hc-step active" data-step="0" onclick="irAPaso(0)">
            <div class="hc-step-number">1</div>
            <div class="hc-step-label"><br>Admisión y Consulta</div>
        </div>
        <div class="hc-step" data-step="1" onclick="irAPaso(1)">
            <div class="hc-step-number">2</div>
            <div class="hc-step-label"><br>Antecedentes</div>
        </div>
        <div class="hc-step" data-step="2" onclick="irAPaso(2)">
            <div class="hc-step-number">3</div>
            <div class="hc-step-label"><br>Signos y Examen</div>
        </div>
        <div class="hc-step" data-step="3" onclick="irAPaso(3)">
            <div class="hc-step-number">4</div>
            <div class="hc-step-label"><br>Odontograma e Índices</div>
        </div>
        <div class="hc-step" data-step="4" onclick="irAPaso(4)">
            <div class="hc-step-number">5</div>
            <div class="hc-step-label"><br>Exámenes y CIE-10</div>
        </div>
        <div class="hc-step" data-step="5" onclick="irAPaso(5)">
            <div class="hc-step-number">6</div>
            <div class="hc-step-label"><br>Cierre y Sesión</div>
        </div>
    </div>

    <form action="{{ $esEdicion ? route('historia_clinica.update', $historia->id) : route('historia_clinica.store') }}" method="POST" id="form-historia">
        @csrf
        @if($esEdicion)
            @method('PUT')
        @endif
        <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">
        <input type="hidden" name="accion_guardar" id="accion_guardar" value="abierta">
        <input type="hidden" name="odontograma_json" id="odontograma_json" value="{{ old('odontograma_json', $odontogramaWizardJson ?? '[]') }}">

        {{-- ================================================================ --}}
        {{-- PASO 1: BLOQUES A, B, C (Datos Establecimiento/Paciente, Motivo y Enfermedad Actual) --}}
        {{-- ================================================================ --}}
        <div class="hc-step-content active" data-step="0">
            {{-- BLOQUE A --}}
            <div class="hc-card">
                <div class="hc-card-header section-a justify-content-between">
                    <span><i class="fas fa-hospital-user me-2"></i> A. DATOS DEL ESTABLECIMIENTO Y USUARIO / PACIENTE</span>
                    <span class="badge bg-white text-dark">Obligatorio</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <label class="hc-form-label">Institución del Sistema</label>
                            <input type="text" name="institucion_sistema" class="form-control hc-form-control"
                                   value="{{ old('institucion_sistema', $historia->institucion_sistema ?? 'RED PRIVADA COMPLEMENTARIA') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="hc-form-label">Establecimiento de Salud</label>
                            <input type="text" name="establecimiento_salud" class="form-control hc-form-control"
                                   value="{{ old('establecimiento_salud', $historia->establecimiento_salud ?? 'DANNY LARA DENTAL SOLUTIONS') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="hc-form-label">UNICÓDIGO</label>
                            <input type="text" name="unicodigo" class="form-control hc-form-control"
                                   value="{{ old('unicodigo', $historia->unicodigo ?? '000000') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="hc-form-label">Nº Archivo</label>
                            <input type="text" name="numero_archivo" class="form-control hc-form-control"
                                   value="{{ old('numero_archivo', $historia->numero_archivo ?? $paciente->cedula) }}">
                        </div>
                    </div>

                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase">Nombres y Apellidos</small>
                                <strong class="text-dark">{{ $paciente->nombres }} {{ $paciente->apellidos }}</strong>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase">Cédula / HCU</small>
                                <strong class="text-dark">{{ $paciente->cedula }}</strong>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase">Edad (Años)</small>
                                <strong class="text-dark">{{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age . ' Años' : 'N/A' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="hc-form-label">Fecha de Apertura *</label>
                            <input type="date" name="fecha_atencion" class="form-control hc-form-control"
                                   value="{{ old('fecha_atencion', ($esEdicion && $historia->fecha_atencion) ? $historia->fecha_atencion->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="hc-form-label">Hora</label>
                            <input type="time" name="hora_apertura" class="form-control hc-form-control"
                                   value="{{ old('hora_apertura', ($esEdicion && $historia->hora_apertura) ? substr($historia->hora_apertura, 0, 5) : date('H:i')) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUE B --}}
            <div class="hc-card">
                <div class="hc-card-header section-azul justify-content-between">
                    <span><i class="fas fa-comment-medical me-2"></i> B. MOTIVO DE CONSULTA</span>
                    <small class="opacity-90">Anote la causa del problema en palabras del informante (entre comillas)</small>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-8">
                            <label class="hc-form-label">Descripción del Motivo de Consulta *</label>
                            <textarea name="motivo_consulta" id="motivo_consulta" class="form-control hc-form-control" rows="3" required
                                      placeholder='Ej: "Me duele la muela de arriba al tomar cosas frías desde hace 3 días"'>{{ old('motivo_consulta', $anamnesis->motivo_consulta ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="embarazada" id="embarazada" value="1"
                                           {{ old('embarazada', $historia->embarazada ?? false) ? 'checked' : '' }} onchange="actualizarBadgeVigencia()">
                                    <label class="form-check-label fw-bold text-dark" for="embarazada">
                                        <i class="fas fa-baby text-danger me-1"></i> ¿Paciente Embarazada?
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">Marcar solo si aplica atención prioritaria gestante.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUE C --}}
            <div class="hc-card">
                <div class="hc-card-header section-azul justify-content-between">
                    <span><i class="fas fa-notes-medical me-2"></i> C. ENFERMEDAD O PROBLEMA ACTUAL</span>
                    <small class="opacity-90">Cronología, localización, características, intensidad, causa aparente y síntomas asociados</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="hc-form-label">Relato Clínico de la Enfermedad Actual (Si no presenta, se registrará "NA")</label>
                        <textarea name="enfermedad_actual" class="form-control hc-form-control" rows="3"
                                  placeholder="Ej: Paciente refiere odontalgia localizada en cuadrante superior derecho de 72 horas de evolución, carácter pulsátil, intensidad moderada (6/10)...">{{ old('enfermedad_actual', ($anamnesis && $anamnesis->enfermedad_actual !== 'NA') ? $anamnesis->enfermedad_actual : '') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="hc-form-label small text-muted">Cronología (Tiempo)</label>
                            <input type="text" name="enf_cronologia" class="form-control form-control-sm hc-form-control"
                                   placeholder="Ej: 3 días" value="{{ old('enf_cronologia', $enfEstruct['cronologia'] ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="hc-form-label small text-muted">Localización</label>
                            <input type="text" name="enf_localizacion" class="form-control form-control-sm hc-form-control"
                                   placeholder="Ej: Cuadrante sup. derecho" value="{{ old('enf_localizacion', $enfEstruct['localizacion'] ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="hc-form-label small text-muted">Intensidad (EVA 1-10)</label>
                            <input type="text" name="enf_intensidad" class="form-control form-control-sm hc-form-control"
                                   placeholder="Ej: 7/10 Moderado" value="{{ old('enf_intensidad', $enfEstruct['intensidad'] ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="hc-form-label small text-muted">Causa Aparente / Síntomas</label>
                            <input type="text" name="enf_causa" class="form-control form-control-sm hc-form-control"
                                   placeholder="Ej: Estímulos térmicos / masticación" value="{{ old('enf_causa', $enfEstruct['causa_aparente'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente<i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- PASO 2: BLOQUES D y E (Antecedentes Patológicos Personales y Familiares JSONB) --}}
        {{-- ================================================================ --}}
        @php
            $noRefPer = empty($antPerChecks) ? true : false;
            $noRefFam = empty($antFamChecks) ? true : false;
        @endphp
        <div class="hc-step-content" data-step="1">
            {{-- BLOQUE D: ANTECEDENTES PERSONALES --}}
            <div class="hc-card">
                <div class="hc-card-header section-d justify-content-between flex-wrap gap-2">
                    <span><i class="fas fa-heartbeat me-2"></i> D. ANTECEDENTES PATOLÓGICOS PERSONALES</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="ant_per_no_refiere" id="ant_per_no_refiere" value="1"
                               {{ $noRefPer ? 'checked' : '' }} onchange="toggleNoRefiere('per', this.checked)">
                        <label class="form-check-label text-white fw-bold" for="ant_per_no_refiere">
                            No refiere antecedentes personales
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Desactive el interruptor superior si el paciente presenta alguna de las 10 condiciones del formulario 033 y escriba el detalle abajo.
                    </p>
                    <div class="row g-3" id="contenedor-ant-per" style="{{ $noRefPer ? 'opacity: 0.55; pointer-events: none;' : 'opacity: 1; pointer-events: auto;' }}">
                        @foreach($antecedentesCatalogo as $codigo => $etiqueta)
                        @php $checkedPer = isset($antPerChecks[$codigo]); @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="ant-item-card {{ $checkedPer ? 'selected' : '' }}" id="card-per-{{ $codigo }}">
                                <div class="form-check">
                                    <input class="form-check-input check-ant-per" type="checkbox"
                                           name="ant_per_checks[]" value="{{ $codigo }}" id="chk_per_{{ $codigo }}"
                                           {{ $checkedPer ? 'checked' : '' }}
                                           onchange="toggleAntCard('per', {{ $codigo }}, this.checked)">
                                    <label class="form-check-label fw-bold text-dark" for="chk_per_{{ $codigo }}">
                                        <span class="badge bg-secondary me-1">{{ $codigo }}</span> {{ $etiqueta }}
                                    </label>
                                </div>
                                <div class="mt-2 {{ $checkedPer ? '' : 'd-none' }}" id="desc-box-per-{{ $codigo }}">
                                    <input type="text" name="ant_per_desc[{{ $codigo }}]"
                                           class="form-control form-control-sm hc-form-control"
                                           value="{{ $antPerChecks[$codigo] ?? '' }}"
                                           placeholder="Especifique detalle, medicamento o tiempo...">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- BLOQUE E: ANTECEDENTES FAMILIARES --}}
            <div class="hc-card">
                <div class="hc-card-header section-d justify-content-between flex-wrap gap-2">
                    <span><i class="fas fa-users me-2"></i> E. ANTECEDENTES PATOLÓGICOS FAMILIARES</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="ant_fam_no_refiere" id="ant_fam_no_refiere" value="1"
                               {{ $noRefFam ? 'checked' : '' }} onchange="toggleNoRefiere('fam', this.checked)">
                        <label class="form-check-label text-white fw-bold" for="ant_fam_no_refiere">
                            No refiere antecedentes familiares
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="contenedor-ant-fam" style="{{ $noRefFam ? 'opacity: 0.55; pointer-events: none;' : 'opacity: 1; pointer-events: auto;' }}">
                        @foreach($antecedentesCatalogo as $codigo => $etiqueta)
                        @php $checkedFam = isset($antFamChecks[$codigo]); @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="ant-item-card {{ $checkedFam ? 'selected' : '' }}" id="card-fam-{{ $codigo }}">
                                <div class="form-check">
                                    <input class="form-check-input check-ant-fam" type="checkbox"
                                           name="ant_fam_checks[]" value="{{ $codigo }}" id="chk_fam_{{ $codigo }}"
                                           {{ $checkedFam ? 'checked' : '' }}
                                           onchange="toggleAntCard('fam', {{ $codigo }}, this.checked)">
                                    <label class="form-check-label fw-bold text-dark" for="chk_fam_{{ $codigo }}">
                                        <span class="badge bg-secondary me-1">{{ $codigo }}</span> {{ $etiqueta }}
                                    </label>
                                </div>
                                <div class="mt-2 {{ $checkedFam ? '' : 'd-none' }}" id="desc-box-fam-{{ $codigo }}">
                                    <input type="text" name="ant_fam_desc[{{ $codigo }}]"
                                           class="form-control form-control-sm hc-form-control"
                                           value="{{ $antFamChecks[$codigo] ?? '' }}"
                                           placeholder="Parentesco (ej: Madre, Padre, Abuelo) y detalle...">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente<i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- PASO 3: BLOQUES F y G (Constantes Vitales y Examen Estomatognático JSONB) --}}
        {{-- ================================================================ --}}
        @php
            $sinPatEstoma = empty($estomaChecks) ? true : false;
        @endphp
        <div class="hc-step-content" data-step="2">
            {{-- BLOQUE F: CONSTANTES VITALES --}}
            <div class="hc-card">
                <div class="hc-card-header section-f">
                    <i class="fas fa-stethoscope me-2"></i> F. CONSTANTES VITALES
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="hc-form-label">Presión Arterial Sistólica (mmHg)</label>
                            <input type="number" name="presion_sistolica" class="form-control hc-form-control"
                                   placeholder="120" min="50" max="260" value="{{ old('presion_sistolica', $vitales->presion_sistolica ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="hc-form-label">Presión Arterial Diastólica (mmHg)</label>
                            <input type="number" name="presion_diastolica" class="form-control hc-form-control"
                                   placeholder="80" min="30" max="160" value="{{ old('presion_diastolica', $vitales->presion_diastolica ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="hc-form-label">Pulso / F. Cardíaca (lpm)</label>
                            <input type="number" name="pulso" class="form-control hc-form-control"
                                   placeholder="72" min="30" max="220" value="{{ old('pulso', $vitales->pulso ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="hc-form-label">Temperatura (°C)</label>
                            <input type="number" step="0.1" name="temperatura" class="form-control hc-form-control"
                                   placeholder="36.5" min="34" max="42" value="{{ old('temperatura', $vitales->temperatura ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="hc-form-label">Frec. Respiratoria (rpm)</label>
                            <input type="number" name="frecuencia_respiratoria" class="form-control hc-form-control"
                                   placeholder="16" min="8" max="60" value="{{ old('frecuencia_respiratoria', $vitales->frecuencia_respiratoria ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUE G: EXAMEN DEL SISTEMA ESTOMATOGNÁTICO --}}
            <div class="hc-card">
                <div class="hc-card-header section-g justify-content-between flex-wrap gap-2">
                    <span><i class="fas fa-teeth-open me-2"></i> G. EXAMEN DEL SISTEMA ESTOMATOGNÁTICO</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="estoma_sin_patologia" id="estoma_sin_patologia" value="1"
                               {{ $sinPatEstoma ? 'checked' : '' }} onchange="toggleEstomaSinPatologia(this.checked)">
                        <label class="form-check-label text-white fw-bold" for="estoma_sin_patologia">
                            Sin patología aparente
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Si identifica alguna patología en los tejidos blandos o maxilares, desactive "Sin patología aparente", marque la región afectada (1 al 12) y descríbala abajo.
                    </p>
                    <div class="row g-3" id="contenedor-estoma" style="{{ $sinPatEstoma ? 'opacity: 0.55; pointer-events: none;' : 'opacity: 1; pointer-events: auto;' }}">
                        @foreach($regionesEstomatognaticas as $codigo => $region)
                        @php $checkedEst = isset($estomaChecks[$codigo]); @endphp
                        <div class="col-md-4 col-lg-3">
                            <div class="estoma-item-card {{ $checkedEst ? 'selected' : '' }}" id="card-estoma-{{ $codigo }}">
                                <div class="form-check">
                                    <input class="form-check-input check-estoma" type="checkbox"
                                           name="estoma_checks[]" value="{{ $codigo }}" id="chk_estoma_{{ $codigo }}"
                                           {{ $checkedEst ? 'checked' : '' }}
                                           onchange="toggleEstomaCard({{ $codigo }}, this.checked)">
                                    <label class="form-check-label fw-bold text-dark" for="chk_estoma_{{ $codigo }}">
                                        <span class="badge bg-secondary me-1">{{ $codigo }}</span> {{ $region }}
                                    </label>
                                </div>
                                <div class="mt-2 {{ $checkedEst ? '' : 'd-none' }}" id="desc-box-estoma-{{ $codigo }}">
                                    <input type="text" name="estoma_desc[{{ $codigo }}]"
                                           class="form-control form-control-sm hc-form-control"
                                           value="{{ $estomaChecks[$codigo] ?? '' }}"
                                           placeholder="Describa la patología en {{ strtolower($region) }}...">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente<i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- PASO 4: BLOQUES H, I, J, K (Odontograma Inmutable, Higiene Oral y CPO-ceo automático) --}}
        {{-- ================================================================ --}}
        <div class="hc-step-content" data-step="3">
            {{-- BLOQUE H y K: ODONTOGRAMA INTERACTIVO --}}
            <div class="hc-card">
                <div class="hc-card-header section-a justify-content-between flex-wrap gap-2">
                    <span><i class="fas fa-tooth me-2"></i> H. ODONTOGRAMA (INMUTABLE SEGÚN REGLA MSP)</span>
                    <div class="d-flex gap-3 small">
                        <span><i class="fas fa-square text-danger me-1"></i> Rojo = Patología Actual</span>
                        <span><i class="fas fa-square text-primary me-1"></i> Azul = Tratamiento Realizado</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-9 text-center">
                            {{-- Arcada Superior Permanente (18-11 | 21-28) --}}
                            <div class="mb-3">
                                <div class="small fw-bold text-muted mb-1">ARCADA SUPERIOR PERMANENTE</div>
                                @foreach([18,17,16,15,14,13,12,11] as $pieza)
                                    @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                                @endforeach
                                <span class="cuadrante-separador"></span>
                                @foreach([21,22,23,24,25,26,27,28] as $pieza)
                                    @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                                @endforeach
                            </div>

                            {{-- Arcadas Temporales / Niños (55-51 | 61-65 y 85-81 | 71-75) --}}
                            <div class="my-3 bg-light p-3 rounded-3 d-inline-block">
                                <div class="small fw-bold text-muted mb-1">DENTICIÓN TEMPORAL (NIÑOS)</div>
                                <div class="mb-2">
                                    @foreach([55,54,53,52,51] as $pieza)
                                        @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                                    @endforeach
                                    <span class="cuadrante-separador"></span>
                                    @foreach([61,62,63,64,65] as $pieza)
                                        @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                                    @endforeach
                                </div>
                                <div>
                                    @foreach([85,84,83,82,81] as $pieza)
                                        @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                                    @endforeach
                                    <span class="cuadrante-separador"></span>
                                    @foreach([71,72,73,74,75] as $pieza)
                                        @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                                    @endforeach
                                </div>
                            </div>

                            {{-- Arcada Inferior Permanente (48-41 | 31-38) --}}
                            <div class="mt-2">
                                <div class="small fw-bold text-muted mb-1">ARCADA INFERIOR PERMANENTE</div>
                                @foreach([48,47,46,45,44,43,42,41] as $pieza)
                                    @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                                @endforeach
                                <span class="cuadrante-separador"></span>
                                @foreach([31,32,33,34,35,36,37,38] as $pieza)
                                    @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                                @endforeach
                            </div>
                        </div>

                        {{-- Lista lateral de hallazgos marcados en el Wizard --}}
                        <div class="col-lg-3 mt-3 mt-lg-0">
                            <div class="border rounded-3 h-100 d-flex flex-column">
                                <div class="p-2 bg-dark text-white rounded-top-3 d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold"><i class="fas fa-list-ul me-1"></i> Hallazgos Marcados</span>
                                    <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" onclick="limpiarOdontogramaWizard()">
                                        Limpiar
                                    </button>
                                </div>
                                <div class="list-group list-group-flush flex-grow-1" id="wizard-lista-odontograma" style="max-height: 310px; overflow-y: auto;">
                                    <div class="text-center p-4 text-muted small">
                                        Haga clic sobre cualquier diente para pintar caries (rojo), obturaciones (azul), extracciones o movilidad.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUE I y J: INDICADORES DE SALUD BUCAL E ÍNDICES CPO-ceo --}}
            <div class="row g-3">
                {{-- Bloque I: Higiene Oral Simplificada --}}
                <div class="col-lg-7">
                    <div class="hc-card h-100">
                        <div class="hc-card-header section-f">
                            <i class="fas fa-teeth me-2"></i> I. INDICADORES DE SALUD BUCAL (HIGIENE ORAL SIMPLIFICADA)
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm higiene-table mb-3">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Piezas Índice</th>
                                            <th>Pieza Evaluada</th>
                                            <th>Placa (0-3)</th>
                                            <th>Cálculo (0-3)</th>
                                            <th>Gingivitis (0-1)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gruposHigiene as $clave => $meta)
                                        @php
                                            $filaHig = $higieneGuardada[$clave] ?? [];
                                            $piezaHig = old("higiene.{$clave}.pieza", $filaHig['pieza'] ?? $meta['piezas'][0]);
                                            $placaHig = old("higiene.{$clave}.placa", $filaHig['placa'] ?? '');
                                            $calcHig  = old("higiene.{$clave}.calculo", $filaHig['calculo'] ?? '');
                                            $gingHig  = old("higiene.{$clave}.gingivitis", $filaHig['gingivitis'] ?? '');
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">{{ $meta['label'] }}</td>
                                            <td>
                                                <select name="higiene[{{ $clave }}][pieza]" class="form-select form-select-sm text-center">
                                                    @foreach($meta['piezas'] as $p)
                                                    <option value="{{ $p }}" {{ (string)$piezaHig === (string)$p ? 'selected' : '' }}>{{ $p }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="higiene[{{ $clave }}][placa]" min="0" max="3" step="1"
                                                       class="form-control form-control-sm text-center input-placa"
                                                       placeholder="0" value="{{ $placaHig }}" oninput="validarInputHigiene(this, 3)">
                                            </td>
                                            <td>
                                                <input type="number" name="higiene[{{ $clave }}][calculo]" min="0" max="3" step="1"
                                                       class="form-control form-control-sm text-center input-calculo"
                                                       placeholder="0" value="{{ $calcHig }}" oninput="validarInputHigiene(this, 3)">
                                            </td>
                                            <td>
                                                <input type="number" name="higiene[{{ $clave }}][gingivitis]" min="0" max="1" step="1"
                                                       class="form-control form-control-sm text-center input-gingivitis"
                                                       placeholder="0" value="{{ $gingHig }}" oninput="validarInputHigiene(this, 1)">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="2">PROMEDIO TOTALES</td>
                                            <td><input type="number" step="0.01" name="placa_total" id="placa_total" class="form-control form-control-sm text-center fw-bold" value="{{ old('placa_total', $indicadores?->placa_total ?? 0) }}" readonly></td>
                                            <td><input type="number" step="0.01" name="calculo_total" id="calculo_total" class="form-control form-control-sm text-center fw-bold" value="{{ old('calculo_total', $indicadores?->calculo_total ?? 0) }}" readonly></td>
                                            <td><input type="number" step="0.01" name="gingivitis_total" id="gingivitis_total" class="form-control form-control-sm text-center fw-bold" value="{{ old('gingivitis_total', $indicadores?->gingivitis_total ?? 0) }}" readonly></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @php
                                $enfPerio = old('enfermedad_periodontal', $indicadores?->enfermedad_periodontal ?? 'sana');
                                $maloc    = old('maloclusion', $indicadores?->maloclusion ?? 'normal');
                                $fluor    = old('fluorosis', $indicadores?->fluorosis ?? 'normal');
                            @endphp
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="hc-form-label">Enfermedad Periodontal</label>
                                    <select name="enfermedad_periodontal" class="form-select form-select-sm hc-form-select">
                                        <option value="sana" {{ $enfPerio === 'sana' ? 'selected' : '' }}>Sin patología</option>
                                        <option value="leve" {{ $enfPerio === 'leve' ? 'selected' : '' }}>Leve</option>
                                        <option value="moderada" {{ $enfPerio === 'moderada' ? 'selected' : '' }}>Moderada</option>
                                        <option value="severa" {{ $enfPerio === 'severa' ? 'selected' : '' }}>Severa</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="hc-form-label">Maloclusión</label>
                                    <select name="maloclusion" class="form-select form-select-sm hc-form-select">
                                        <option value="normal" {{ $maloc === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="angle_I" {{ $maloc === 'angle_I' ? 'selected' : '' }}>Angle I</option>
                                        <option value="angle_II" {{ $maloc === 'angle_II' ? 'selected' : '' }}>Angle II</option>
                                        <option value="angle_III" {{ $maloc === 'angle_III' ? 'selected' : '' }}>Angle III</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="hc-form-label">Fluorosis</label>
                                    <select name="fluorosis" class="form-select form-select-sm hc-form-select">
                                        <option value="normal" {{ $fluor === 'normal' ? 'selected' : '' }}>Normal / Ausente</option>
                                        <option value="leve" {{ $fluor === 'leve' ? 'selected' : '' }}>Leve</option>
                                        <option value="moderada" {{ $fluor === 'moderada' ? 'selected' : '' }}>Moderada</option>
                                        <option value="severa" {{ $fluor === 'severa' ? 'selected' : '' }}>Severa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloque J: Índices CPO-ceo --}}
                <div class="col-lg-5">
                    <div class="hc-card h-100">
                        <div class="hc-card-header section-b justify-content-between">
                            <span><i class="fas fa-calculator me-2"></i> J. ÍNDICES CPO-ceo</span>
                            <span class="cpo-auto-badge"><i class="fas fa-magic me-1"></i> Cálculo Automático</span>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                Se calculan automáticamente según las piezas que marque en el Odontograma (o puede ajustarlos manualmente).
                            </p>

                            {{-- CPO Dientes Definitivos (11-48) --}}
                            <div class="p-3 bg-light rounded-3 mb-3">
                                <h6 class="fw-bold text-primary mb-2">Dientes Definitivos (CPO-D)</h6>
                                <div class="row g-2 text-center">
                                    <div class="col-3">
                                        <label class="small fw-bold text-danger">C (Cariados)</label>
                                        <input type="number" name="cpo_c" id="cpo_c" class="form-control text-center fw-bold" value="{{ old('cpo_c', $indicadores?->cpo_c ?? 0) }}" min="0" oninput="actualizarTotalesCpo()">
                                    </div>
                                    <div class="col-3">
                                        <label class="small fw-bold text-dark">P (Perdidos)</label>
                                        <input type="number" name="cpo_p" id="cpo_p" class="form-control text-center fw-bold" value="{{ old('cpo_p', $indicadores?->cpo_p ?? 0) }}" min="0" oninput="actualizarTotalesCpo()">
                                    </div>
                                    <div class="col-3">
                                        <label class="small fw-bold text-primary">O (Obturados)</label>
                                        <input type="number" name="cpo_o" id="cpo_o" class="form-control text-center fw-bold" value="{{ old('cpo_o', $indicadores?->cpo_o ?? 0) }}" min="0" oninput="actualizarTotalesCpo()">
                                    </div>
                                    <div class="col-3">
                                        <label class="small fw-bold text-success">TOTAL</label>
                                        <input type="number" id="cpo_total_view" class="form-control text-center fw-bold bg-white" value="{{ ($indicadores?->cpo_c ?? 0) + ($indicadores?->cpo_p ?? 0) + ($indicadores?->cpo_o ?? 0) }}" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- ceo Dientes Temporales (51-85) --}}
                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold text-secondary mb-2">Dientes Temporales (ceo-d)</h6>
                                <div class="row g-2 text-center">
                                    <div class="col-3">
                                        <label class="small fw-bold text-danger">c (cariados)</label>
                                        <input type="number" name="ceo_c" id="ceo_c" class="form-control text-center fw-bold" value="{{ old('ceo_c', $indicadores?->ceo_c ?? 0) }}" min="0" oninput="actualizarTotalesCpo()">
                                    </div>
                                    <div class="col-3">
                                        <label class="small fw-bold text-dark">e (extracción)</label>
                                        <input type="number" name="ceo_e" id="ceo_e" class="form-control text-center fw-bold" value="{{ old('ceo_e', $indicadores?->ceo_e ?? 0) }}" min="0" oninput="actualizarTotalesCpo()">
                                    </div>
                                    <div class="col-3">
                                        <label class="small fw-bold text-primary">o (obturados)</label>
                                        <input type="number" name="ceo_o" id="ceo_o" class="form-control text-center fw-bold" value="{{ old('ceo_o', $indicadores?->ceo_o ?? 0) }}" min="0" oninput="actualizarTotalesCpo()">
                                    </div>
                                    <div class="col-3">
                                        <label class="small fw-bold text-success">TOTAL</label>
                                        <input type="number" id="ceo_total_view" class="form-control text-center fw-bold bg-white" value="{{ ($indicadores?->ceo_c ?? 0) + ($indicadores?->ceo_e ?? 0) + ($indicadores?->ceo_o ?? 0) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente<i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- PASO 5: BLOQUES L, M, N (Plan de Diagnóstico, Exámenes y Diagnósticos CIE-10) --}}
        {{-- ================================================================ --}}
        <div class="hc-step-content" data-step="4">
            {{-- BLOQUES L y M: PLAN DE DIAGNÓSTICO Y EXÁMENES --}}
            <div class="hc-card">
                <div class="hc-card-header section-g justify-content-between">
                    <span><i class="fas fa-x-ray me-2"></i> L y M. PLAN DE DIAGNÓSTICO Y EXÁMENES COMPLEMENTARIOS</span>
                    <button type="button" class="btn btn-sm btn-light fw-bold" onclick="agregarFilaExamen()">
                        <i class="fas fa-plus me-1"></i> Añadir Examen
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Biometría, Química Sanguínea, Rayos X (Periapical/Panorámica) u otros exámenes solicitados o ya informados.
                    </p>
                    <div id="contenedor-examenes">
                        @if($examenesGuardados->count() > 0)
                            @foreach($examenesGuardados as $idxEx => $ex)
                            <div class="row g-2 align-items-end mb-2 fila-examen">
                                <div class="col-md-3">
                                    @if($idxEx === 0)<label class="hc-form-label">Tipo de Examen</label>@endif
                                    <select name="examenes[{{ $idxEx }}][tipo]" class="form-select hc-form-select">
                                        <option value="">-- Ninguno --</option>
                                        @foreach($tiposExamen as $key => $label)
                                        <option value="{{ $key }}" {{ $ex->tipo === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    @if($idxEx === 0)<label class="hc-form-label">Pedido / Indicación (Bloque L)</label>@endif
                                    <input type="text" name="examenes[{{ $idxEx }}][pedido_descripcion]" class="form-control hc-form-control"
                                           value="{{ $ex->pedido_descripcion }}" placeholder="Ej: Rx periapical pieza 16">
                                </div>
                                <div class="{{ $idxEx > 0 ? 'col-md-4' : 'col-md-5' }}">
                                    @if($idxEx === 0)<label class="hc-form-label">Informe / Resultado (Bloque M)</label>@endif
                                    <input type="text" name="examenes[{{ $idxEx }}][informe_resultado]" class="form-control hc-form-control"
                                           value="{{ $ex->informe_resultado }}" placeholder="Ej: Radiolucidez coronal compatible con caries profunda">
                                </div>
                                @if($idxEx > 0)
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.fila-examen').remove()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="row g-2 align-items-end mb-2 fila-examen">
                                <div class="col-md-3">
                                    <label class="hc-form-label">Tipo de Examen</label>
                                    <select name="examenes[0][tipo]" class="form-select hc-form-select">
                                        <option value="">-- Ninguno --</option>
                                        @foreach($tiposExamen as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="hc-form-label">Pedido / Indicación (Bloque L)</label>
                                    <input type="text" name="examenes[0][pedido_descripcion]" class="form-control hc-form-control"
                                           placeholder="Ej: Rx periapical pieza 16">
                                </div>
                                <div class="col-md-5">
                                    <label class="hc-form-label">Informe / Resultado (Bloque M)</label>
                                    <input type="text" name="examenes[0][informe_resultado]" class="form-control hc-form-control"
                                           placeholder="Ej: Radiolucidez coronal compatible con caries profunda">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- BLOQUE N: DIAGNÓSTICOS CIE-10 --}}
            <div class="hc-card">
                <div class="hc-card-header section-diag justify-content-between">
                    <span><i class="fas fa-diagnoses me-2"></i> N. DIAGNÓSTICOS ODONTOLÓGICOS (CIE-10)</span>
                    <button type="button" class="btn btn-sm btn-light fw-bold" onclick="agregarFilaDiagnostico()">
                        <i class="fas fa-plus me-1"></i> Añadir Diagnóstico (Máx. 6)
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Seleccione el código oficial <strong>CIE-10</strong> (capítulo K00–K14), indique si es <strong>PRE</strong> (Presuntivo) o <strong>DEF</strong> (Definitivo) y la pieza dental si aplica.
                    </p>
                    <div id="contenedor-diagnosticos">
                        @if($diagnosticosGuardados->count() > 0)
                            @foreach($diagnosticosGuardados as $idxDg => $dg)
                            <div class="row g-2 align-items-end mb-3 fila-diagnostico">
                                <div class="col-md-5">
                                    @if($idxDg === 0)<label class="hc-form-label">Código CIE-10 Odontológico</label>@endif
                                    <select name="diagnosticos[{{ $idxDg }}][cie10_codigo]" class="form-select hc-form-select" onchange="autocompletarDescripcionCie(this, {{ $idxDg }})">
                                        <option value="">-- Seleccione código CIE-10 --</option>
                                        @foreach($cie10Catalogo as $cie)
                                        <option value="{{ $cie->codigo }}" data-nombre="{{ $cie->nombre }}" {{ $dg->cie10_codigo === $cie->codigo ? 'selected' : '' }}>
                                            {{ $cie->codigo }} - {{ $cie->nombre }} ({{ $cie->categoria }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="{{ $idxDg > 0 ? 'col-md-3' : 'col-md-4' }}">
                                    @if($idxDg === 0)<label class="hc-form-label">Descripción del Diagnóstico</label>@endif
                                    <input type="text" name="diagnosticos[{{ $idxDg }}][descripcion]" id="diag_desc_{{ $idxDg }}" class="form-control hc-form-control"
                                           value="{{ $dg->descripcion }}" placeholder="Descripción clínica o nota diagnóstica...">
                                </div>
                                <div class="col-md-2">
                                    @if($idxDg === 0)<label class="hc-form-label">Tipo</label>@endif
                                    <select name="diagnosticos[{{ $idxDg }}][tipo]" class="form-select hc-form-select">
                                        <option value="PRE" {{ $dg->tipo === 'PRE' ? 'selected' : '' }}>PRE (Presuntivo)</option>
                                        <option value="DEF" {{ $dg->tipo === 'DEF' ? 'selected' : '' }}>DEF (Definitivo)</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    @if($idxDg === 0)<label class="hc-form-label">Pieza</label>@endif
                                    <input type="number" name="diagnosticos[{{ $idxDg }}][pieza_dental]" class="form-control hc-form-control text-center"
                                           min="11" max="85" oninput="validarInputPiezaDental(this)" onblur="if(this.value.length===1)this.value=''"
                                           value="{{ $dg->pieza_dental }}" placeholder="16">
                                </div>
                                @if($idxDg > 0)
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.fila-diagnostico').remove()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="row g-2 align-items-end mb-3 fila-diagnostico">
                                <div class="col-md-5">
                                    <label class="hc-form-label">Código CIE-10 Odontológico</label>
                                    <select name="diagnosticos[0][cie10_codigo]" class="form-select hc-form-select" onchange="autocompletarDescripcionCie(this, 0)">
                                        <option value="">-- Seleccione código CIE-10 --</option>
                                        @foreach($cie10Catalogo as $cie)
                                        <option value="{{ $cie->codigo }}" data-nombre="{{ $cie->nombre }}">
                                            {{ $cie->codigo }} - {{ $cie->nombre }} ({{ $cie->categoria }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="hc-form-label">Descripción del Diagnóstico</label>
                                    <input type="text" name="diagnosticos[0][descripcion]" id="diag_desc_0" class="form-control hc-form-control"
                                           placeholder="Descripción clínica o nota diagnóstica...">
                                </div>
                                <div class="col-md-2">
                                    <label class="hc-form-label">Tipo</label>
                                    <select name="diagnosticos[0][tipo]" class="form-select hc-form-select">
                                        <option value="PRE">PRE (Presuntivo)</option>
                                        <option value="DEF">DEF (Definitivo)</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="hc-form-label">Pieza</label>
                                    <input type="number" name="diagnosticos[0][pieza_dental]" class="form-control hc-form-control text-center"
                                           min="11" max="85" oninput="validarInputPiezaDental(this)" onblur="if(this.value.length===1)this.value=''"
                                           placeholder="16">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente<i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- PASO 6: BLOQUES O y P (Datos del Profesional, Firma y Primera Sesión de Tratamiento) --}}
        {{-- ================================================================ --}}
        <div class="hc-step-content" data-step="5">
            {{-- BLOQUE P: SESIÓN DE TRATAMIENTO INICIAL --}}
            <div class="hc-card">
                <div class="hc-card-header section-treat">
                    <i class="fas fa-Notes-medical me-2"></i> P. TRATAMIENTO REALIZADO EN ESTA SESIÓN
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="hc-form-label">Diagnósticos y Complicaciones</label>
                            <textarea name="sesion_diagnosticos_complicaciones" class="form-control hc-form-control" rows="3"
                                      placeholder="Ej: Caries dentinaria pieza 16. Sin complicaciones.">{{ old('sesion_diagnosticos_complicaciones', $primeraSesion?->diagnosticos_complicaciones) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="hc-form-label">Procedimientos Realizados</label>
                            <textarea name="sesion_procedimientos" class="form-control hc-form-control" rows="3"
                                      placeholder="Ej: Profilaxis, anestesia infiltrativa, remoción de tejido cariado y restauración con resina fotocurable en 16 (O)...">{{ old('sesion_procedimientos', $primeraSesion?->procedimientos) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="hc-form-label">Prescripciones (Fármacos, Dosis, Indicaciones)</label>
                            <textarea name="sesion_prescripciones" class="form-control hc-form-control" rows="3"
                                      placeholder="Ej: Ibuprofeno 400mg VO cada 8 horas por 3 días si hay dolor.">{{ old('sesion_prescripciones', $primeraSesion?->prescripciones) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOQUE O: DATOS DEL PROFESIONAL Y OBSERVACIONES --}}
            <div class="hc-card">
                <div class="hc-card-header section-obs">
                    <i class="fas fa-user-md me-2"></i> O. DATOS DEL PROFESIONAL RESPONSABLE Y FIRMA
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-5">
                            <label class="hc-form-label">Nombres y Apellidos del Profesional</label>
                            <input type="text" class="form-control hc-form-control bg-light" readonly
                                   value="Dr(a). {{ Auth::user()->nombre ?? '' }} {{ Auth::user()->apellido ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="hc-form-label">Código Profesional / Cédula MSP</label>
                            <input type="text" name="codigo_profesional" class="form-control hc-form-control"
                                   value="{{ old('codigo_profesional', $historia->codigo_profesional ?? Auth::user()->cedula ?? 'MSP-OD-001') }}">
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <small class="text-muted d-block">Sello y Firma Digital</small>
                                <span class="badge bg-success mt-1"><i class="fas fa-check-circle me-1"></i> SHA-256 Automático</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="hc-form-label">Observaciones Generales Adicionales</label>
                        <textarea name="observaciones" class="form-control hc-form-control" rows="2"
                                  placeholder="Notas clínicas adicionales del expediente...">{{ old('observaciones', $historia->observaciones ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <div class="d-flex gap-2">
                    <button type="submit" formnovalidate class="hc-btn-action hc-btn-warning" onclick="document.getElementById('accion_guardar').value='borrador'">
                        <i class="fas fa-save me-1"></i> {{ $esEdicion ? 'Actualizar Borrador' : 'Guardar como Borrador' }}
                    </button>
                    <button type="submit" class="hc-btn-action hc-btn-success btn-lg" onclick="document.getElementById('accion_guardar').value='abierta'">
                        <i class="fas fa-check-double me-1"></i> Firmar y Activar Historia Clínica HCU-033
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

{{-- MODAL INTERACTIVO PARA MARCAR DIENTE EN EL PASO 4 --}}
<div class="modal fade" id="modalTratamiento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-tooth me-2"></i> Pieza Dental <span id="lbl-diente-seleccionado"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">1. TIPO DE REGISTRO (COLOR NORMATIVA MSP)</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="wiz_estado" id="wiz_estado_malo" value="malo" checked>
                        <label class="btn btn-outline-danger" for="wiz_estado_malo">
                            <i class="fas fa-circle text-danger me-1"></i> Patología Actual (Rojo)
                        </label>
                        <input type="radio" class="btn-check" name="wiz_estado" id="wiz_estado_bueno" value="bueno">
                        <label class="btn btn-outline-primary" for="wiz_estado_bueno">
                            <i class="fas fa-circle text-primary me-1"></i> Realizado / Bueno (Azul)
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">2. HALLAZGO / SIMBOLOGÍA (BLOQUE K)</label>
                    <select class="form-select hc-form-select fw-semibold" id="wiz-select-hallazgo" onchange="autoSeleccionarColorMsp(this.value)">
                        <option value="Caries" data-cat="Restauradora">● Caries dental (Rojo por cara)</option>
                        <option value="Obturado" data-cat="Restauradora">● Obturado / Resina / Amalgama (Azul por cara)</option>
                        <option value="Sellante necesario" data-cat="Prevención">* Sellante necesario (Asterisco Rojo)</option>
                        <option value="Sellante realizado" data-cat="Prevención">* Sellante realizado (Asterisco Azul)</option>
                        <option value="Extracción indicada" data-cat="Cirugía">X Extracción indicada (Cruz Roja)</option>
                        <option value="Pérdida por caries" data-cat="Cirugía">⊗ Pérdida por caries (Aspa/Círculo Azul)</option>
                        <option value="Pérdida por otra causa" data-cat="Cirugía">⊗ Pérdida por otra causa (Doble línea)</option>
                        <option value="Endodoncia por realizar" data-cat="Endodoncia">△ Endodoncia por realizar (Triángulo Rojo)</option>
                        <option value="Endodoncia realizada" data-cat="Endodoncia">▲ Endodoncia realizada (Triángulo Azul)</option>
                        <option value="Corona" data-cat="Prótesis">⊡ Corona (Recuadro Contorno)</option>
                        <option value="Prótesis fija" data-cat="Prótesis">--- Prótesis fija (Barra de póntico)</option>
                        <option value="Prótesis removible" data-cat="Prótesis">(- - -) Prótesis removible (Línea discontinua)</option>
                        <option value="Sano" data-cat="Prevención">Diente Sano / Limpiar</option>
                    </select>
                </div>

                <div class="mb-3" id="bloque-caras-diente">
                    <label class="form-label small fw-bold text-muted">3. SUPERFICIES / CARAS AFECTADAS</label>
                    <div class="d-flex justify-content-between gap-2">
                        @foreach(['vestibular' => 'V', 'distal' => 'D', 'mesial' => 'M', 'palatina' => 'P/L', 'oclusal' => 'O'] as $val => $lbl)
                        <div class="flex-fill">
                            <input type="checkbox" class="btn-check wiz-cara-chk" id="wiz-chk-{{ $val }}" value="{{ $val }}">
                            <label class="btn btn-cara w-100 btn-sm" for="wiz-chk-{{ $val }}">{{ $lbl }}</label>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">* Para Caries y Obturaciones seleccione una o varias caras.</small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Movilidad (1 a 3)</label>
                        <select class="form-select form-select-sm hc-form-select" id="wiz-movilidad">
                            <option value="">Sin movilidad</option>
                            <option value="1">Grado 1</option>
                            <option value="2">Grado 2</option>
                            <option value="3">Grado 3</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Recesión (1 a 4)</label>
                        <select class="form-select form-select-sm hc-form-select" id="wiz-recesion">
                            <option value="">Sin recesión</option>
                            <option value="1">1 mm</option>
                            <option value="2">2 mm</option>
                            <option value="3">3 mm</option>
                            <option value="4">4 mm o más</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-bold text-muted">Nota de la Pieza (Opcional)</label>
                    <input type="text" class="form-control form-control-sm hc-form-control" id="wiz-observacion" placeholder="Detalle clínico de la pieza...">
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between p-3">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-eliminar-hallazgo" onclick="quitarHallazgoDienteWizard()">
                    <i class="fas fa-trash me-1"></i> Quitar Hallazgo
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold" onclick="aplicarHallazgoWizard()">
                        <i class="fas fa-check me-1"></i> Aplicar al Diente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let pasoActual = 0;
    const totalPasos = 6;
    let dienteActivoWizard = null;
    let registrosOdontogramaWizard = []; // Array de objetos {pieza, categoria, tratamiento, estado, caras, movilidad, recesion, observacion, color}

    // --- Vigencia dinámica según condición de Embarazo (Instructivo MSP) ---
    function actualizarBadgeVigencia() {
        const chk = document.getElementById('embarazada');
        const badge = document.getElementById('badge-vigencia');
        if (!badge) return;

        if (chk && chk.checked) {
            badge.className = 'badge bg-danger text-white shadow-sm';
            badge.innerHTML = '<i class="fas fa-baby me-1"></i> Vigencia: Gestación (9 meses)';
        } else {
            badge.className = 'badge bg-light text-dark';
            badge.innerHTML = '<i class="fas fa-calendar-alt me-1 text-primary"></i> Vigencia: 365 días';
        }
    }
    document.addEventListener('DOMContentLoaded', actualizarBadgeVigencia);

    function irAPaso(paso) {
        if (paso < 0 || paso >= totalPasos) return;

        // Validar motivo de consulta antes de salir del paso 0
        if (pasoActual === 0 && paso > 0) {
            const motivo = document.getElementById('motivo_consulta');
            if (motivo && !motivo.value.trim()) {
                motivo.focus();
                motivo.classList.add('is-invalid');
                return;
            } else if (motivo) {
                motivo.classList.remove('is-invalid');
            }
        }

        document.querySelectorAll('.hc-step-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.hc-step').forEach(el => el.classList.remove('active'));

        document.querySelectorAll('.hc-step').forEach((el, i) => {
            el.classList.remove('completed');
            if (i < paso) el.classList.add('completed');
        });

        document.querySelector(`.hc-step-content[data-step="${paso}"]`).classList.add('active');
        document.querySelector(`.hc-step[data-step="${paso}"]`).classList.add('active');

        pasoActual = paso;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function siguientePaso() { irAPaso(pasoActual + 1); }
    function anteriorPaso() { irAPaso(pasoActual - 1); }

    // --- Antecedentes D y E ---
    function toggleNoRefiere(tipo, noRefiere) {
        const cont = document.getElementById(`contenedor-ant-${tipo}`);
        if (!cont) return;
        if (noRefiere) {
            cont.style.opacity = '0.55';
            cont.style.pointerEvents = 'none';
            cont.querySelectorAll(`.check-ant-${tipo}`).forEach(chk => {
                chk.checked = false;
                toggleAntCard(tipo, chk.value, false);
            });
        } else {
            cont.style.opacity = '1';
            cont.style.pointerEvents = 'auto';
        }
    }

    function toggleAntCard(tipo, codigo, checked) {
        const card = document.getElementById(`card-${tipo}-${codigo}`);
        const box = document.getElementById(`desc-box-${tipo}-${codigo}`);
        if (checked) {
            card?.classList.add('selected');
            box?.classList.remove('d-none');
        } else {
            card?.classList.remove('selected');
            box?.classList.add('d-none');
        }
    }

    // --- Examen Estomatognático G ---
    function toggleEstomaSinPatologia(sinPatologia) {
        const cont = document.getElementById('contenedor-estoma');
        if (!cont) return;
        if (sinPatologia) {
            cont.style.opacity = '0.55';
            cont.style.pointerEvents = 'none';
            cont.querySelectorAll('.check-estoma').forEach(chk => {
                chk.checked = false;
                toggleEstomaCard(chk.value, false);
            });
        } else {
            cont.style.opacity = '1';
            cont.style.pointerEvents = 'auto';
        }
    }

    function toggleEstomaCard(codigo, checked) {
        const card = document.getElementById(`card-estoma-${codigo}`);
        const box = document.getElementById(`desc-box-estoma-${codigo}`);
        if (checked) {
            card?.classList.add('selected');
            box?.classList.remove('d-none');
        } else {
            card?.classList.remove('selected');
            box?.classList.add('d-none');
        }
    }

    // --- Odontograma en el Wizard (Paso 4) ---
    function autoSeleccionarColorMsp(hallazgo) {
        const radioMalo = document.getElementById('wiz_estado_malo');
        const radioBueno = document.getElementById('wiz_estado_bueno');
        const bloqueCaras = document.getElementById('bloque-caras-diente');

        // Solo Caries y Obturado requieren marcar caras
        const requiereCaras = (hallazgo === 'Caries' || hallazgo === 'Obturado');
        if (bloqueCaras) {
            bloqueCaras.style.display = requiereCaras ? 'block' : 'none';
        }

        // Selección automática de color según normativa técnica MSP HCU-033:
        // ROJO = Patología actual / Indicado por realizar
        // AZUL / OSCURO = Tratamiento ya realizado / Ausente
        if (hallazgo === 'Caries' || 
            hallazgo === 'Sellante necesario' || 
            hallazgo === 'Extracción indicada' || 
            hallazgo === 'Endodoncia por realizar') {
            if (radioMalo) radioMalo.checked = true;
        } else if (hallazgo === 'Obturado' || 
                   hallazgo === 'Sellante realizado' || 
                   hallazgo === 'Pérdida por caries' || 
                   hallazgo === 'Pérdida por otra causa' || 
                   hallazgo === 'Endodoncia realizada' || 
                   hallazgo === 'Prótesis fija' || 
                   hallazgo === 'Prótesis removible') {
            if (radioBueno) radioBueno.checked = true;
        }
    }

    window.abrirModalDiente = function(pieza) {
        dienteActivoWizard = pieza;
        document.getElementById('lbl-diente-seleccionado').innerText = pieza;

        // Buscar si ya existe hallazgo registrado para esta pieza
        const reg = registrosOdontogramaWizard.find(r => String(r.pieza) === String(pieza));
        const btnEliminar = document.getElementById('btn-eliminar-hallazgo');

        // Deshabilitar movilidad/recesión en temporales (51-85) según instructivo MSP
        const esTemporal = (pieza >= 51 && pieza <= 85);
        const movEl = document.getElementById('wiz-movilidad');
        const recEl = document.getElementById('wiz-recesion');
        movEl.disabled = esTemporal;
        recEl.disabled = esTemporal;

        if (reg) {
            document.getElementById('wiz-select-hallazgo').value = reg.tratamiento || 'Caries';
            if (reg.estado === 'bueno') {
                document.getElementById('wiz_estado_bueno').checked = true;
            } else {
                document.getElementById('wiz_estado_malo').checked = true;
            }
            movEl.value = reg.movilidad || '';
            recEl.value = reg.recesion || '';
            document.getElementById('wiz-observacion').value = reg.observacion || '';

            // Restaurar caras marcadas si aplica
            document.querySelectorAll('.wiz-cara-chk').forEach(c => {
                c.checked = Array.isArray(reg.caras) && reg.caras.includes(c.value);
            });

            if (btnEliminar) btnEliminar.style.display = 'inline-block';
        } else {
            document.getElementById('wiz-select-hallazgo').value = 'Caries';
            document.getElementById('wiz_estado_malo').checked = true;
            movEl.value = '';
            recEl.value = '';
            document.getElementById('wiz-observacion').value = '';
            document.querySelectorAll('.wiz-cara-chk').forEach(c => c.checked = false);

            if (btnEliminar) btnEliminar.style.display = 'none';
        }

        autoSeleccionarColorMsp(document.getElementById('wiz-select-hallazgo').value);

        new bootstrap.Modal(document.getElementById('modalTratamiento')).show();
    };

    function quitarHallazgoDienteWizard() {
        if (!dienteActivoWizard) return;
        registrosOdontogramaWizard = registrosOdontogramaWizard.filter(r => String(r.pieza) !== String(dienteActivoWizard));
        renderizarDienteSvg(dienteActivoWizard, null);
        document.getElementById('odontograma_json').value = JSON.stringify(registrosOdontogramaWizard);
        renderizarListaOdontogramaWizard();
        recalcularCpoAutomatico();
        bootstrap.Modal.getInstance(document.getElementById('modalTratamiento')).hide();
    }

    function aplicarHallazgoWizard() {
        const selectEl = document.getElementById('wiz-select-hallazgo');
        const tratamiento = selectEl.value;
        const categoria = selectEl.options[selectEl.selectedIndex].dataset.cat || 'General';
        const estado = document.querySelector('input[name="wiz_estado"]:checked').value;
        const movilidad = document.getElementById('wiz-movilidad').value;
        const recesion = document.getElementById('wiz-recesion').value;
        const observacion = document.getElementById('wiz-observacion').value;

        let caras = [];
        document.querySelectorAll('.wiz-cara-chk:checked').forEach(c => caras.push(c.value));

        if (tratamiento === 'Sano') {
            registrosOdontogramaWizard = registrosOdontogramaWizard.filter(r => String(r.pieza) !== String(dienteActivoWizard));
            renderizarDienteSvg(dienteActivoWizard, null);
        } else {
            // Para Caries y Obturado, asignar 'oclusal' por defecto si no seleccionó caras
            if ((tratamiento === 'Caries' || tratamiento === 'Obturado') && caras.length === 0) {
                caras = ['oclusal'];
            }

            let color = (estado === 'malo') ? '#dc3545' : '#0d6efd';
            if (tratamiento === 'Pérdida por otra causa') {
                color = '#1e293b';
            }

            const nuevoReg = {
                pieza: parseInt(dienteActivoWizard),
                categoria,
                tratamiento,
                estado,
                caras,
                movilidad,
                recesion,
                observacion,
                color
            };

            // Reemplazar registro previo si existía para esta misma pieza
            registrosOdontogramaWizard = registrosOdontogramaWizard.filter(r => String(r.pieza) !== String(dienteActivoWizard));
            registrosOdontogramaWizard.push(nuevoReg);

            renderizarDienteSvg(dienteActivoWizard, nuevoReg);
        }

        document.getElementById('odontograma_json').value = JSON.stringify(registrosOdontogramaWizard);
        renderizarListaOdontogramaWizard();
        recalcularCpoAutomatico();

        bootstrap.Modal.getInstance(document.getElementById('modalTratamiento')).hide();
    }

    // Renderiza el SVG con figuras vectoriales precisas según la normativa técnica MSP
    function renderizarDienteSvg(pieza, reg) {
        const svg = document.querySelector(`svg[data-pieza="${pieza}"]`);
        if (!svg) return;

        const container = svg.closest('.diente-container');
        const simboloLayer = svg.querySelector('.simbolo-layer');
        const indMovRec = container ? container.querySelector('.indicador-movilidad-recesion') : null;

        // 1. Resetear todas las caras al blanco base
        const carasElements = svg.querySelectorAll('.vestibular, .lingual, .distal, .mesial, .oclusal, .palatina');
        carasElements.forEach(el => {
            el.style.fill = '#ffffff';
            el.style.stroke = '#222222';
        });

        // 2. Limpiar símbolos previos y texto de movilidad
        if (simboloLayer) simboloLayer.innerHTML = '';
        if (indMovRec) {
            indMovRec.textContent = '';
            indMovRec.title = '';
        }

        // Actualizar <title> SVG nativo
        let titleEl = svg.querySelector('title');
        if (!titleEl) {
            titleEl = document.createElementNS('http://www.w3.org/2000/svg', 'title');
            svg.prepend(titleEl);
        }

        if (!reg || reg.tratamiento === 'Sano') {
            titleEl.textContent = `Pieza ${pieza} - Sano`;
            return;
        }

        const carasStr = (reg.caras && reg.caras.length > 0) ? ` (${reg.caras.join(', ').toUpperCase()})` : '';
        titleEl.textContent = `Pieza ${pieza} - ${reg.tratamiento}${carasStr}`;

        const color = reg.color || (reg.estado === 'malo' ? '#dc3545' : '#0d6efd');

        // 3. Pintar según tipo de hallazgo
        if (reg.tratamiento === 'Caries' || reg.tratamiento === 'Obturado') {
            // Rellenar las caras específicas con Rojo (Caries) o Azul (Obturado)
            const caras = reg.caras || ['oclusal'];
            caras.forEach(cara => {
                let sel = `.${cara}`;
                if (cara === 'palatina' || cara === 'lingual') sel = '.palatina, .lingual';
                svg.querySelectorAll(sel).forEach(el => {
                    el.style.fill = color;
                });
            });
        } else if (simboloLayer) {
            let svgHtml = '';
            switch (reg.tratamiento) {
                case 'Extracción indicada':
                    // Cruz roja diagonal sobre toda la pieza (X)
                    svgHtml = `
                        <line x1="5" y1="5" x2="45" y2="45" stroke="#dc3545" stroke-width="5" stroke-linecap="round"/>
                        <line x1="45" y1="5" x2="5" y2="45" stroke="#dc3545" stroke-width="5" stroke-linecap="round"/>
                    `;
                    break;

                case 'Pérdida por caries':
                    // Símbolo MSP ⊗ (Círculo azul con cruz interna)
                    svgHtml = `
                        <circle cx="25" cy="25" r="21" fill="none" stroke="#0d6efd" stroke-width="3.5"/>
                        <line x1="10" y1="10" x2="40" y2="40" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="40" y1="10" x2="10" y2="40" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                    `;
                    break;

                case 'Pérdida por otra causa':
                    // Círculo oscuro con dos líneas horizontales paralelas
                    svgHtml = `
                        <circle cx="25" cy="25" r="21" fill="none" stroke="#1e293b" stroke-width="3.5"/>
                        <line x1="6" y1="20" x2="44" y2="20" stroke="#1e293b" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="6" y1="30" x2="44" y2="30" stroke="#1e293b" stroke-width="3.5" stroke-linecap="round"/>
                    `;
                    break;

                case 'Sellante necesario':
                    // Asterisco rojo de 8 puntas centrado en oclusal
                    svgHtml = `
                        <line x1="25" y1="13" x2="25" y2="37" stroke="#dc3545" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="13" y1="25" x2="37" y2="25" stroke="#dc3545" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="16.5" y1="16.5" x2="33.5" y2="33.5" stroke="#dc3545" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="33.5" y1="16.5" x2="16.5" y2="33.5" stroke="#dc3545" stroke-width="3.5" stroke-linecap="round"/>
                    `;
                    break;

                case 'Sellante realizado':
                    // Asterisco azul de 8 puntas centrado en oclusal
                    svgHtml = `
                        <line x1="25" y1="13" x2="25" y2="37" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="13" y1="25" x2="37" y2="25" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="16.5" y1="16.5" x2="33.5" y2="33.5" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="33.5" y1="16.5" x2="16.5" y2="33.5" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                    `;
                    break;

                case 'Endodoncia por realizar':
                    // Triángulo hueco rojo con vértice arriba (△)
                    svgHtml = `
                        <polygon points="25,6 44,43 6,43" fill="none" stroke="#dc3545" stroke-width="4" stroke-linejoin="round"/>
                    `;
                    break;

                case 'Endodoncia realizada':
                    // Triángulo relleno azul (▲)
                    svgHtml = `
                        <polygon points="25,6 44,43 6,43" fill="#0d6efd" stroke="#0d6efd" stroke-width="2" stroke-linejoin="round"/>
                    `;
                    break;

                case 'Corona':
                    // Recuadro que enmarca el contorno dental (⊡)
                    svgHtml = `
                        <rect x="3" y="3" width="44" height="44" rx="4" fill="none" stroke="${color}" stroke-width="4"/>
                    `;
                    break;

                case 'Prótesis fija':
                    // Barra de póntico continua horizontal
                    svgHtml = `
                        <line x1="0" y1="25" x2="50" y2="25" stroke="${color}" stroke-width="6"/>
                        <circle cx="25" cy="25" r="9" fill="${color}"/>
                    `;
                    break;

                case 'Prótesis removible':
                    // Línea discontinua con delimitadores
                    svgHtml = `
                        <line x1="4" y1="25" x2="46" y2="25" stroke="${color}" stroke-width="4.5" stroke-dasharray="6,4" stroke-linecap="round"/>
                        <path d="M 6,14 C 2,19 2,31 6,36" fill="none" stroke="${color}" stroke-width="3" stroke-linecap="round"/>
                        <path d="M 44,14 C 48,19 48,31 44,36" fill="none" stroke="${color}" stroke-width="3" stroke-linecap="round"/>
                    `;
                    break;
            }
            simboloLayer.innerHTML = svgHtml;
        }

        // 4. Mostrar movilidad y recesión en el span inferior si tiene (formato limpio y centrado M1 R1)
        if (indMovRec && (reg.movilidad || reg.recesion)) {
            let partes = [];
            if (reg.movilidad) partes.push(`M${reg.movilidad}`);
            if (reg.recesion) partes.push(`R${reg.recesion}`);
            indMovRec.textContent = partes.join(' ');
            indMovRec.title = `Movilidad: Grado ${reg.movilidad || 0}, Recesión: ${reg.recesion || 0}mm`;
        }
    }

    function renderizarListaOdontogramaWizard() {
        const cont = document.getElementById('wizard-lista-odontograma');
        if (!cont) return;
        if (registrosOdontogramaWizard.length === 0) {
            cont.innerHTML = `<div class="text-center p-4 text-muted small"><i class="fas fa-tooth me-1"></i> Haga clic sobre cualquier diente para registrar caries, obturaciones, extracciones, sellantes u otros hallazgos.</div>`;
            return;
        }
        cont.innerHTML = registrosOdontogramaWizard.map(r => {
            let badgeColor = 'bg-danger';
            if (r.color === '#1e293b') badgeColor = 'bg-dark';
            else if (r.estado === 'bueno') badgeColor = 'bg-primary';

            return `
                <div class="list-group-item list-group-item-action py-2 px-3" style="cursor: pointer;" onclick="abrirModalDiente(${r.pieza})">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-primary small"><i class="fas fa-tooth me-1"></i> Pieza ${r.pieza}</strong>
                        <span class="badge ${badgeColor}">${r.tratamiento}</span>
                    </div>
                    ${(r.tratamiento === 'Caries' || r.tratamiento === 'Obturado') ? 
                        `<small class="text-muted d-block" style="font-size:11px;">Caras: ${(r.caras || []).join(', ').toUpperCase()}</small>` : ''}
                    ${r.movilidad ? `<small class="text-warning d-block" style="font-size:11px;">Movilidad: Grado ${r.movilidad}</small>` : ''}
                    ${r.recesion ? `<small class="text-info d-block" style="font-size:11px;">Recesión: ${r.recesion}mm</small>` : ''}
                    ${r.observacion ? `<small class="text-secondary d-block fst-italic" style="font-size:11px;">"${r.observacion}"</small>` : ''}
                </div>
            `;
        }).join('');
    }

    function limpiarOdontogramaWizard() {
        if (!confirm('¿Está seguro de que desea limpiar todos los hallazgos del odontograma?')) return;
        registrosOdontogramaWizard = [];
        document.getElementById('odontograma_json').value = '[]';
        document.querySelectorAll('.diente-svg').forEach(svg => {
            const pieza = svg.getAttribute('data-pieza');
            renderizarDienteSvg(pieza, null);
        });
        renderizarListaOdontogramaWizard();
        recalcularCpoAutomatico();
    }

    // Cálculo automático de CPO-D (11-48) y ceo-d (51-85) según el bloque J del instructivo MSP
    function recalcularCpoAutomatico() {
        let cpoC = 0, cpoP = 0, cpoO = 0;
        let ceoC = 0, ceoE = 0, ceoO = 0;

        registrosOdontogramaWizard.forEach(r => {
            const esTemp = (r.pieza >= 51 && r.pieza <= 85);
            const t = (r.tratamiento || '').toLowerCase();

            if (!esTemp) {
                // Permanentes (CPO-D)
                if (t.includes('pérdida') || t.includes('extracci')) {
                    cpoP++;
                } else if (t.includes('caries') || r.estado === 'malo') {
                    cpoC++;
                } else if (t.includes('obturado') || t.includes('corona') || r.estado === 'bueno') {
                    cpoO++;
                }
            } else {
                // Temporales (ceo-d)
                if (t.includes('pérdida') || t.includes('extracci')) {
                    ceoE++;
                } else if (t.includes('caries') || r.estado === 'malo') {
                    ceoC++;
                } else if (t.includes('obturado') || t.includes('corona') || r.estado === 'bueno') {
                    ceoO++;
                }
            }
        });

        document.getElementById('cpo_c').value = cpoC;
        document.getElementById('cpo_p').value = cpoP;
        document.getElementById('cpo_o').value = cpoO;
        document.getElementById('ceo_c').value = ceoC;
        document.getElementById('ceo_e').value = ceoE;
        document.getElementById('ceo_o').value = ceoO;
        actualizarTotalesCpo();
    }

    function actualizarTotalesCpo() {
        ['cpo_c', 'cpo_p', 'cpo_o', 'ceo_c', 'ceo_e', 'ceo_o'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            let limpio = el.value.replace(/[^0-9]/g, '');
            if (limpio !== '') {
                let maxPiezas = id.startsWith('cpo_') ? 32 : 20;
                let num = parseInt(limpio, 10);
                if (num > maxPiezas) num = maxPiezas;
                el.value = String(num); // Convierte "01" -> "1"
            }
        });

        const cpoTotal = (parseInt(document.getElementById('cpo_c').value) || 0)
                       + (parseInt(document.getElementById('cpo_p').value) || 0)
                       + (parseInt(document.getElementById('cpo_o').value) || 0);
        const ceoTotal = (parseInt(document.getElementById('ceo_c').value) || 0)
                       + (parseInt(document.getElementById('ceo_e').value) || 0)
                       + (parseInt(document.getElementById('ceo_o').value) || 0);

        document.getElementById('cpo_total_view').value = cpoTotal;
        document.getElementById('ceo_total_view').value = ceoTotal;
    }

    function validarInputHigiene(input, max) {
        // Dejar solo dígitos numéricos
        let limpio = input.value.replace(/[^0-9]/g, '');

        if (limpio === '') {
            input.value = '';
            recalcularPromediosHigiene();
            return;
        }

        // Quitar ceros a la izquierda (ej. "01" -> "1", "02" -> "2")
        let sinCerosIzquierda = String(parseInt(limpio, 10));

        // Si el usuario teclea más de un dígito (ej. "1111" o "12"), tomar el último dígito ingresado
        let digito = parseInt(sinCerosIzquierda.slice(-1), 10);

        // Restringir estrictamente al máximo permitido (3 para Placa/Cálculo, 1 para Gingivitis)
        if (digito > max) {
            digito = max;
        }

        input.value = String(digito);
        recalcularPromediosHigiene();
    }

    function recalcularPromediosHigiene() {
        const promediar = (selector) => {
            let suma = 0, count = 0;
            document.querySelectorAll(selector).forEach(inp => {
                if (inp.value !== '') {
                    suma += parseFloat(inp.value) || 0;
                    count++;
                }
            });
            return count > 0 ? (suma / count).toFixed(2) : '0';
        };
        document.getElementById('placa_total').value = promediar('.input-placa');
        document.getElementById('calculo_total').value = promediar('.input-calculo');
        document.getElementById('gingivitis_total').value = promediar('.input-gingivitis');
    }

    // --- Exámenes (L y M) y Diagnósticos CIE-10 (N) dinámicos ---
    let idxExamen = {{ max(1, $examenesGuardados->count()) }};
    function agregarFilaExamen() {
        const cont = document.getElementById('contenedor-examenes');
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 fila-examen';
        row.innerHTML = `
            <div class="col-md-3">
                <select name="examenes[${idxExamen}][tipo]" class="form-select hc-form-select">
                    <option value="rayos_x">Rayos X (Periapical / Panorámica)</option>
                    <option value="biometria">Biometría hemática</option>
                    <option value="quimica_sanguinea">Química sanguínea</option>
                    <option value="otros">Otros exámenes</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="examenes[${idxExamen}][pedido_descripcion]" class="form-control hc-form-control" placeholder="Pedido / Indicación...">
            </div>
            <div class="col-md-4">
                <input type="text" name="examenes[${idxExamen}][informe_resultado]" class="form-control hc-form-control" placeholder="Informe / Resultado...">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.fila-examen').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        cont.appendChild(row);
        idxExamen++;
    }

    let idxDiag = {{ max(1, $diagnosticosGuardados->count()) }};
    function agregarFilaDiagnostico() {
        const cont = document.getElementById('contenedor-diagnosticos');
        if (cont.querySelectorAll('.fila-diagnostico').length >= 6) {
            alert('El formulario HCU-033 permite hasta 6 diagnósticos por apertura.');
            return;
        }
        const opcionesCie = document.querySelector('select[name="diagnosticos[0][cie10_codigo]"]').innerHTML.replace(/selected/g, '');
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-3 fila-diagnostico';
        row.innerHTML = `
            <div class="col-md-5">
                <select name="diagnosticos[${idxDiag}][cie10_codigo]" class="form-select hc-form-select" onchange="autocompletarDescripcionCie(this, ${idxDiag})">
                    ${opcionesCie}
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="diagnosticos[${idxDiag}][descripcion]" id="diag_desc_${idxDiag}" class="form-control hc-form-control" placeholder="Descripción...">
            </div>
            <div class="col-md-2">
                <select name="diagnosticos[${idxDiag}][tipo]" class="form-select hc-form-select">
                    <option value="PRE">PRE (Presuntivo)</option>
                    <option value="DEF">DEF (Definitivo)</option>
                </select>
            </div>
            <div class="col-md-1">
                <input type="number" name="diagnosticos[${idxDiag}][pieza_dental]" class="form-control hc-form-control text-center"
                       min="11" max="85" oninput="validarInputPiezaDental(this)" onblur="if(this.value.length===1)this.value=''" placeholder="Pieza">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.fila-diagnostico').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        cont.appendChild(row);
        idxDiag++;
    }

    // Restringe estrictamente la pieza dental a la nomenclatura FDI del odontograma (11-48 permanentes y 51-85 temporales)
    function validarInputPiezaDental(input) {
        let limpio = input.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
        if (limpio === '') {
            input.value = '';
            return;
        }

        // El primer dígito (cuadrante) solo puede ser del 1 al 8
        let c = parseInt(limpio[0], 10);
        if (c < 1 || c > 8) {
            input.value = '';
            return;
        }

        if (limpio.length === 1) {
            input.value = String(c);
            return;
        }

        // El segundo dígito (diente) es de 1 a 8 en permanentes (cuadrantes 1-4) y de 1 a 5 en temporales (cuadrantes 5-8)
        let d = parseInt(limpio[1], 10);
        let maxDiente = (c <= 4) ? 8 : 5;
        if (d < 1) {
            input.value = String(c);
            return;
        }
        if (d > maxDiente) {
            d = maxDiente;
        }

        // Máximo 2 dígitos exactos (ej: 11 a 85, impide poner > 100 como 118)
        input.value = String(c) + String(d);
    }

    function autocompletarDescripcionCie(selectEl, index) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const descInput = document.getElementById(`diag_desc_${index}`);
        if (opt && opt.dataset.nombre && descInput && !descInput.value.trim()) {
            descInput.value = opt.dataset.nombre;
        }
    }

    // --- Inicialización de datos guardados (modo edición de borrador o validación old()) ---
    document.addEventListener('DOMContentLoaded', function() {
        // Si un campo en un paso oculto falla validación HTML5 al enviar, saltar automáticamente a ese paso
        const formEl = document.getElementById('form-historia');
        if (formEl) {
            formEl.addEventListener('invalid', function(e) {
                const stepPane = e.target.closest('.hc-step-content');
                if (stepPane) {
                    const stepIdx = parseInt(stepPane.getAttribute('data-step'), 10);
                    if (!isNaN(stepIdx) && stepIdx !== pasoActual) {
                        irAPaso(stepIdx);
                    }
                }
            }, true);
        }

        const jsonGuardado = document.getElementById('odontograma_json')?.value;
        if (jsonGuardado && jsonGuardado !== '[]') {
            try {
                const parsed = JSON.parse(jsonGuardado);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    registrosOdontogramaWizard = parsed;
                    registrosOdontogramaWizard.forEach(reg => {
                        renderizarDienteSvg(reg.pieza, reg);
                    });
                    renderizarListaOdontogramaWizard();
                }
            } catch (e) {
                console.error('Error al cargar odontograma del borrador:', e);
            }
        }
        actualizarTotalesCpo();
    });
</script>
@endpush
