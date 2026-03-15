@extends('layouts.insole_login')
@section('title','Restablecer contraseña')

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">

                        <h2 class="text-center mb-4">Restablecer contraseña</h2>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Correo"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Nueva contraseña"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    placeholder="Confirmar contraseña"
                                    required>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    Restablecer contraseña
                                </button>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('login') }}">Volver a iniciar sesión</a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection