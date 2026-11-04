@extends('layouts.nav-bar')

@section('title', 'Gestión')

@push('styles')
    @vite(['resources/css/admin/usuarios.css'])
@endpush

@section('content')
<div class="container-fluid py-3 px-3 gestion-container">
    <!-- Mensajes de Alerta -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <!-- ===================================================================
         PARTICIÓN 1: GESTIÓN DE USUARIOS (CRUD COMPLETO CON BÚSQUEDA Y FILTROS)
         =================================================================== -->
    <div class="page-card mb-4">
        <!-- HERO USUARIOS -->
        <div class="page-hero">
            <div class="title">
                <div class="hero-icon">👥</div>
                <div>
                    <h2>Gestión de Usuarios</h2>
                    <p>Administración integral de cuentas de acceso, roles y asignación clínica</p>
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

        <!-- TOOLBAR DE BÚSQUEDA, FILTRO POR ROL Y ORDENAMIENTO -->
        <div class="table-toolbar">
            <form action="{{ route('admin.usuarios.index') }}" method="GET" class="w-100 d-flex flex-wrap align-items-center justify-content-between gap-2" id="formFiltroUsuarios">
                <!-- Buscador por texto -->
                <div class="search-box-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text"
                           name="search"
                           id="inputBuscarUsuario"
                           class="form-control"
                           placeholder="Buscar por nombre, correo o teléfono..."
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>

                <!-- Filtros y Ordenamiento -->
                <div class="filter-actions">
                    <!-- Filtro por Rol -->
                    <select name="rol" id="selectRol" class="form-select filter-select" onchange="this.form.submit()">
                        <option value="todos" {{ request('rol') == 'todos' || !request('rol') ? 'selected' : '' }}>
                            Todos los roles
                        </option>
                        @foreach($roles as $rolItem)
                            <option value="{{ $rolItem->name }}" {{ request('rol') == $rolItem->name ? 'selected' : '' }}>
                                {{ ucfirst($rolItem->name) }}
                            </option>
                        @endforeach
                        <option value="Super_admin" {{ request('rol') == 'Super_admin' ? 'selected' : '' }}>
                            Super_admin
                        </option>
                    </select>

                    <!-- Ordenamiento A-Z / Z-A / Más recientes -->
                    <select name="sort" id="selectSort" class="form-select filter-select" onchange="this.form.submit()">
                        <option value="name_asc" {{ request('sort', 'name_asc') == 'name_asc' ? 'selected' : '' }}>
                            🔤 Orden: A - Z
                        </option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                            🔤 Orden: Z - A
                        </option>
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>
                            🕒 Más recientes
                        </option>
                    </select>

                    <button type="submit" class="btn-filter-apply">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>

                    @if(request('search') || (request('rol') && request('rol') !== 'todos') || (request('sort') && request('sort') !== 'name_asc'))
                        <a href="{{ route('admin.usuarios.index') }}" class="btn-filter-reset" title="Restablecer filtros">
                            <i class="fas fa-undo"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABLA USUARIOS -->
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table-oracle" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Especialidades</th>
                            <th>Estado</th>
                            @can('editar-usuarios')
                            <th class="text-end">Acciones</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $u)
                            <tr class="fila-usuario" data-nombre="{{ strtolower($u->nombre) }}" data-email="{{ strtolower($u->email) }}" data-rol="{{ strtolower($u->nombre_rol) }}">
                                <td class="fw-semibold text-dark">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2 text-primary fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                                            {{ strtoupper(substr($u->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $u->nombre }}</div>
                                            @if($u->tel)
                                                <small class="text-muted" style="font-size: 11px;">📞 {{ $u->tel }}</small>
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
                                    @if($u->hasAnyRole(['doctor', 'admin', 'Super_admin']))
                                        @if($u->especialidades->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($u->especialidades as $espU)
                                                    <span class="badge-specialty" style="background-color: {{ $espU->color ?? '#0b4f79' }};">
                                                        {{ $espU->nombre }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small fst-italic">Sin especialidades</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">No aplica</span>
                                    @endif
                                </td>
                                <td>
                                    @if(($u->estado ?? 1) == 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                                            <i class="fas fa-check-circle me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                                            <i class="fas fa-ban me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>
                                @can('editar-usuarios')
                                <td class="text-end">
                                    <div class="actions-vertical">
                                        {{-- Botón Especialidades (solo si el rol es clínico) --}}
                                        @if($u->hasAnyRole(['doctor', 'admin', 'Super_admin']))
                                            <button type="button" class="btn-action-item btn-action-especialidad"
                                                    data-bs-toggle="modal" data-bs-target="#modalEspecialidadesUsuario{{ $u->id }}"
                                                    title="Asignar especialidades">
                                                <i class="fas fa-tags"></i> Especialidad
                                            </button>
                                        @endif

                                        {{-- Botón Editar Usuario --}}
                                        <button type="button" class="btn-action-item btn-action-edit" data-bs-toggle="modal" data-bs-target="#modalEditar{{ $u->id }}" title="Editar datos del usuario">
                                            <i class="fas fa-user-edit"></i> Editar
                                        </button>

                                        {{-- Alternar Estado --}}
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('admin.usuarios.toggle', $u->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn-action-item {{ ($u->estado ?? 1) == 1 ? 'btn-action-toggle-danger' : 'btn-action-toggle-success' }}"
                                                        title="{{ ($u->estado ?? 1) == 1 ? 'Desactivar usuario' : 'Activar usuario' }}">
                                                    <i class="fas {{ ($u->estado ?? 1) == 1 ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                    {{ ($u->estado ?? 1) == 1 ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Eliminar Usuario (CRUD - Delete) --}}
                                        @can('eliminar-usuarios')
                                            @if($u->id !== auth()->id())
                                                <form action="{{ route('admin.usuarios.destroy', $u->id) }}" method="POST"
                                                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente al usuario {{ $u->nombre }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action-item btn-action-delete" title="Eliminar usuario">
                                                        <i class="fas fa-trash-alt"></i> Eliminar
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    No se encontraron usuarios con los criterios de búsqueda seleccionados.<br>
                                    <small class="text-muted">Prueba limpiando los filtros o registra un nuevo usuario con el botón superior.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($usuarios->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $usuarios->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ===================================================================
         PARTICIÓN 2 Y 3: PACIENTES (IZQUIERDA) Y ESPECIALIDADES (DERECHA)
         =================================================================== -->
    <div class="row g-4">
        <!-- PARTICIÓN 2: GESTIÓN DE PACIENTES (COL-LG-6) -->
        <div class="col-lg-6 col-md-12">
            <div class="page-card h-100">
                <!-- HERO PACIENTES -->
                <div class="page-hero hero-pacientes">
                    <div class="title">
                        <div class="hero-icon">🏥</div>
                        <div>
                            <h2>Gestión de Pacientes</h2>
                            <p>Catálogo de clientes, historial de citas y datos de contacto</p>
                        </div>
                    </div>

                    @can('crear-pacientes')
                    <div>
                        <a href="{{ route('pacientes.create') }}" class="btn-create-paciente">
                            <i class="fas fa-user-plus"></i> Nuevo Paciente
                        </a>
                    </div>
                    @endcan
                </div>

                <!-- TABLA PACIENTES -->
                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table-oracle">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Contacto</th>
                                    <th>Citas</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pacientes as $pac)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $pac->nombres }} {{ $pac->apellidos }}</div>
                                            <small class="text-muted" style="font-size: 11px;">
                                                🪪 C.I: {{ $pac->cedula }}
                                            </small>
                                        </td>
                                        <td>
                                            <div style="font-size: 12.5px;">📞 {{ $pac->telefono ?? 'Sin tel' }}</div>
                                            <small class="text-muted" style="font-size: 11px;">{{ $pac->email ?? 'Sin correo' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 11px;">
                                                {{ $pac->citas_count ?? 0 }} citas
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="actions-vertical">
                                                <a href="{{ route('citas.index', ['paciente_id' => $pac->id]) }}"
                                                   class="btn-action-item btn-action-cita"
                                                   title="Agendar cita para este paciente">
                                                    <i class="fas fa-calendar-plus"></i> Cita
                                                </a>

                                                <a href="{{ route('pacientes.citas', $pac->id) }}"
                                                   class="btn-action-item btn-action-historial"
                                                   title="Ver historial de citas">
                                                    <i class="fas fa-history"></i> Historial
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            No hay pacientes registrados aún.<br>
                                            <small class="text-muted">Crea un paciente con el botón "+ Nuevo Paciente" o desde la búsqueda de citas.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($pacientes->hasPages())
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $pacientes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- PARTICIÓN 3: GESTIÓN DE ESPECIALIDADES (COL-LG-6) -->
        <div class="col-lg-6 col-md-12">
            <div class="page-card h-100">
                <!-- HERO ESPECIALIDADES -->
                <div class="page-hero hero-especialidades">
                    <div class="title">
                        <div class="hero-icon">🦷</div>
                        <div>
                            <h2>Gestión de Especialidades</h2>
                            <p>Áreas clínicas y catálogo de tratamientos</p>
                        </div>
                    </div>

                    @hasanyrole('admin|Super_admin')
                    <div>
                        <button type="button" class="btn-create-esp" data-bs-toggle="modal" data-bs-target="#modalNuevaEspecialidad">
                            <i class="fas fa-plus"></i> Nueva Especialidad
                        </button>
                    </div>
                    @endhasanyrole
                </div>

                <!-- TABLA ESPECIALIDADES -->
                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table-oracle">
                            <thead>
                                <tr>
                                    <th>Especialidad</th>
                                    <th>Citas</th>
                                    <th>Doctores</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($especialidades as $esp)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="color-dot me-2" style="background-color: {{ $esp->color ?? '#3B82F6' }};"></span>
                                                <span class="fw-semibold text-dark">{{ $esp->nombre }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 11px;">
                                                {{ $esp->citas_count ?? 0 }} citas
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 11px;">
                                                {{ $esp->doctores_count ?? 0 }} doctores
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="actions-vertical">
                                                <!-- Botón Editar Especialidad -->
                                                <button type="button" class="btn-action-item btn-action-edit"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditarEspecialidad{{ $esp->id }}"
                                                        title="Editar especialidad">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>

                                                <!-- Eliminar Especialidad -->
                                                @if(($esp->citas_count ?? 0) === 0)
                                                    <form action="{{ route('especialidades.destroy', $esp->id) }}" method="POST"
                                                          onsubmit="return confirm('¿Seguro que deseas eliminar la especialidad {{ $esp->nombre }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-item btn-action-delete" title="Eliminar especialidad">
                                                            <i class="fas fa-trash-alt"></i> Eliminar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            No hay especialidades registradas aún.<br>
                                            <small class="text-muted">Crea una con el botón "+ Nueva Especialidad".</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($especialidades->hasPages())
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $especialidades->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================
     MODALES DEL MÓDULO DE GESTIÓN
     =================================================================== -->

{{-- Modal 1: Nuevo Usuario (CRUD - Create) --}}
@can('crear-usuarios')
<div class="modal fade text-start" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #b7d8ee; border-bottom: 1px solid #a3c8e0;">
                <h5 class="modal-title fw-bold" style="color: #0b4f79;">
                    ➕ Registrar Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Dr. Carlos Mendoza" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="usuario@clinica.com" required>
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
                        <select name="rol" id="nuevoUsuarioRolSelect" class="form-select" required onchange="toggleEspecialidadesNuevoUsuario(this.value)">
                            <option value="" disabled selected>Seleccione un rol...</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->name }}">{{ ucfirst($rol->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Especialidades (visibles si el rol es doctor, admin o Super_admin) --}}
                    <div class="mb-3" id="seccionEspecialidadesNuevoUsuario" style="display: none;">
                        <label class="form-label fw-bold text-dark">Especialidades Clínicas (Opcional)</label>
                        <div class="border rounded p-3 bg-light" style="max-height: 140px; overflow-y: auto;">
                            @forelse($todasEspecialidades as $esp)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="especialidades[]" value="{{ $esp->id }}" id="nuevoEspCheck{{ $esp->id }}">
                                    <label class="form-check-label d-flex align-items-center" for="nuevoEspCheck{{ $esp->id }}">
                                        <span class="color-dot me-2" style="background-color: {{ $esp->color }};"></span>
                                        {{ $esp->nombre }}
                                    </label>
                                </div>
                            @empty
                                <small class="text-muted">No hay especialidades creadas aún.</small>
                            @endforelse
                        </div>
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

{{-- Modales Editar Usuarios (CRUD - Update) --}}
@can('editar-usuarios')
    @foreach($usuarios as $u)
    <div class="modal fade text-start" id="modalEditar{{ $u->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header" style="background: #b7d8ee; border-bottom: 1px solid #a3c8e0;">
                    <h5 class="modal-title fw-bold" style="color: #0b4f79;">
                        🛠️ Editar Usuario: {{ $u->nombre }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                            <label class="form-label fw-bold text-dark">Teléfono</label>
                            <input type="text" name="tel" class="form-control" value="{{ old('tel', $u->tel) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Nueva Contraseña (Opcional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                            <small class="text-muted" style="font-size: 11px;">Solo llene este campo si desea cambiar la clave.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Rol del Usuario</label>
                            <select name="rol" class="form-select" required onchange="toggleEspecialidadesEditar('{{ $u->id }}', this.value)">
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->name }}" {{ $u->hasRole($rol->name) ? 'selected' : '' }}>
                                        {{ ucfirst($rol->name) }}
                                    </option>
                                @endforeach
                                @if($u->hasRole('Super_admin') || auth()->user()->hasRole('Super_admin'))
                                    <option value="Super_admin" {{ $u->hasRole('Super_admin') ? 'selected' : '' }}>
                                        Super_admin
                                    </option>
                                @endif
                            </select>
                        </div>

                        {{-- Selección de especialidades si es doctor, admin o Super_admin --}}
                        <div class="mb-3" id="seccionEspecialidadesEdit{{ $u->id }}" style="{{ $u->hasAnyRole(['doctor', 'admin', 'Super_admin']) ? '' : 'display: none;' }}">
                            <label class="form-label fw-bold text-dark">Especialidades Asignadas</label>
                            <div class="border rounded p-3 bg-light" style="max-height: 150px; overflow-y: auto;">
                                @php
                                    $userEspIds = $u->especialidades->pluck('id')->toArray();
                                @endphp
                                @forelse($todasEspecialidades as $esp)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="especialidades[]"
                                               value="{{ $esp->id }}" id="uEdit{{ $u->id }}esp{{ $esp->id }}"
                                               {{ in_array($esp->id, $userEspIds) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex align-items-center" for="uEdit{{ $u->id }}esp{{ $esp->id }}">
                                            <span class="color-dot me-2" style="background-color: {{ $esp->color }};"></span>
                                            {{ $esp->nombre }}
                                        </label>
                                    </div>
                                @empty
                                    <small class="text-muted">No hay especialidades disponibles.</small>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Volver</button>
                        <button type="submit" class="btn btn-primary px-4" style="background: #0b4f79; border-color: #0b4f79;">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Específico para Asignar Especialidades a este usuario --}}
    @if($u->hasAnyRole(['doctor', 'admin', 'Super_admin']))
    <div class="modal fade text-start" id="modalEspecialidadesUsuario{{ $u->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header" style="background: #c7e6f8; border-bottom: 1px solid #a8d5f1;">
                    <h5 class="modal-title fw-bold" style="color: #0b4f79;">
                        🩺 Especialidades: {{ $u->nombre }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('admin.usuarios.especialidades', $u->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Marque las especialidades que el usuario puede atender en citas:</p>
                        <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            @php
                                $uEspIds = $u->especialidades->pluck('id')->toArray();
                            @endphp
                            @forelse($todasEspecialidades as $esp)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="especialidades[]"
                                           value="{{ $esp->id }}" id="uQuick{{ $u->id }}esp{{ $esp->id }}"
                                           {{ in_array($esp->id, $uEspIds) ? 'checked' : '' }}>
                                    <label class="form-check-label d-flex align-items-center" for="uQuick{{ $u->id }}esp{{ $esp->id }}">
                                        <span class="color-dot me-2" style="background-color: {{ $esp->color }};"></span>
                                        <strong>{{ $esp->nombre }}</strong>
                                    </label>
                                </div>
                            @empty
                                <small class="text-muted">No hay especialidades registradas en el sistema.</small>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4" style="background: #0b4f79; border-color: #0b4f79;">
                            Guardar Especialidades
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endforeach
@endcan

{{-- Modal 3: Nueva Especialidad --}}
@hasanyrole('admin|Super_admin')
<div class="modal fade text-start" id="modalNuevaEspecialidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #fbe6c4; border-bottom: 1px solid #ebd0a4;">
                <h5 class="modal-title fw-bold" style="color: #92570a;">
                    🦷 Registrar Nueva Especialidad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('especialidades.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nombre de la Especialidad</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Ortodoncia, Endodoncia..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Color Identificador</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" name="color" class="form-control form-control-color" value="#0b4f79" style="width: 50px; height: 38px; cursor: pointer;">
                            <small class="text-muted">Color para badges de citas y reportes de la especialidad.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4" style="background: #dfa72c; border-color: #dfa72c; color: white;">
                        Crear Especialidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modales Editar Especialidad --}}
@foreach($todasEspecialidades as $esp)
<div class="modal fade text-start" id="modalEditarEspecialidad{{ $esp->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #fbe6c4; border-bottom: 1px solid #ebd0a4;">
                <h5 class="modal-title fw-bold" style="color: #92570a;">
                    ✏️ Editar Especialidad: {{ $esp->nombre }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('especialidades.update', $esp->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nombre de la Especialidad</label>
                        <input type="text" name="nombre" class="form-control" value="{{ $esp->nombre }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Color Identificador</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" name="color" class="form-control form-control-color" value="{{ $esp->color ?? '#0b4f79' }}" style="width: 50px; height: 38px; cursor: pointer;">
                            <small class="text-muted">Color distintivo de la especialidad.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4" style="background: #dfa72c; border-color: #dfa72c; color: white;">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endhasanyrole

<script>
function toggleEspecialidadesNuevoUsuario(rol) {
    const seccion = document.getElementById('seccionEspecialidadesNuevoUsuario');
    if (!seccion) return;
    if (['doctor', 'admin', 'Super_admin'].includes(rol)) {
        seccion.style.display = 'block';
    } else {
        seccion.style.display = 'none';
    }
}

function toggleEspecialidadesEditar(userId, rol) {
    const seccion = document.getElementById('seccionEspecialidadesEdit' + userId);
    if (!seccion) return;
    if (['doctor', 'admin', 'Super_admin'].includes(rol)) {
        seccion.style.display = 'block';
    } else {
        seccion.style.display = 'none';
    }
}

// Búsqueda en vivo (filtro interactivo instantáneo mientras escribe)
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar = document.getElementById('inputBuscarUsuario');
    const filas = document.querySelectorAll('.fila-usuario');

    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', function () {
            const query = this.value.trim().toLowerCase();
            filas.forEach(fila => {
                const nombre = fila.getAttribute('data-nombre') || '';
                const email = fila.getAttribute('data-email') || '';
                const rol = fila.getAttribute('data-rol') || '';

                if (nombre.includes(query) || email.includes(query) || rol.includes(query)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});
</script>

@endsection
