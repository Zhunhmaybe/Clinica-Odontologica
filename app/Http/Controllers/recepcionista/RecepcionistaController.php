<?php

namespace App\Http\Controllers\recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Paciente;
use App\Models\Citas;
use App\Models\Especialidades;
use App\Rules\ValidarCedulaEcuatoriana;
use Illuminate\Database\QueryException;

class RecepcionistaController extends Controller
{
    //Perfil
    public function editProfile()
    {
        $user = Auth::user();
        return view('recepcionista.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $antes = $user->toArray();
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email'  => 'required|email|max:100|unique:usuarios,email,' . $user->id,
            'tel'    => 'nullable|string|max:10',
        ]);

        $user->update([
            'nombre' => $request->nombre,
            'email'  => $request->email,
            'tel'    => $request->tel,
        ]);

        return redirect()
            ->route('recepcionista.home')
            ->with('success', 'Perfil actualizado correctamente');
    }

    //Pacientes
    public function pacientesIndex(Request $request)
    {
        $pacientes = Paciente::orderBy('nombres')->get();

        $pacienteSeleccionado = null;
        if ($request->has('paciente')) {
            $pacienteSeleccionado = Paciente::find($request->paciente);
        }

        return view('recepcionista.paciente.index', compact(
            'pacientes',
            'pacienteSeleccionado'
        ));
    }

    public function pacientesCreate()
    {
        return view('recepcionista.paciente.create');
    }

    public function pacientesStore(Request $request)
    {
        $request->validate([
            'cedula' => ['required', 'string', 'max:20', new ValidarCedulaEcuatoriana],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email' => 'nullable|email',
            'telefono' => 'required|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'nullable|string',
            'consentimiento_lopdp' => 'required|accepted',
        ]);

        try {
            $paciente = Paciente::create([
                'cedula' => $request->cedula,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'direccion' => $request->direccion,
                'consentimiento_lopdp' => true,
                'fecha_firma_lopdp' => now(),
            ]);

            return redirect()
                ->route('secretaria.pacientes.index')
                ->with('success', 'Paciente registrado correctamente');
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2] ?? 'Error desconocido';

            if (str_contains($errorMessage, 'no cumple la edad mínima')) {
                return back()
                    ->withErrors(['fecha_nacimiento' => 'El paciente debe tener al menos 1 año de edad.'])
                    ->withInput();
            }

            if (str_contains($errorMessage, 'fecha de nacimiento no puede ser futura')) {
                return back()
                    ->withErrors(['fecha_nacimiento' => 'La fecha no puede ser futura.'])
                    ->withInput();
            }

            if (str_contains($errorMessage, 'El correo electrónico') && str_contains($errorMessage, 'ya está registrado')) {
                return back()
                    ->withErrors(['email' => 'Este correo ya está registrado en el sistema.'])
                    ->withInput();
            }

            if (str_contains($errorMessage, 'pacientes_cedula_unique') || str_contains($errorMessage, 'cedula')) {
                return back()
                    ->withErrors(['cedula' => 'Esta cédula ya está registrada.'])
                    ->withInput();
            }

            return back()
                ->with('error', 'Error de base de datos: ' . $errorMessage)
                ->withInput();
        }
    }

    public function pacientesUpdate(Request $request, Paciente $paciente)
    {
        $antes = $paciente->toArray();

        $request->validate([
            'telefono' => 'required|string|max:10',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
        ]);

        try {
            $paciente->update([
                'telefono' => $request->telefono,
                'email' => $request->email,
                'direccion' => $request->direccion,
            ]);        

            return redirect()
                ->route('secretaria.pacientes.index')
                ->with('success', 'Paciente actualizado correctamente');
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2] ?? 'Error desconocido';

            if (str_contains($errorMessage, 'El correo electrónico') && str_contains($errorMessage, 'ya está registrado')) {
                return back()
                    ->withErrors(['email' => 'Este correo ya está registrado por otro paciente.'])
                    ->withInput();
            }

            return back()
                ->with('error', 'Error al actualizar: ' . $errorMessage)
                ->withInput();
        }
    }


    public function pacientesCitas(Paciente $paciente)
    {
        $paciente->load([
            'citas.especialidad',
            'citas.doctor'
        ]);

        return view('recepcionista.paciente.citas', compact('paciente'));
    }

    public function citasIndex()
    {
        $citas = Citas::with(['paciente', 'doctor', 'especialidad'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('recepcionista.citas.create', compact('citas'));
    }
    public function Adminstore(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'doctor_id' => 'required|exists:usuarios,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'fecha_inicio' => 'required|date',
            'motivo' => 'nullable|string|max:255',
        ]);

        $cita = Citas::create([
            'paciente_id' => $request->paciente_id,
            'doctor_id' => $request->doctor_id,
            'especialidad_id' => $request->especialidad_id,
            'fecha_inicio' => $request->fecha_inicio,
            'estado' => 'pendiente',
            'motivo' => $request->motivo
        ]);

        return redirect()
            ->route('secretaria.citas.create')
            ->with('success', 'Cita agendada correctamente');
    }
}