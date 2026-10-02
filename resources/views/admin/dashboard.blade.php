@extends('layouts.nav-bar')

@section('title', 'Panel de Administración')

@section('content')
<div class="container-fluid py-4 px-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="fas fa-user-shield text-primary me-2"></i>Panel de Administración
            </h2>
            <p class="text-muted mb-0">Bienvenido/a, <strong>{{ Auth::user()->nombre }}</strong>. Aquí tienes una visión general del sistema.</p>
        </div>
        <div>
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill shadow-sm">
                <i class="fas fa-shield-alt me-1"></i> {{ Auth::user()->getRoleNames()->first() ?? 'Administrador' }}
            </span>
        </div>
    </div>

    <!-- Mensajes de Alerta -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tarjetas de Métricas Rápidas -->
    <div class="row g-3 mb-4">
        <!-- Usuarios Registrados -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-users-cog fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Usuarios</div>
                        <h3 class="fw-bold mb-0">{{ $totalUsuarios ?? 0 }}</h3>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('admin.usuarios.index') }}" class="text-decoration-none small text-primary fw-medium">
                        Gestionar usuarios <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pacientes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-hospital-user fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Pacientes</div>
                        <h3 class="fw-bold mb-0">Módulo</h3>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('pacientes.index') }}" class="text-decoration-none small text-success fw-medium">
                        Ver directorio <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Citas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Agenda Citas</div>
                        <h3 class="fw-bold mb-0">Calendario</h3>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('citas.index') }}" class="text-decoration-none small text-warning fw-medium">
                        Ver agenda <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Historias Clínicas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-notes-medical fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Historias Clínicas</div>
                        <h3 class="fw-bold mb-0">Expedientes</h3>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('historia_clinica.index') }}" class="text-decoration-none small text-info fw-medium">
                        Ver expedientes <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Usuarios Recientes -->
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-clock text-secondary me-2"></i>Usuarios Registrados Recientemente
                    </h5>
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        Ver Todos
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuariosRecientes as $usuario)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2 text-secondary fw-bold" style="width: 38px; height: 38px;">
                                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $usuario->nombre }}</div>
                                                <small class="text-muted">{{ $usuario->tel ?? 'Sin teléfono' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $usuario->email }}</span>
                                    </td>
                                    <td>
                                        @foreach($usuario->roles as $rol)
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">
                                                {{ $rol->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if(($usuario->estado ?? 1) == 1)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                <i class="fas fa-circle fa-2xs me-1"></i>Activo
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                <i class="fas fa-circle fa-2xs me-1"></i>Inactivo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No hay usuarios registrados recientemente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos y Administración -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark">
                    <i class="fas fa-bolt text-warning me-2"></i>Acciones Rápidas
                </h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-primary text-start py-2 px-3 rounded-3 d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-user-plus me-2"></i>Gestionar Usuarios</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('pacientes.create') }}" class="btn btn-outline-success text-start py-2 px-3 rounded-3 d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-user-injured me-2"></i>Registrar Nuevo Paciente</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('historia_clinica.create') }}" class="btn btn-outline-info text-start py-2 px-3 rounded-3 d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-file-medical me-2"></i>Nueva Historia Clínica</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                    @can('ver-auditoria')
                    <a href="{{ route('auditor.index') }}" class="btn btn-outline-secondary text-start py-2 px-3 rounded-3 d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-history me-2"></i>Módulo de Auditoría</span>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </a>
                    @endcan
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection
