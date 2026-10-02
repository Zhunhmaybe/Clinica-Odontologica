@extends('layouts.nav-bar')

@section('title', 'Nueva Historia Clínica')

@push('styles')
<link rel="stylesheet" href="{{ asset('build/assets/historia-clinica.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="hc-header-banner">
        <h2><i class="fas fa-file-medical"></i> Nueva Historia Clínica Odontológica</h2>
        <p class="mb-0 opacity-90">Formulario basado en SNS-MSP/HCU-FORM.033/2021</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 rounded-3">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>¡Error!</strong> Por favor corrija los siguientes errores:
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Stepper Visual --}}
    <div class="hc-stepper" id="stepper-nav">
        <div class="hc-step active" data-step="0" onclick="irAPaso(0)">
            <div class="hc-step-number">A</div>
            <div class="hc-step-label">Datos Paciente</div>
        </div>
        <div class="hc-step" data-step="1" onclick="irAPaso(1)">
            <div class="hc-step-number">B</div>
            <div class="hc-step-label">Motivo</div>
        </div>
        <div class="hc-step" data-step="2" onclick="irAPaso(2)">
            <div class="hc-step-number">C</div>
            <div class="hc-step-label">Enfermedad</div>
        </div>
        <div class="hc-step" data-step="3" onclick="irAPaso(3)">
            <div class="hc-step-number">D</div>
            <div class="hc-step-label">Ant. Personales</div>
        </div>
        <div class="hc-step" data-step="4" onclick="irAPaso(4)">
            <div class="hc-step-number">E</div>
            <div class="hc-step-label">Ant. Familiares</div>
        </div>
        <div class="hc-step" data-step="5" onclick="irAPaso(5)">
            <div class="hc-step-number">F</div>
            <div class="hc-step-label">Constantes</div>
        </div>
        <div class="hc-step" data-step="6" onclick="irAPaso(6)">
            <div class="hc-step-number">G</div>
            <div class="hc-step-label">Examen + Obs.</div>
        </div>
    </div>

    <form action="{{ route('historia_clinica.store') }}" method="POST" id="form-historia">
        @csrf
        <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">

        {{-- PASO 0: DATOS DEL PACIENTE --}}
        <div class="hc-step-content active" data-step="0">
            <div class="hc-card">
                <div class="hc-card-header section-a">
                    <i class="fas fa-user-circle"></i> A. DATOS DEL PACIENTE
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombres:</strong> {{ $paciente->nombres }}</p>
                            <p><strong>Apellidos:</strong> {{ $paciente->apellidos }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Cédula:</strong> {{ $paciente->cedula }}</p>
                            <p><strong>Edad:</strong> {{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age . ' años' : 'No registrada' }}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="hc-form-label">Fecha de Atención *</label>
                            <input type="date" name="fecha_atencion" class="form-control hc-form-control"
                                   value="{{ old('fecha_atencion', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- PASO 1: MOTIVO DE CONSULTA --}}
        <div class="hc-step-content" data-step="1">
            <div class="hc-card">
                <div class="hc-card-header section-b">
                    <i class="fas fa-comment-medical"></i> B. MOTIVO DE CONSULTA
                </div>
                <div class="card-body">
                    <textarea name="motivo_consulta" class="form-control hc-form-control" rows="4" required
                              placeholder="Describa el motivo principal de la consulta...">{{ old('motivo_consulta') }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- PASO 2: ENFERMEDAD ACTUAL --}}
        <div class="hc-step-content" data-step="2">
            <div class="hc-card">
                <div class="hc-card-header section-b">
                    <i class="fas fa-disease"></i> C. ENFERMEDAD ACTUAL
                </div>
                <div class="card-body">
                    <textarea name="enfermedad_actual" class="form-control hc-form-control" rows="4"
                              placeholder="Descripción detallada de la enfermedad actual...">{{ old('enfermedad_actual') }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- PASO 3: ANTECEDENTES PERSONALES --}}
        <div class="hc-step-content" data-step="3">
            <div class="hc-card">
                <div class="hc-card-header section-d">
                    <i class="fas fa-heartbeat"></i> D. ANTECEDENTES PATOLÓGICOS PERSONALES
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="hc-form-label">Alergias</label>
                            <textarea name="alergias" class="form-control hc-form-control" rows="2"
                                      placeholder="Especifique alergias conocidas...">{{ old('alergias') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="hc-form-label">Otros Antecedentes</label>
                            <textarea name="antecedentes_otros" class="form-control hc-form-control" rows="2"
                                      placeholder="Otros antecedentes relevantes...">{{ old('antecedentes_otros') }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cardiopatias" id="cardiopatias" value="1" {{ old('cardiopatias') ? 'checked' : '' }}>
                                <label class="form-check-label" for="cardiopatias">Cardiopatías</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="diabetes" id="diabetes" value="1" {{ old('diabetes') ? 'checked' : '' }}>
                                <label class="form-check-label" for="diabetes">Diabetes</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="hipertension" id="hipertension" value="1" {{ old('hipertension') ? 'checked' : '' }}>
                                <label class="form-check-label" for="hipertension">Hipertensión</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tuberculosis" id="tuberculosis" value="1" {{ old('tuberculosis') ? 'checked' : '' }}>
                                <label class="form-check-label" for="tuberculosis">Tuberculosis</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- PASO 4: ANTECEDENTES FAMILIARES --}}
        <div class="hc-step-content" data-step="4">
            <div class="hc-card">
                <div class="hc-card-header section-d">
                    <i class="fas fa-users"></i> E. ANTECEDENTES PATOLÓGICOS FAMILIARES
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fam_cardiopatias" id="fam_cardiopatias" value="1" {{ old('fam_cardiopatias') ? 'checked' : '' }}>
                                <label class="form-check-label" for="fam_cardiopatias">Cardiopatías</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fam_diabetes" id="fam_diabetes" value="1" {{ old('fam_diabetes') ? 'checked' : '' }}>
                                <label class="form-check-label" for="fam_diabetes">Diabetes</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fam_hipertension" id="fam_hipertension" value="1" {{ old('fam_hipertension') ? 'checked' : '' }}>
                                <label class="form-check-label" for="fam_hipertension">Hipertensión</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fam_cancer" id="fam_cancer" value="1" {{ old('fam_cancer') ? 'checked' : '' }}>
                                <label class="form-check-label" for="fam_cancer">Cáncer</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="fam_tuberculosis" id="fam_tuberculosis" value="1" {{ old('fam_tuberculosis') ? 'checked' : '' }}>
                                <label class="form-check-label" for="fam_tuberculosis">Tuberculosis</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- PASO 5: CONSTANTES VITALES --}}
        <div class="hc-step-content" data-step="5">
            <div class="hc-card">
                <div class="hc-card-header section-f">
                    <i class="fas fa-stethoscope"></i> F. CONSTANTES VITALES
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="hc-form-label">Temperatura (°C)</label>
                            <input type="number" step="0.1" name="temperatura" class="form-control hc-form-control"
                                   placeholder="36.5" value="{{ old('temperatura') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="hc-form-label">Presión Arterial (mmHg)</label>
                            <input type="text" name="presion_arterial" class="form-control hc-form-control"
                                   placeholder="120/80" value="{{ old('presion_arterial') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="hc-form-label">Pulso (lpm)</label>
                            <input type="number" name="pulso" class="form-control hc-form-control"
                                   placeholder="70" value="{{ old('pulso') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="hc-form-label">Frecuencia Respiratoria (rpm)</label>
                            <input type="number" name="frecuencia_respiratoria" class="form-control hc-form-control"
                                   placeholder="16" value="{{ old('frecuencia_respiratoria') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="button" class="hc-btn-action hc-btn-primary" onclick="siguientePaso()">
                    Siguiente <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- PASO 6: EXAMEN ESTOMATOGNÁTICO + OBSERVACIONES --}}
        <div class="hc-step-content" data-step="6">
            <div class="hc-card">
                <div class="hc-card-header section-g">
                    <i class="fas fa-teeth"></i> G. EXAMEN DEL SISTEMA ESTOMATOGNÁTICO
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">Labios</label>
                            <input type="text" name="labios" class="form-control hc-form-control" placeholder="Normal" value="{{ old('labios') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">Lengua</label>
                            <input type="text" name="lengua" class="form-control hc-form-control" placeholder="Normal" value="{{ old('lengua') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">Paladar</label>
                            <input type="text" name="paladar" class="form-control hc-form-control" placeholder="Normal" value="{{ old('paladar') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">Piso de Boca</label>
                            <input type="text" name="piso_boca" class="form-control hc-form-control" placeholder="Normal" value="{{ old('piso_boca') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">Encías</label>
                            <input type="text" name="encias" class="form-control hc-form-control" placeholder="Normal" value="{{ old('encias') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">Carrillos</label>
                            <input type="text" name="carrillos" class="form-control hc-form-control" placeholder="Normal" value="{{ old('carrillos') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">Orofaringe</label>
                            <input type="text" name="orofaringe" class="form-control hc-form-control" placeholder="Normal" value="{{ old('orofaringe') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="hc-form-label">ATM</label>
                            <input type="text" name="atm" class="form-control hc-form-control" placeholder="Normal" value="{{ old('atm') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="hc-card">
                <div class="hc-card-header section-obs">
                    <i class="fas fa-comment-dots"></i> OBSERVACIONES GENERALES
                </div>
                <div class="card-body">
                    <textarea name="observaciones" class="form-control hc-form-control" rows="4"
                              placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                </div>
            </div>

            {{-- Botones Finales --}}
            <div class="d-flex justify-content-between">
                <button type="button" class="hc-btn-action btn btn-outline-secondary" onclick="anteriorPaso()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="submit" class="hc-btn-action hc-btn-success btn-lg">
                    <i class="fas fa-save me-1"></i> Crear Historia Clínica y Continuar al Odontograma
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    let pasoActual = 0;
    const totalPasos = 7;

    function irAPaso(paso) {
        if (paso < 0 || paso >= totalPasos) return;

        // Ocultar paso actual
        document.querySelectorAll('.hc-step-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.hc-step').forEach(el => el.classList.remove('active'));

        // Marcar pasos anteriores como completados
        document.querySelectorAll('.hc-step').forEach((el, i) => {
            el.classList.remove('completed');
            if (i < paso) el.classList.add('completed');
        });

        // Mostrar paso nuevo
        document.querySelector(`.hc-step-content[data-step="${paso}"]`).classList.add('active');
        document.querySelector(`.hc-step[data-step="${paso}"]`).classList.add('active');

        pasoActual = paso;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function siguientePaso() {
        irAPaso(pasoActual + 1);
    }

    function anteriorPaso() {
        irAPaso(pasoActual - 1);
    }

    // Auto-guardar borrador cada 30 segundos
    setInterval(function() {
        const formData = new FormData(document.getElementById('form-historia'));
        const data = Object.fromEntries(formData.entries());
        localStorage.setItem('historia_clinica_draft', JSON.stringify(data));
    }, 30000);

    // Recuperar borrador al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const draft = localStorage.getItem('historia_clinica_draft');
        if (draft && confirm('Se encontró un borrador guardado. ¿Desea recuperarlo?')) {
            const data = JSON.parse(draft);
            Object.keys(data).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = data[key] === '1';
                    } else {
                        field.value = data[key];
                    }
                }
            });
        }
    });

    // Limpiar borrador al enviar
    document.getElementById('form-historia').addEventListener('submit', function() {
        localStorage.removeItem('historia_clinica_draft');
    });
</script>
@endpush
