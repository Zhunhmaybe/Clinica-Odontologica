@extends('layouts.nav-bar')
@section('title', 'Perfil')

@section('content')
<h3 class="fw-bold mb-4">Mi perfil</h3>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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

        <div class="text-center mt-4">
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                @can('editar-Perfil')
                <a href="{{ route('perfil.edit') }}" class="btn btn-gold btn-save w-100 mb-2 mb-sm-0">
                    Editar
                </a>
                @endcan

                @can('2FA')
                <button type="button"
                    class="btn-2fa btn btn-outline-secondary btn-cancel w-100 d-flex align-items-center justify-content-center gap-2"
                    data-bs-toggle="modal"
                    data-bs-target="#modal2FA"
                    title="Configuración de Doble Factor de Autenticación">
                    <span>🔐 2FA</span>
                    @if (Auth::user()->two_factor_enabled)
                        <span class="badge bg-success" style="font-size: 11px;">Activo</span>
                    @else
                        <span class="badge bg-secondary" style="font-size: 11px;">Inactivo</span>
                    @endif
                </button>
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- Modal 2FA --}}
@can('2FA')
<div class="modal fade" id="modal2FA" tabindex="-1" aria-labelledby="modal2FALabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1a5490 0%, #2c7fb8 100%);">
                <h5 class="modal-title fw-bold" id="modal2FALabel">
                    <i class="fas fa-shield-alt me-2 text-warning"></i>Autenticación de Dos Factores (2FA)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 text-center">
                @if (Auth::user()->two_factor_enabled)
                    <div class="mb-3">
                        <span class="badge bg-success px-3 py-2 fs-6 rounded-pill">
                            <i class="fas fa-check-circle me-1"></i> Estado: Habilitado en Base de Datos
                        </span>
                    </div>
                    <p class="text-muted mb-3" style="font-size: 14.5px;">
                        Tu cuenta está actualmente protegida. Cada vez que inicies sesión se solicitará un código de 6 dígitos que será enviado a tu correo:
                    </p>
                    <div class="alert alert-light border py-2 mb-4">
                        <strong class="text-primary">{{ Auth::user()->email }}</strong>
                    </div>

                    <form method="POST" action="{{ route('profile.2fa.disable') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100 py-2 rounded-pill fw-semibold">
                            <i class="fas fa-shield-slash me-2"></i>Desactivar Doble Factor
                        </button>
                    </form>

                    @php
                        $sesionesActivas = \Illuminate\Support\Facades\DB::table('sessions')
                            ->where('user_id', Auth::id())
                            ->orderByDesc('last_activity')
                            ->get();
                    @endphp
                    @if ($sesionesActivas->count() > 0)
                        <div class="mt-4 pt-3 border-top text-start">
                            <h6 class="fw-bold mb-2 text-dark" style="font-size: 13px;">
                                <i class="fas fa-laptop me-1 text-primary"></i> Sesiones y Dispositivos en BDD ({{ $sesionesActivas->count() }}):
                            </h6>
                            <ul class="list-group list-group-flush mb-3" style="font-size: 12px;">
                                @foreach ($sesionesActivas as $ses)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2 bg-light rounded mb-1">
                                        <div>
                                            <strong>{{ $ses->dispositivo ?? 'Dispositivo' }}</strong>
                                            <div class="text-muted" style="font-size: 11px;">
                                                IP: <code>{{ $ses->ip_address }}</code> &bull; {{ $ses->ubicacion ?? 'Desconocida' }}
                                            </div>
                                        </div>
                                        <span class="badge bg-primary" style="font-size: 10px;">En BDD</span>
                                    </li>
                                @endforeach
                            </ul>

                            <form method="POST" action="{{ route('profile.2fa.forget-devices') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100 rounded-pill">
                                    <i class="fas fa-trash-alt me-1"></i> Limpiar sesiones recordadas
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="mb-3">
                        <span class="badge bg-secondary px-3 py-2 fs-6 rounded-pill">
                            <i class="fas fa-shield-alt me-1"></i> Estado: Deshabilitado
                        </span>
                    </div>
                    <p class="text-muted mb-3" style="font-size: 14.5px;">
                        Aumenta la seguridad de tu cuenta activando el doble factor de autenticación. Al activar, cada inicio de sesión requerirá un código de 6 dígitos con validez de 5 minutos enviado a:
                    </p>
                    <div class="alert alert-light border py-2 mb-4">
                        <strong class="text-primary">{{ Auth::user()->email }}</strong>
                    </div>

                    <form method="POST" action="{{ route('profile.2fa.enable') }}">
                        @csrf
                        <button type="submit" class="btn w-100 py-2 rounded-pill fw-semibold text-white shadow-sm" style="background: linear-gradient(135deg, #1a5490 0%, #2c7fb8 100%);">
                            <i class="fas fa-check me-2"></i>Activar Doble Factor
                        </button>
                    </form>
                @endif
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection