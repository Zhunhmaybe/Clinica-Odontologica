@extends('layouts.insole_login')

@section('title','Registrarse')
@push('styles')
    @vite('resources/css/login/register.css')
@endpush

@section('content')
    <div class="register-container">
        <div class="register-card">

            <div class="icon-circle">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10
                         10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0
                         3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3
                         1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22
                         .03-1.99 4-3.08 6-3.08 1.99 0 5.97
                         1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                </svg>
            </div>

            <h2 class="register-title">Crear Cuenta</h2>
            <p class="register-subtitle">Únete a nuestro sistema</p>

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            
            <form method="POST" action="{{ url('/register') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text"
                        name="nombre"
                        value="{{ old('nombre') }}"
                        class="form-control @error('nombre') is-invalid @enderror"
                        required autofocus>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Teléfono (opcional)</label>
                    <input type="text"
                        name="tel"
                        maxlength="10"
                        value="{{ old('tel') }}"
                        class="form-control @error('tel') is-invalid @enderror">
                    @error('tel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input type="password"
                        name="password_confirmation"
                        class="form-control"
                        required>
                </div>



                <button type="submit" class="btn btn-register">Registrarse</button>

                <div class="text-center mt-3">
                    ¿Ya tienes cuenta?
                    <a href="{{ url('/login') }}" class="link-login">Inicia Sesión</a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection