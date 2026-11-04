<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citas;
use App\Models\Especialidades;
use App\Models\User;

use App\Models\Paciente;
use App\Models\Auditoria;

class CitasController extends Controller
{
    public function citasIndex(Request $request)
    {
        $citas = Citas::with(['paciente', 'doctor', 'especialidad'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $paciente = null;
        if ($request->filled('paciente_id')) {
            $paciente = Paciente::find($request->paciente_id);
        } elseif ($request->filled('cedula')) {
            $paciente = Paciente::where('cedula', trim($request->cedula))->first();
        }

        return view('components.citas.create', [
            'citas'          => $citas,
            'paciente'       => $paciente,
            'doctores'       => User::role(['doctor', 'admin', 'Super_admin'])->where('estado', 1)->with('especialidades')->get(),
            'especialidades' => Especialidades::all()
        ]);
    }

    /**
     * Búsqueda en tiempo real de pacientes por cédula o nombre
     */
    public function buscarPacientes(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (empty($q)) {
            return response()->json([]);
        }

        $queryLower = '%' . strtolower($q) . '%';
        $pacientes = Paciente::where('cedula', 'like', "%{$q}%")
            ->orWhereRaw('LOWER(nombres) LIKE ?', [$queryLower])
            ->orWhereRaw('LOWER(apellidos) LIKE ?', [$queryLower])
            ->take(10)
            ->get();

        $resultados = $pacientes->map(function ($p) {
            return [
                'id'              => $p->id,
                'cedula'          => $p->cedula,
                'nombre_completo' => trim("{$p->nombres} {$p->apellidos}"),
                'nombres'         => $p->nombres,
                'apellidos'       => $p->apellidos,
                'telefono'        => $p->telefono ?? '',
                'email'           => $p->email ?? '',
            ];
        });

        return response()->json($resultados);
    }

    public function citasStore(Request $request)
    {
        // Validar y guardar la nueva cita
        $request->validate([
            'paciente_id'     => 'required|exists:pacientes,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'doctor_id'       => 'required|exists:usuarios,id',
            'fecha_inicio'    => ['required', 'date', 'after:now'],
            'motivo'          => ['nullable', 'string', 'max:500', 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s]+$/u']
        ], [
            'paciente_id.required'     => 'Debe buscar y seleccionar un paciente para agendar la cita.',
            'paciente_id.exists'       => 'El paciente seleccionado no es válido.',
            'especialidad_id.required' => 'Debe seleccionar una especialidad.',
            'doctor_id.required'       => 'Debe asignar un doctor a la cita.',
            'fecha_inicio.required'    => 'Debe seleccionar la fecha y hora de inicio de la cita.',
            'fecha_inicio.after'       => 'La fecha y hora de la cita debe ser posterior a la fecha y hora actual.',
            'motivo.regex'             => 'El motivo de la consulta no puede contener caracteres especiales, solo letras y números.',
            'motivo.max'               => 'El motivo de la consulta no puede superar los 500 caracteres.',
        ]);

        $cita = Citas::create([
            'paciente_id'     => $request->paciente_id,
            'doctor_id'       => $request->doctor_id,
            'especialidad_id' => $request->especialidad_id,
            'fecha_inicio'    => $request->fecha_inicio,
            'estado'          => 'pendiente',
            'motivo'          => $request->motivo,
        ]);

        Auditoria::registrar(
            accion: 'Registro de nueva cita',
            tabla: 'citas',
            registroId: (string) $cita->id,
            nuevos: [
                'paciente_id'     => $cita->paciente_id,
                'doctor_id'       => $cita->doctor_id,
                'especialidad_id' => $cita->especialidad_id,
                'fecha_inicio'    => $cita->fecha_inicio,
                'motivo'          => $cita->motivo,
            ],
            request: $request
        );

        return redirect()
            ->route('citas.index')
            ->with('success', 'Cita registrada y agendada correctamente.');
    }

    public function citasUpdate(Request $request, Citas $cita)
    {
        $request->validate([
            'doctor_id'       => 'required|exists:usuarios,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'fecha_inicio'    => 'required|date',
            'estado'          => 'required|in:pendiente,confirmada,cancelada',
            'motivo'          => 'nullable|string|max:255',
        ]);

        $antes = $cita->toArray();

        $cita->update([
            'doctor_id'       => $request->doctor_id,
            'especialidad_id' => $request->especialidad_id,
            'fecha_inicio'    => $request->fecha_inicio,
            'estado'          => $request->estado,
            'motivo'          => $request->motivo,
        ]);

        return redirect()
            ->route('citas.index')
            ->with('success', 'Cita actualizada correctamente.');
    }
}
