@props(['paciente' => null, 'especialidades' => collect(), 'doctores' => collect()])

@vite('resources/css/components/citas/create.css')

<h4 class="fw-bold mb-2">Registrar nueva cita</h4>
<p class="text-muted mb-4">Aquí puedes gestionar tus próximas citas.</p>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        ✅ {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel mb-4">
    <h5 class="fw-bold mb-3">Buscar Cliente</h5>
    <form method="GET" action="{{ route('secretaria.citas.create') }}" class="row g-3">
        <div class="col-md-8">
            <input type="text" name="cedula" maxlength="10" class="form-control"
                placeholder="Ingrese la cédula del cliente" value="{{ request('cedula') }}" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-gold w-100">Buscar</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('secretaria.pacientes.create') }}" class="btn btn-outline-secondary w-100">
                Crear
            </a>
        </div>
    </form>
    
    @if (session('paciente_no_encontrado'))
        <div class="alert alert-warning d-flex justify-content-between align-items-center mt-3">
            <div>❌ No existe ningún paciente con la cédula <strong>{{ session('paciente_no_encontrado') }}</strong></div>
            <a href="{{ route('secretaria.pacientes.create', ['cedula' => session('paciente_no_encontrado')]) }}" class="btn btn-gold">
                Crear Paciente
            </a>
        </div>
    @endif
</div>

<div class="panel">
    <form method="POST" action="{{ route('secretaria.citas.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-md-6">
                <span class="section-title">1. Datos del Paciente</span>
                <input type="hidden" name="paciente_id" value="{{ optional($paciente)->id }}">
                <div class="mt-3">
                    <label>Cédula / DNI</label>
                    <input class="form-control" value="{{ optional($paciente)->cedula }}" disabled>
                </div>
                </div>

            <div class="col-md-6">
                <span class="section-title">2. Detalles de la Cita</span>
                <div class="mt-3">
                    <label>Especialidad</label>
                    <select class="form-select" name="especialidad_id" required>
                        @foreach ($especialidades as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-3">
                    <label>Doctor Asignado</label>
                    <select class="form-select" name="doctor_id" required>
                        @foreach ($doctores as $d)
                            <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                </div>
        </div>
        <div class="text-end mt-4">
            <a href="{{ route('secretaria.citas.create') }}" class="btn btn-light">Cancelar</a>
            <button class="btn btn-gold ms-2">Confirmar y Agendar</button>
        </div>
    </form>
</div>