<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Plantilla')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
    @vite(['resources/css/navbar/editar-perfil.css'])
</head>

<body>
    <div class="wrapper">
        <aside class="sidebar">

            <div class="logo">
                <img src="/images/logo-danny.png" alt="Logo Danny">
            </div>

            @php
                /** @var \App\Models\User $user */
                $user = Auth::user();

                // Determinar la ruta exacta dependiendo del rol
                $rutaPerfil = match(true) {
                $user->hasRole('doctor') => 'usuario.index',
                $user->hasRole('recepcionista') => 'usuario.index',
                $user->hasRole('auditor') => 'usuario.index',
                $user->hasRole('admin') => 'usuario.index',
                default => 'usuario.index',
                };

                //Determinar la ruta exacta para citas 
            @endphp

            {{-- Boton de perfil para 0-1-2-3-4 --}}
            @can('ver-Perfil')
            <a href="{{ route($rutaPerfil) }}"
                class="nav-link {{ request()->routeIs($rutaPerfil) ? 'active' : '' }}">
                <i class="fas fa-user-md"></i> Mi Perfil
            </a>
            @endcan

            @can('ver-citas')
            @endcan

            {{--Cerrar sesion--}}
            <div class="user">
                <strong>{{ Auth::user()->nombre}}</strong><br>
                <small> {{ Auth::user()->nombre_rol}}</small>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button class="btn btn-sm btn-light w-100">Cerrar Sesión</button>
                </form>
            </div>
        </aside>

        {{-- Botones y accesos con los permisos --}}
        {{-- Perfil --}}
        <main class="content">

            <h3 class="fw-bold mb-4">Mi perfil</h3>
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar"></div>
                    <div>
                        <h4>{{ Auth::user()->nombre }}</h4>
                        <small>{{Auth::user()->nombre_rol}}</small>
                    </div>
                </div>

                <div class="profile-body">
                    <h5>Información Personal:</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Completo:</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->nombre }}" disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" value="{{ Auth::user()->email }}" disabled>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono:</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->tel ?? 'No registrado' }}"
                                disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado:</label>
                            <input type="text" class="form-control"
                                value="{{ Auth::user()->nombre_estado ?? 'No registrada' }}" disabled>
                        </div>
                    </div>

                    @can('2FA')
                        <div class="text-center">
                            <div class="action-buttons">
                                <a href="#" class="btn btn-gold">
                                    Editar
                                </a>
                                <a href="#"
                                    class="btn-2fa"
                                    title="Seguridad 2FA">
                                    🔐2FA
                                </a>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
            
            @yield('content')

        </main>
    </div>
</body>

</html>