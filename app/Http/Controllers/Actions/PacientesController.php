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
        $fechaLimiteMinima = date('Y-m-d', strtotime('-1 year'));

        $request->validate([
            'cedula'               => ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/', new ValidarCedulaEcuatoriana, 'unique:pacientes,cedula'],
            'nombres'              => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'apellidos'            => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'telefono'             => ['required', 'string', 'max:10', 'regex:/^[0-9]{7,10}$/'],
            'email'                => ['nullable', 'string', 'max:100', 'email:rfc,dns,filter', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'fecha_nacimiento'     => ['required', 'date', 'before_or_equal:' . $fechaLimiteMinima],
            'direccion'            => ['nullable', 'string', 'max:1000'],
            'consentimiento_lopdp' => ['required', 'accepted'],
        ], [
            'cedula.required'                  => 'La cédula es obligatoria.',
            'cedula.size'                      => 'La cédula debe contener exactamente 10 dígitos numéricos.',
            'cedula.regex'                     => 'La cédula solo puede contener dígitos numéricos.',
            'cedula.unique'                    => 'Esta cédula ya se encuentra registrada en el sistema.',
            'nombres.required'                 => 'El nombre del paciente es obligatorio.',
            'nombres.max'                      => 'El nombre no puede tener más de 100 caracteres.',
            'nombres.regex'                    => 'El nombre solo puede contener letras y espacios (sin caracteres especiales ni números).',
            'apellidos.required'               => 'El apellido del paciente es obligatorio.',
            'apellidos.max'                    => 'El apellido no puede tener más de 100 caracteres.',
            'apellidos.regex'                  => 'El apellido solo puede contener letras y espacios (sin caracteres especiales ni números).',
            'telefono.required'                => 'El número de teléfono es obligatorio.',
            'telefono.max'                     => 'El teléfono no puede superar los 10 dígitos.',
            'telefono.regex'                   => 'El teléfono solo debe contener números (máximo 10 dígitos).',
            'email.max'                        => 'El correo electrónico no puede superar los 100 caracteres.',
            'email.email'                      => 'Debe ingresar un formato de correo electrónico válido.',
            'email.regex'                      => 'El correo debe tener un dominio estándar válido (ej. @gmail.com, @hotmail.com, @outlook.com).',
            'fecha_nacimiento.required'        => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before_or_equal' => 'El paciente debe tener al menos 1 año de edad.',
            'consentimiento_lopdp.required'    => 'Debe aceptar la política de tratamiento de datos personales (LOPDP).',
            'consentimiento_lopdp.accepted'    => 'Debe aceptar la política de tratamiento de datos personales (LOPDP).',
        ]);

        try {
            $paciente = Paciente::create([
                'cedula'               => $request->cedula,
                'nombres'              => $request->nombres,
                'apellidos'            => $request->apellidos,
                'email'                => $request->email,
                'telefono'             => $request->telefono,
                'fecha_nacimiento'     => $request->fecha_nacimiento,
                'direccion'            => $request->direccion,
                'consentimiento_lopdp' => true,
                'fecha_firma_lopdp'    => now(),
            ]);

            return redirect()
                ->route('pacientes.index')
                ->with('success', 'Paciente registrado correctamente.');
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2] ?? 'Error desconocido';

            if (str_contains($errorMessage, 'no cumple la edad mínima')) {
                return back()
                    ->withErrors(['fecha_nacimiento' => 'El paciente debe tener al menos 1 año de edad.'])
                    ->withInput();
            }

            if (str_contains($errorMessage, 'fecha de nacimiento no puede ser futura')) {
                return back()
                    ->withErrors(['fecha_nacimiento' => 'La fecha de nacimiento no puede ser futura.'])
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
        $request->validate([
            'telefono'  => ['required', 'string', 'max:10', 'regex:/^[0-9]{7,10}$/'],
            'email'     => ['nullable', 'string', 'max:100', 'email:rfc,dns,filter', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'unique:pacientes,email,'.$paciente->id],
            'direccion' => ['nullable', 'string', 'max:1000'],
        ], [
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.max'      => 'El teléfono no puede superar los 10 dígitos.',
            'telefono.regex'    => 'El teléfono solo debe contener números (máximo 10 dígitos).',
            'email.max'         => 'El correo electrónico no puede superar los 100 caracteres.',
            'email.email'       => 'Debe ingresar un formato de correo electrónico válido.',
            'email.regex'       => 'El correo debe tener un dominio estándar válido (ej. @gmail.com, @hotmail.com, @outlook.com).',
            'email.unique'      => 'Este correo ya está registrado por otro paciente.',
        ]);

        try {
            $paciente->update([
                'telefono'  => $request->telefono,
                'email'     => $request->email,
                'direccion' => $request->direccion,
            ]);

            return redirect()
                ->route('pacientes.index')
                ->with('success', 'Paciente actualizado correctamente.');
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
        return redirect()->route('historia_clinica.index', ['paciente_id' => $paciente->id]);
    }

}
