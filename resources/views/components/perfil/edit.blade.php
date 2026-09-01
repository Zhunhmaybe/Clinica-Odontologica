@extends('layouts.nav-bar')
@section('title', 'Editar Perfil')

@section('content')
<h3 class="fw-bold mb-4">Editar Perfil</h3>

<div class="profile-card">

    <div class="profile-header">
        <div class="avatar"></div>
        <div>
            <h4>{{ Auth::user()->nombre }}</h4>
            <small>{{ Auth::user()->nombre_rol }}</small>
        </div>
    </div>

    <div class="profile-body">

        {{-- ERRORES --}}
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('perfil.update') }}">
            @csrf
            @method('PUT')

            <h5 class="fw-bold mb-4">Información Personal</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text"
                        name="nombre"
                        class="form-control"
                        value="{{ old('nombre', Auth::user()->nombre) }}"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Correo Electrónico</label>
                    <input class="form-control"
                        name="email"
                        value="{{ Auth::user()->email }}"
                        readonly>

                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text"
                        name="tel"
                        class="form-control"
                        maxlength="10"
                        value="{{ old('tel', Auth::user()->tel) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="1" {{ Auth::user()->estado == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="2" {{ Auth::user()->estado == 2 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4">
                <button type="submit" class="btn-save w-100 mb-2 mb-sm-0">Guardar Cambio</button>
                <a href="{{ route('perfil.index') }}" class="btn btn-outline-secondary btn-cancel w-100 text-center">
                    Cancelar
                </a>
            </div>

        </form>
    </div>

</div>
@endsection