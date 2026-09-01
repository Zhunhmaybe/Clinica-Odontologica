<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Rules\ValidarCedulaEcuatoriana;
use Illuminate\Database\QueryException;

class PacientesController extends Controller
{
        public function pacientesIndex(Request $request)
    {
        $pacientes = Paciente::orderBy('nombres')->get();

        $pacienteSeleccionado = null;
        if ($request->has('paciente')) {
            $pacienteSeleccionado = Paciente::find($request->paciente);
        }

        return view('components.pacientes.index', compact(
            'pacientes',
            'pacienteSeleccionado'
        ));
    }

    public function pacientesCreate()
    {
        return view('components.pacientes.create');
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
                ->route('pacientes.index')
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
                ->route('pacientes.index')
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

        return view('components.pacientes.citas', compact('paciente'));
    }

    public function pacientesHistoria(Paciente $paciente)
    {
        // Método de placeholder para cuando lo implementes
        return back()->with('success', 'Historial clínico en desarrollo...');
    }

}
