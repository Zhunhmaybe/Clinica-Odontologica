@extends('layouts.insole_login')
@section('title','Recuperar contraseña')
@push('styles')
    @vite('resources/css/login/resetpassword.css')
@endpush

@section('content')
    <div class="recover-container">
        <div class="recover-card">

            <h2 class="text-center mb-4">Recuperar contraseña</h2>

            {{-- exito --}}
            @if (session('status'))
                <div class="alert alert-success text-center">
                    {{ session('status') }}
                </div>
            @endif

            {{-- errores --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="usuario@ejemplo.com"
                        required>
                </div>

                <button type="submit" class="btn btn-recover mb-3">
                    Enviar enlace
                </button>
            </form>

            <div class="text-center">
                <a href="{{ route('login') }}" class="link-login">
                    Volver a iniciar sesión
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection

