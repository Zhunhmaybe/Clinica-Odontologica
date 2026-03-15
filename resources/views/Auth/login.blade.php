@extends('layouts.insole_login')
@section('title','Ingresar')

<!--@push('styles')
@endpush-->

@section('content')
    <div class="login-container">
        <div class="login-card">

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

            <h2 class="login-title">Bienvenido</h2>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                        required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="link-text">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="btn btn-login">Iniciar sesión</button>

                <div class="text-center mt-3">
                    <span style="font-size: 13px;">¿No tienes cuenta?</span>
                    <a href="{{ route('register') }}" class="register-link">Regístrate</a>
                    <p></p>
                    <a href="{{ route('home') }}" class="volver-link">Volver</a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection