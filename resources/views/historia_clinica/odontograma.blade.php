@extends('layouts.nav-bar')

@section('title', 'Odontograma Interactivo')

@push('styles')
<link rel="stylesheet" href="{{ asset('build/assets/historia-clinica.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4"
     data-historia-id="{{ $historia->id }}"
     data-save-url="{{ route('historia_clinica.odontograma.guardar', $historia->id) }}"
     data-load-url="{{ route('historia_clinica.odontograma.json', $historia->id) }}">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-primary mb-0"><i class="fas fa-tooth"></i> Odontograma Digital</h3>
            <p class="text-muted mb-0">Paciente: <strong>{{ $historia->paciente->nombres ?? '' }} {{ $historia->paciente->apellidos ?? '' }}</strong>
                | Historia: <strong>{{ $historia->numero_historia ?? '' }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('historia_clinica.show', $historia->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button type="button" class="btn btn-success" onclick="guardarOdontograma()">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 rounded-3 alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        {{-- PANEL LATERAL: Tratamientos Registrados --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-list"></i> Tratamientos Registrados</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="lista-tratamientos" style="max-height: 600px; overflow-y: auto;">
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-info-circle mb-2"></i><br>
                            Haga clic en un diente para agregar un diagnóstico o tratamiento.
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <button class="btn btn-outline-danger btn-sm w-100" onclick="resetearOdontograma()">
                        <i class="fas fa-trash"></i> Limpiar Todo
                    </button>
                </div>
            </div>
        </div>

        {{-- ODONTOGRAMA VISUAL --}}
        <div class="col-md-9">
            <div class="odontograma-wrapper text-center">

                {{-- Leyenda --}}
                <div class="d-flex justify-content-center gap-3 mb-4 text-muted small flex-wrap">
                    <span class="d-flex align-items-center"><span style="width:12px;height:12px;background:red;margin-right:5px;border-radius:2px;"></span> Patología (Caries)</span>
                    <span class="d-flex align-items-center"><span style="width:12px;height:12px;background:blue;margin-right:5px;border-radius:2px;"></span> Restaurado/Bueno</span>
                    <span class="d-flex align-items-center"><span style="width:12px;height:12px;background:gold;margin-right:5px;border-radius:2px;"></span> En Proceso</span>
                    <span class="d-flex align-items-center"><span style="width:12px;height:12px;background:black;margin-right:5px;border-radius:2px;"></span> Perdido/Ausente</span>
                </div>

                {{-- ADULTOS SUPERIOR (18-11 | 21-28) --}}
                <div class="mb-4">
                    <span class="me-3"></span>
                    @foreach([18,17,16,15,14,13,12,11] as $pieza)
                        @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                    @endforeach
                    <span class="mx-3 border-end" style="border-color: #999 !important;"></span>
                    @foreach([21,22,23,24,25,26,27,28] as $pieza)
                        @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                    @endforeach
                </div>

                {{-- NIÑOS (55-51 | 61-65 / 85-81 | 71-75) --}}
                <div class="mb-4 bg-light p-3 rounded d-inline-block">
                    <div class="mb-2">
                        @foreach([55,54,53,52,51] as $pieza)
                            @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                        @endforeach
                        <span class="mx-3 border-end" style="border-color: #999 !important;"></span>
                        @foreach([61,62,63,64,65] as $pieza)
                            @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                        @endforeach
                    </div>
                    <div>
                        @foreach([85,84,83,82,81] as $pieza)
                            @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                        @endforeach
                        <span class="mx-3 border-end" style="border-color: #999 !important;"></span>
                        @foreach([71,72,73,74,75] as $pieza)
                            @include('historia_clinica.partials.diente_nino', ['pieza' => $pieza])
                        @endforeach
                    </div>
                </div>

                {{-- ADULTOS INFERIOR (48-41 | 31-38) --}}
                <div class="mt-4">
                    <span class="me-3"></span>
                    @foreach([48,47,46,45,44,43,42,41] as $pieza)
                        @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                    @endforeach
                    <span class="mx-3 border-end" style="border-color: #999 !important;"></span>
                    @foreach([31,32,33,34,35,36,37,38] as $pieza)
                        @include('historia_clinica.partials.diente_adulto', ['pieza' => $pieza])
                    @endforeach
                </div>

            </div>

            {{-- Índices de Salud Bucal --}}
            <div class="hc-indices-container mt-4">
                <h5><i class="fas fa-chart-bar text-primary me-2"></i>Índices de Salud Bucal</h5>
                <div class="row">
                    {{-- CPO-D (Adultos) --}}
                    <div class="col-md-6">
                        <div class="hc-indice-grupo">
                            <label class="d-block mb-2"><strong>CPO-D</strong> (Permanentes)</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="form-label small">Cariados</label>
                                    <input type="number" class="form-control form-control-sm" id="cpo_cariados" value="0" min="0">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">Perdidos</label>
                                    <input type="number" class="form-control form-control-sm" id="cpo_perdidos" value="0" min="0">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">Obturados</label>
                                    <input type="number" class="form-control form-control-sm" id="cpo_obturados" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- ceo-d (Temporales) --}}
                    <div class="col-md-6">
                        <div class="hc-indice-grupo">
                            <label class="d-block mb-2"><strong>ceo-d</strong> (Temporales)</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="form-label small">Cariados</label>
                                    <input type="number" class="form-control form-control-sm" id="ceo_cariados" value="0" min="0">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">Extracción</label>
                                    <input type="number" class="form-control form-control-sm" id="ceo_extraccion" value="0" min="0">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">Obturados</label>
                                    <input type="number" class="form-control form-control-sm" id="ceo_obturados" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="hc-indice-grupo">
                            <label class="form-label small">Placa Bacteriana</label>
                            <input type="number" class="form-control form-control-sm" id="placa_bacteriana" value="0" min="0" max="100" step="0.1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="hc-indice-grupo">
                            <label class="form-label small">Cálculo Dental</label>
                            <input type="number" class="form-control form-control-sm" id="calculo_dental" value="0" min="0" max="100" step="0.1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="hc-indice-grupo">
                            <label class="form-label small">Gingivitis</label>
                            <input type="number" class="form-control form-control-sm" id="gingivitis" value="0" min="0" max="100" step="0.1">
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="hc-indice-grupo">
                            <label class="form-label small">Nivel de Fluorosis</label>
                            <select class="form-select form-select-sm" id="nivel_fluorosis">
                                <option value="0">Normal</option>
                                <option value="1">Muy Leve</option>
                                <option value="2">Leve</option>
                                <option value="3">Moderada</option>
                                <option value="4">Severa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="hc-indice-grupo">
                            <label class="form-label small">Tipo de Oclusión</label>
                            <select class="form-select form-select-sm" id="tipo_oclusion">
                                <option value="0">Normal</option>
                                <option value="1">Clase I</option>
                                <option value="2">Clase II</option>
                                <option value="3">Clase III</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE TRATAMIENTO --}}
<div class="modal fade" id="modalTratamiento" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-tooth me-2"></i> Diente <span id="lbl-diente-seleccionado"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-tratamiento">

                    {{-- 1. Categoría --}}
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CATEGORÍA</label>
                        <select class="form-select" id="select-categoria" onchange="cargarTratamientos()">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    {{-- 2. Tratamiento --}}
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">TRATAMIENTO / DIAGNÓSTICO</label>
                        <select class="form-select" id="select-tratamiento" disabled>
                            <option value="">Seleccione categoría primero...</option>
                        </select>
                    </div>

                    {{-- 3. Estado --}}
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">ESTADO</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="estado_tratamiento" id="estado_malo" value="malo" checked>
                            <label class="btn btn-outline-danger" for="estado_malo">Por Realizar / Patología (Rojo)</label>
                            <input type="radio" class="btn-check" name="estado_tratamiento" id="estado_bueno" value="bueno">
                            <label class="btn btn-outline-primary" for="estado_bueno">Realizado / Buen Estado (Azul)</label>
                        </div>
                    </div>

                    {{-- 4. Caras Afectadas --}}
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CARAS AFECTADAS</label>
                        <div class="d-flex justify-content-between gap-2">
                            <div class="flex-fill">
                                <input type="checkbox" class="btn-check cara-checkbox" id="chk-vestibular" value="vestibular">
                                <label class="btn btn-cara w-100 btn-sm" for="chk-vestibular">V</label>
                            </div>
                            <div class="flex-fill">
                                <input type="checkbox" class="btn-check cara-checkbox" id="chk-distal" value="distal">
                                <label class="btn btn-cara w-100 btn-sm" for="chk-distal">D</label>
                            </div>
                            <div class="flex-fill">
                                <input type="checkbox" class="btn-check cara-checkbox" id="chk-mesial" value="mesial">
                                <label class="btn btn-cara w-100 btn-sm" for="chk-mesial">M</label>
                            </div>
                            <div class="flex-fill">
                                <input type="checkbox" class="btn-check cara-checkbox" id="chk-palatina" value="palatina">
                                <label class="btn btn-cara w-100 btn-sm" for="chk-palatina">P/L</label>
                            </div>
                            <div class="flex-fill">
                                <input type="checkbox" class="btn-check cara-checkbox" id="chk-oclusal" value="oclusal">
                                <label class="btn btn-cara w-100 btn-sm" for="chk-oclusal">O</label>
                            </div>
                        </div>
                        <div class="form-text small mt-2">* V: Vestibular, D: Distal, M: Mesial, P/L: Palatino/Lingual, O: Oclusal/Incisal</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">OBSERVACIONES</label>
                        <textarea class="form-control" id="txt-observacion" rows="2"></textarea>
                    </div>

                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" onclick="aplicarTratamiento()">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div class="hc-toast-container" id="toast-container"></div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/odontograma.js') }}"></script>
@endpush
