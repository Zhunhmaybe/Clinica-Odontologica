@extends('layouts.nav-bar')

@section('title', 'Usuarios')

@push('styles')
    @vite(['resources/css/admin/usuarios.css'])
@endpush

@section('content')
<div class="container-fluid py-3 px-3">
    <!-- Mensajes de Alerta -->
    <div style="max-width: 1050px; margin: 0 auto;">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- TARJETA PRINCIPAL (ESTILO EXACTO DE PROYECT-ORACLE) -->
    <div class="page-card">
        <!-- HERO -->
        <div class="page-hero">
            <div class="title">
                <div class="hero-icon">👥</div>
                <div>
                    <h2>Usuarios</h2>
                    <p>Listado y gestión de usuarios del sistema</p>
                </div>
            </div>

            @can('crear-usuarios')
            <div>
                <button type="button" class="btn-create-user" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                    <i class="fas fa-plus"></i> Nuevo Usuario
                </button>
            </div>
            @endcan
        </div>

        <!-- TABLA -->
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table-oracle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            @can('editar-usuarios')
                            <th class="text-end">Acción</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $u)
                            <tr>
                                <td class="fw-semibold text-dark">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2 text-primary fw-bold" style="width: 34px; height: 34px; font-size: 13px;">
                                            {{ strtoupper(substr($u->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            {{ $u->nombre }}
                                            @if($u->tel)
                                                <small class="d-block text-muted" style="font-size: 11px;">Tel: {{ $u->tel }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $u->email }}</td>
                                <td>
                                    <span class="badge-role">
                                        {{ $u->nombre_rol }}
                                    </span>
                                </td>
                                <td>
                                    @if(($u->estado ?? 1) == 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                            <i class="fas fa-check-circle me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                            <i class="fas fa-ban me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>
                                @can('editar-usuarios')
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <!-- Botón Editar / Rol -->
                                        <button type="button" class="btn-edit-role" data-bs-toggle="modal" data-bs-target="#modalEditar{{ $u->id }}">
                                            <i class="fas fa-user-edit"></i> Editar / Rol
                                        </button>

                                        <!-- Alternar Estado -->
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('admin.usuarios.toggle', $u->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn-toggle-status {{ ($u->estado ?? 1) == 1 ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        title="{{ ($u->estado ?? 1) == 1 ? 'Desactivar usuario' : 'Activar usuario' }}">
                                                    <i class="fas {{ ($u->estado ?? 1) == 1 ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Modal Editar Usuario (Inspirado en edit.blade.php del proyecto anterior) -->
                                    <div class="modal fade text-start" id="modalEditar{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                                <div class="modal-header" style="background: #b7d8ee; border-bottom: 1px solid #a3c8e0;">
                                                    <h5 class="modal-title fw-bold" style="color: #0b4f79;">
                                                        🛠️ Editar Usuario
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.usuarios.update', $u->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-dark">Nombre Completo</label>
                                                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $u->nombre) }}" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-dark">Correo Electrónico</label>
                                                            <input type="email" name="email" class="form-control" value="{{ old('email', $u->email) }}" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-dark">Rol del Usuario</label>
                                                            <select name="rol" class="form-select" required>
                                                                @foreach($roles as $rol)
                                                                    <option value="{{ $rol->name }}" {{ $u->hasRole($rol->name) ? 'selected' : '' }}>
                                                                        {{ ucfirst($rol->name) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <small class="text-muted">Gestionado mediante el sistema de roles y permisos.</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light border-top">
                                                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Volver</button>
                                                        <button type="submit" class="btn btn-primary px-4" style="background: #0b4f79; border-color: #0b4f79;">
                                                            💾 Guardar cambios
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    No hay usuarios registrados en el sistema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($usuarios->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $usuarios->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@can('crear-usuarios')
<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #b7d8ee; border-bottom: 1px solid #a3c8e0;">
                <h5 class="modal-title fw-bold" style="color: #0b4f79;">
                    ➕ Registrar Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Dr. Mario Gómez" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="correo@clinica.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Teléfono</label>
                        <input type="text" name="tel" class="form-control" placeholder="Ej. 0991234567" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Rol Asignado</label>
                        <select name="rol" class="form-select" required>
                            <option value="" disabled selected>Seleccione un rol...</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->name }}">{{ ucfirst($rol->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4" style="background: #0b4f79; border-color: #0b4f79;">
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
