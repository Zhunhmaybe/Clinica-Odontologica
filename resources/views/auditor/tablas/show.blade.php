@extends('layouts.nav-bar')
@section('title', 'Auditoría - Tabla ' . ucfirst($tabla))

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-shield-alt text-primary me-2"></i>Auditoría: Tabla {{ ucfirst(str_replace('_', ' ', $tabla)) }}
            </h3>
            <p class="text-muted mb-0">Visualizando registros y eventos de auditoría del sistema</p>
        </div>
        <a href="{{ route('auditor.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Volver al Panel
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            @if ($registros->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                    <h5>No hay registros disponibles en esta tabla.</h5>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light">
                            <tr>
                                @foreach ((array) $registros->first() as $col => $val)
                                    <th class="py-3 px-3 text-nowrap">
                                        {{ ucfirst(str_replace('_', ' ', $col)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($registros as $row)
                                <tr>
                                    @foreach ((array) $row as $col => $val)
                                        <td class="px-3 py-2 text-nowrap">
                                            @if (is_null($val))
                                                <span class="text-muted fst-italic">null</span>
                                            @elseif ($col === 'accion')
                                                <span class="badge bg-primary text-white py-1 px-2 rounded-pill">
                                                    {{ $val }}
                                                </span>
                                            @elseif ($col === 'ip_address')
                                                <code>{{ $val }}</code>
                                            @elseif (in_array($col, ['valores_anteriores', 'valores_nuevos']))
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ is_string($val) ? $val : json_encode($val) }}">
                                                    {{ is_string($val) ? $val : json_encode($val) }}
                                                </span>
                                            @else
                                                {{ is_string($val) ? Str::limit($val, 40) : $val }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($registros->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-center">
                            {{ $registros->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
