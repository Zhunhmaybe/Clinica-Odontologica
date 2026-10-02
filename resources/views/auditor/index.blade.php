@extends('layouts.nav-bar')
@section('title', 'Panel de Auditoría')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-shield-alt text-primary me-2"></i>Panel de Auditoría y Seguridad
            </h3>
            <p class="text-muted mb-0">Supervisión integral de accesos, 2FA, dispositivos y operaciones en base de datos.</p>
        </div>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-history fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total de Registros de Auditoría</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalLogs ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-laptop-code fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Sesiones Registradas en BDD</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ \Illuminate\Support\Facades\DB::table('sessions')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Usuarios con 2FA Activo</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ \App\Models\User::where('two_factor_enabled', true)->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas Disponibles para Inspección -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-database text-secondary me-2"></i>Tablas Disponibles para Auditoría
            </h5>
        </div>
        <div class="card-body p-4 pt-1">
            <div class="row g-3">
                @foreach ($tablasDisponibles as $tbl)
                    <div class="col-md-4 col-sm-6">
                        <div class="card border rounded-3 p-3 h-100 hover-shadow transition">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-primary mb-0 text-capitalize">
                                    {{ str_replace('_', ' ', $tbl) }}
                                </h6>
                                <span class="badge bg-light text-dark border">
                                    {{ \Illuminate\Support\Facades\DB::table($tbl)->count() }} registros
                                </span>
                            </div>
                            <p class="text-muted small mb-3">
                                Inspeccionar logs, cambios, IPs y agentes de {{ $tbl }}.
                            </p>
                            <a href="{{ route('auditor.tablas.show', ['tabla' => $tbl]) }}" class="btn btn-sm btn-outline-primary rounded-pill mt-auto">
                                <i class="fas fa-eye me-1"></i> Ver Registros
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection