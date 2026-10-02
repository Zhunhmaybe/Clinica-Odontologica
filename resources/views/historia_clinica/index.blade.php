@extends('layouts.nav-bar')

@section('title', 'Historial Clínico')

@push('styles')
<link rel="stylesheet" href="{{ asset('build/assets/historia-clinica.css') }}">
<style>
    /* Inline fallback si Vite no compila el CSS aún */
    @import url('/resources/css/historia-clinica.css');
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="hc-header-banner">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2><i class="fas fa-notes-medical"></i> Gestión de Historias Clínicas</h2>
                <p class="mb-0 opacity-90">Listado general de atenciones y expedientes odontológicos.</p>
            </div>
            @can('crear-pacientes')
            <a href="{{ route('pacientes.index') }}" class="hc-btn-action hc-btn-primary">
                <i class="fas fa-plus"></i> Nueva Historia (Desde Pacientes)
            </a>
            @endcan
        </div>
    </div>

    @if(isset($pacienteFiltrado) && $pacienteFiltrado)
    <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center mb-4 rounded-4 shadow-sm gap-2" role="alert">
        <div>
            <i class="fas fa-user-check me-2 text-primary fa-lg"></i>
            Mostrando historias clínicas de: <strong>{{ $pacienteFiltrado->nombres }} {{ $pacienteFiltrado->apellidos }}</strong> (Cédula: {{ $pacienteFiltrado->cedula }})
        </div>
        <div class="d-flex gap-2">
            @can('editar-historia')
            <a href="{{ route('historia_clinica.create', ['paciente_id' => $pacienteFiltrado->id]) }}" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                <i class="fas fa-plus me-1"></i> Nueva Historia
            </a>
            @endcan
            <a href="{{ route('historia_clinica.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 bg-white">
                <i class="fas fa-times me-1"></i> Ver Todos
            </a>
        </div>
    </div>
    @endif

    {{-- Tarjeta Principal --}}
    <div class="hc-card">
        <div class="card-body">

            {{-- Buscador Real-time --}}
            <div class="row mb-3">
                <div class="col-md-5">
                    <div class="hc-search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="buscador-historias" class="form-control"
                               placeholder="Buscar por paciente, cédula o número de historia...">
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="table-responsive">
                <table class="table table-hover hc-table align-middle" id="tabla-historias">
                    <thead>
                        <tr>
                            <th>Nº Historia</th>
                            <th>Paciente</th>
                            <th>Fecha Atención</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historias as $historia)
                        <tr class="fila-historia">
                            <td>
                                <span class="fw-bold text-dark">#{{ $historia->numero_historia }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="hc-avatar">
                                        {{ strtoupper(substr($historia->paciente->nombres ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $historia->paciente->nombres ?? '' }} {{ $historia->paciente->apellidos ?? '' }}</div>
                                        <small class="text-muted">{{ $historia->paciente->cedula ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="far fa-calendar-alt text-muted me-1"></i>
                                {{ $historia->fecha_atencion ? $historia->fecha_atencion->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                      title="{{ $historia->motivo_consulta }}">
                                    {{ Str::limit($historia->motivo_consulta, 30) }}
                                </span>
                            </td>
                            <td>
                                @if($historia->estado_historia === 'abierta')
                                <span class="hc-badge-abierta">Abierta</span>
                                @else
                                <span class="hc-badge-cerrada">Cerrada</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('historia_clinica.show', $historia->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('editar-historia')
                                    <a href="{{ route('historia_clinica.odontograma', $historia->id) }}"
                                       class="btn btn-sm btn-outline-warning" title="Odontograma">
                                        <i class="fas fa-tooth"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                    <p class="mb-0">No se encontraron historias clínicas registradas.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $historias->links() }}
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Buscador en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscador-historias');
        if (!buscador) return;

        buscador.addEventListener('input', function() {
            const termino = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.fila-historia');

            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(termino) ? '' : 'none';
            });
        });
    });
</script>
@endpush
