@props(['name', 'label' => 'Contraseña', 'id' => null, 'required' => false])

@php
    $inputId = $id ?? $name;
@endphp

<div class="mb-3 position-relative">
    <label for="{{ $inputId }}" class="form-label">{{ $label }}</label>
    
    <div class="input-group">
        <input 
            type="password" 
            name="{{ $name }}" 
            id="{{ $inputId }}"
            class="form-control @error($name) is-invalid @enderror"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
        
        <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" style="border-color: #dee2e6;">
            <!-- Icono de Ojo (Cerrado por defecto) -->
            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
            </svg>
            <!-- Icono de Ojo Tachado (Oculto por defecto) -->
            <svg class="eye-slash-icon d-none" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7.029 7.029 0 0 0 2.79-.588zM5.21 3.088A7.028 7.028 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474L5.21 3.089z"/>
                <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829l-2.83-2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12-.708.708z"/>
            </svg>
        </button>
        
        @error($name)
            <div class="invalid-feedback d-block w-100 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Buscamos todos los botones dentro de nuestro componente
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            // Evitamos añadir eventos múltiples si la página se recarga parcialmente
            if (button.dataset.listenerAdded) return;
            button.dataset.listenerAdded = 'true';

            button.addEventListener('click', function(e) {
                // Prevenimos que el botón envíe el formulario si está dentro de uno
                e.preventDefault();
                
                // Encontramos el input que está justo antes del botón en el DOM
                const input = this.previousElementSibling;
                const eyeIcon = this.querySelector('.eye-icon');
                const eyeSlashIcon = this.querySelector('.eye-slash-icon');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    eyeIcon.classList.add('d-none');
                    eyeSlashIcon.classList.remove('d-none');
                } else {
                    input.type = 'password';
                    eyeIcon.classList.remove('d-none');
                    eyeSlashIcon.classList.add('d-none');
                }
            });
        });
    });
</script>
