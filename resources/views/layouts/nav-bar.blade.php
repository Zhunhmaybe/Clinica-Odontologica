<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Home')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
    @vite(['resources/css/navbar/editar-perfil.css'])
</head>

<body>
    <div class="wrapper">
        <aside class="sidebar">

            <!-- Encabezado del Sidebar (Logo + Hamburguesa en Móvil) -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="logo mb-0 pb-0 border-0">
                    <img src="/images/logo-danny.png" alt="Logo Danny" style="max-height: 60px; width: auto;">
                </div>
                <!-- Botón Hamburguesa (solo visible en móviles) -->
                <button class="btn btn-outline-light d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse" aria-expanded="false" aria-controls="sidebarCollapse">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Contenido del Sidebar Colapsable -->
            <div class="collapse d-lg-flex flex-column flex-grow-1" id="sidebarCollapse">

                {{-- Boton de perfil para todos --}}
                @can('ver-Perfil')
                <a href="{{ route('perfil.index') }}"
                    class="nav-link {{ request()->routeIs('perfil.index') ? 'active' : '' }}">
                    <i class="fas fa-user-md"></i> Mi Perfil
                </a>
                @endcan

                @can('ver-citas')
                <a href="{{ route('citas.index') }}"
                    class="nav-link {{ request()->routeIs('citas.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Citas
                </a>
                @endcan

                @can('ver-pacientes')
                <a href="{{ route('pacientes.index') }}"
                    class="nav-link {{ request()->routeIs('pacientes.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Pacientes
                </a>
                @endcan

                @can('ver-historia')
                <a href="{{ route('historia_clinica.index') }}"
                    class="nav-link {{ request()->routeIs('historia_clinica.*') ? 'active' : '' }}">
                    <i class="fas fa-notes-medical"></i> Historia Clínica
                </a>
                @endcan

                @hasanyrole('admin|Super_admin')
                <a href="{{ route('admin.index') }}"
                    class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Panel Admin
                </a>
                @endhasanyrole

                @can('ver-usuarios')
                <a href="{{ route('admin.usuarios.index') }}"
                    class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i> Gestión Usuarios
                </a>
                @endcan

                @can('ver-auditoria')
                <a href="{{ route('auditor.index') }}"
                    class="nav-link {{ request()->routeIs('auditor.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i> Auditoría
                </a>
                @endcan

                {{--Cerrar sesion--}}
                <div class="user mt-auto">
                    <strong>{{ Auth::user()->nombre}}</strong><br>
                    <small> {{ Auth::user()->nombre_rol}}</small>

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button class="btn btn-sm btn-light w-100">Cerrar Sesión</button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Botones y accesos con los permisos --}}
        {{-- Perfil --}}
        <main class="content w-100">
            @yield('content')
        </main>
    </div>

    <!-- Script de Bootstrap JS necesario para el menú colapsable -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>