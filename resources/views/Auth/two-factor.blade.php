@extends('layouts.insole_login')
@section('title', 'Verificación 2FA')

@push('styles')
    @vite('resources/css/login/two-factor.css')
@endpush

@section('content')
<div class="login-container">
    <div class="two-factor-card">

        <!-- Icono de Seguridad -->
        <div class="two-factor-icon-circle">
            <svg viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.4 0 2.5 1.1 2.5 2.5V11h1c.55 0 1 .45 1 1v5c0 .55-.45 1-1 1H8.5c-.55 0-1-.45-1-1v-5c0-.55.45-1 1-1h1V9.5C9.5 8.1 10.6 7 12 7zm0 1.5c-.55 0-1 .45-1 1V11h2V9.5c0-.55-.45-1-1-1z" />
            </svg>
        </div>

        <h2 class="login-title">Verificación 2FA</h2>
        <p class="login-subtitle mb-2">Ingresa el código de 6 dígitos que enviamos a tu correo:</p>

        <div class="user-email-badge">
            ✉️ {{ $user->email }}
        </div>

        {{-- Alertas de estado --}}
        @if (session('status'))
            <div class="alert alert-success py-2 px-3 mb-3 text-start" style="font-size: 13.5px;" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info py-2 px-3 mb-3 text-start" style="font-size: 13.5px;" role="alert">
                ℹ️ {{ session('info') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3 text-start" style="font-size: 13.5px;" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario para verificar código --}}
        <form method="POST" action="{{ route('2fa.verify.post') }}" id="verifyForm">
            @csrf

            <div class="code-input-container">
                <label for="code" class="form-label d-block text-muted mb-2" style="font-size: 13px;">
                    Código de 6 dígitos:
                </label>
                <input type="text"
                       name="code"
                       id="code"
                       class="form-control code-input @error('code') is-invalid @enderror"
                       placeholder="••••••"
                       maxlength="6"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       autocomplete="one-time-code"
                       value="{{ old('code') }}"
                       required
                       autofocus>
            </div>

            <div class="form-check text-start mb-3 mt-3" style="font-size: 13px;">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1"
                    {{ session('2fa:remember') ? 'checked' : '' }}>
                <label class="form-check-label text-muted" for="remember">
                    Recordarme en este dispositivo
                </label>
            </div>

            <button type="submit" class="btn btn-verify-2fa">
                Verificar e Ingresar
            </button>
        </form>

        {{-- Reenviar código y temporizador --}}
        @php
            $lifetimeMinutes = \App\Models\User::getTwoFactorLifetimeMinutes();
            $minText = $lifetimeMinutes === 1 ? '1 minuto' : "{$lifetimeMinutes} minutos";
        @endphp
        <div class="resend-box mt-4 pt-3 border-top">
            <p class="timer-box mb-2">
                El código es válido durante <strong>{{ $minText }}</strong>.
                <span id="timerContainer" class="d-block mt-1">
                    @if (($remainingSeconds ?? 0) > 0)
                        Puedes solicitar otro código en: <span class="time-remaining" id="timerDisplay">--:--</span>
                    @else
                        <span class="text-success fw-bold">✓ Ya puedes solicitar un nuevo código.</span>
                    @endif
                </span>
            </p>

            <form method="POST" action="{{ route('2fa.resend') }}" id="resendForm">
                @csrf
                <button type="submit" id="btnResend" class="btn-resend">
                    ¿No recibiste el código? Reenviar código
                </button>
            </form>
        </div>

        <div class="mt-4 pt-2">
            <a href="{{ route('login') }}" class="volver-link text-decoration-none" style="font-size: 13.5px;">
                ← Volver a iniciar sesión
            </a>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const codeInput = document.getElementById('code');
        const timerDisplay = document.getElementById('timerDisplay');
        const timerContainer = document.getElementById('timerContainer');
        const btnResend = document.getElementById('btnResend');

        // Permitir solo números en el input de código
        if (codeInput) {
            codeInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
        }

        // Temporizador visual basado en reloj monotónico (performance.now())
        // 1. Inmune a alteraciones del reloj de Windows/sistema operativo en el cliente.
        // 2. Mantiene la medición exacta incluso si la pestaña está en segundo plano (abriendo Gmail).
        const initialSeconds = parseInt("{{ $remainingSeconds ?? 0 }}", 10);
        const startPerf = performance.now();
        let timerActive = initialSeconds > 0;

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        function getRemainingSeconds() {
            const elapsedSeconds = (performance.now() - startPerf) / 1000;
            return Math.max(0, Math.ceil(initialSeconds - elapsedSeconds));
        }

        function tick() {
            if (!timerActive) return;

            const remaining = getRemainingSeconds();

            if (remaining > 0) {
                if (timerDisplay) {
                    timerDisplay.textContent = formatTime(remaining);
                }
                setTimeout(tick, 500);
            } else {
                timerActive = false;
                if (timerDisplay) {
                    timerDisplay.textContent = '00:00';
                }
                if (timerContainer) {
                    timerContainer.innerHTML = '<span class="text-success fw-bold">✓ El código ha expirado. Ya puedes solicitar uno nuevo.</span>';
                }
                if (btnResend) {
                    btnResend.removeAttribute('disabled');
                    btnResend.style.opacity = '1';
                }
            }
        }

        // Si el usuario cambia de pestaña a su Gmail y luego regresa, sincronizar inmediatamente
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible' && timerActive) {
                tick();
            }
        });

        window.addEventListener('focus', function () {
            if (timerActive) {
                tick();
            }
        });

        if (initialSeconds > 0) {
            tick();
        } else if (btnResend) {
            btnResend.removeAttribute('disabled');
        }
    });
</script>
@endsection
