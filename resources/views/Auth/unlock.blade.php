@extends('layouts.insole_login')
@section('title','Desbloquear cuenta')
@push('styles')
    @vite('resources/css/login/unlock.css')
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">

                        <h2 class="text-center mb-3 text-danger">
                            Cuenta Bloqueada
                        </h2>

                        <p class="text-center text-muted mb-4">
                            Por tu seguridad, tu cuenta fue bloqueada tras varios
                            intentos fallidos.
                            Ingresa el código que enviamos a tu correo.
                        </p>

                        {{-- Mensaje de éxito --}}
                        @if (session('status'))
                        <div class="alert alert-success text-center">
                            {{ session('status') }}
                        </div>
                        @endif

                        {{-- Errores --}}
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('lock.verify') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Ingresa tu correo"
                                    value="{{ old('email') }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Código de desbloqueo</label>
                                <input type="text"
                                    name="code"
                                    class="form-control"
                                    placeholder="Código recibido por correo"
                                    required>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-danger">
                                    Desbloquear cuenta
                                </button>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('login') }}">
                                    Volver a iniciar sesión
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection