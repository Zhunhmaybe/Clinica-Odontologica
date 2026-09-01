<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citas;
use App\Models\Especialidades;
use App\Models\User;

class CitasController extends Controller
{
    public function citasIndex()
    {
        $citas = Citas::with(['paciente', 'doctor', 'especialidad'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('components.citas.create', [
            'citas' => $citas,
            'paciente' => null,
            'doctores' => User::role('doctor')->get(),
            'especialidades' => Especialidades::all()
        ]);
    }

    public function citasStore(Request $request)
    {
        // Validar y guardar la nueva cita
        $request->validate([
            'doctor_id'       => 'required|exists:usuarios,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'paciente_id'     => 'nullable'
        ]);

        Citas::create([
            'doctor_id'       => $request->doctor_id,
            'especialidad_id' => $request->especialidad_id,
            'paciente_id'     => $request->paciente_id,
            'fecha_inicio'    => now(),
            'estado'          => 'pendiente',
        ]);

        return redirect()
            ->route('citas.index')
            ->with('success', 'Cita registrada correctamente.');
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
