@extends('layouts.nav-bar')
@section('title', 'Perfil')

@section('content')
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
        <div class="text-center mt-4">
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="{{ route('perfil.edit') }}" class="btn btn-gold btn-save w-100 mb-2 mb-sm-0">
                    Editar
                </a>

                <a href="#"
                    class="btn-2fa btn btn-outline-secondary btn-cancel w-100"
                    title="Seguridad 2FA">
                    🔐2FA
                </a>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection