<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\LoginNotification;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        session()->forget(['2fa:user:id', '2fa:remember']);
        return view('auth.login');
    }
    public function unlockForm()
    {
        return view('auth.unlock');
    }

    public function showTwoFactorForm(Request $request)
    {
        if (!$request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('2fa:user:id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        $remainingSeconds = $user->getTwoFactorRemainingSeconds();

        return view('auth.two-factor', compact('user', 'remainingSeconds'));
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'El email es obligatorio',
            'email.email' => 'Debe ser un email válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // PASO 2: BUSCAR USUARIO
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])
                ->withInput();
        }

        // PASO 3: VERIFICAR BLOQUEO
        if ($user->is_locked == 1) {
            return redirect()->route('lock.form')
                ->withErrors(['email' => 'Cuenta bloqueada. Revisa tu correo para desbloquear.']);
        }

        // PASO 4: VERIFICAR CONTRASEÑA
        if (!Hash::check($request->password, $user->password)) {

            $user->failed_attempts += 1;

            // PASO 5: BLOQUEAR DESPUÉS DE 3 INTENTOS
            if ($user->failed_attempts >= 3) {

                $code = rand(100000, 999999);

                $user->update([
                    'is_locked' => 1,
                    'lock_code' => $code
                ]);

                // Enviar correo de bloqueo
                try {
                    Mail::raw(
                        "Se detectaron múltiples intentos fallidos de inicio de sesión.\n\n" .
                            "Tu código de desbloqueo es: $code",
                        function ($message) use ($user) {
                            $message->to($user->email)
                                ->subject('Advertencia de seguridad - Cuenta bloqueada');
                        }
                    );
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de bloqueo: ' . $e->getMessage());
                }

                return redirect()->route('lock.form')
                    ->withErrors(['email' => 'Cuenta bloqueada. Código enviado a tu correo.']);
            }

            $user->save();

            return redirect()->back()
                ->withErrors(['password' => 'Contraseña incorrecta'])
                ->withInput();
        }

        // PASO 6: VERIFICAR ESTADO (ACTIVO/INACTIVO)
        if ($user->estado != 1) {
            return redirect()->back()
                ->withErrors(['email' => 'Tu cuenta está inactiva. Contacta al administrador.'])
                ->withInput();
        }

        // PASO 7: RESETEAR INTENTOS FALLIDOS
        $user->update([
            'failed_attempts' => 0
        ]);

        // PASO 8: VERIFICAR 2FA
        if ($user->two_factor_enabled) {

            // Si existe una sesión previa confiable para este usuario con la misma IP y dispositivo en 'sessions'
            if ($user->hasTrustedSession($request)) {
                Auth::login($user, $request->filled('remember'));
                $request->session()->regenerate();
                $user->recordSessionDetails($request);

                \App\Models\Auditoria::registrar(
                    accion: 'Inicio de sesión (2FA omitido - Sesión y dispositivo reconocido en tabla sessions)',
                    usuarioId: $user->id,
                    tabla: 'usuarios',
                    registroId: (string) $user->id,
                    request: $request
                );

                try {
                    $loginTime = Carbon::now()->format('d/m/Y H:i:s');
                    $ipAddress = $request->ip();
                    $user->notify(new LoginNotification($loginTime, $ipAddress));
                } catch (\Exception $e) {
                    Log::error('Error al enviar notificación de login: ' . $e->getMessage());
                }

                return $this->redirectByRole($user);
            }

            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $request->filled('remember'));

            // Validar si ya tiene un código vigente (menos de 5 minutos)
            if ($user->hasValidTwoFactorCode()) {
                return redirect()->route('2fa.verify')
                    ->with('info', 'El codigo ya ha sido enviado a su correo introdusca el codigo');
            }

            // Generar nuevo código de 6 dígitos válido por 5 minutos
            $code = $user->generateTwoFactorCode();

            try {
                $user->notify(new TwoFactorCodeNotification($code));
                $message = 'Hemos enviado un código de verificación a su correo electrónico.';
            } catch (\Exception $e) {
                Log::error('Error al enviar código 2FA: ' . $e->getMessage());
                $message = 'Código generado, pero ocurrió un problema al enviar el correo. Verifique la configuración de correo.';
            }

            \App\Models\Auditoria::registrar(
                accion: 'Solicitud de código 2FA',
                usuarioId: $user->id,
                tabla: 'usuarios',
                registroId: (string) $user->id,
                request: $request
            );

            return redirect()->route('2fa.verify')->with('status', $message);
        }

        // PASO 9: LOGIN EXITOSO
        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();
        $user->recordSessionDetails($request);

        \App\Models\Auditoria::registrar(
            accion: 'Inicio de sesión exitoso',
            usuarioId: $user->id,
            tabla: 'usuarios',
            registroId: (string) $user->id,
            request: $request
        );

        // PASO 10: ENVIAR NOTIFICACIÓN
        try {
            $loginTime = Carbon::now()->format('d/m/Y H:i:s');
            $ipAddress = $request->ip();
            $user->notify(new LoginNotification($loginTime, $ipAddress));
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de login: ' . $e->getMessage());
        }

        // PASO 11: REDIRECCIÓN POR ROL
        return $this->redirectByRole($user);
    }
    private function redirectByRole(User $user)
    {
        if ($user->hasRole('doctor')) {
            return redirect()->route('doctor.index');
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.index');
        } elseif ($user->hasRole('auditor')) {
            return redirect()->route('auditor.index');
        } elseif ($user->hasRole('recepcionista')) {
            return redirect()->route('recepcionista.index');
        } elseif ($user->hasRole('usuario')) {
            return redirect()->route('usuario.index');
        }

        return redirect()->intended('/login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //=> operador de doble flecha sirve para asignar una clave a un valor dentro de un arreglo en php
            // regex:/^[\pL\s]+$/u  regex es un patron de busqueda de texto 
            //^  comienzo de la cadena
            // $  fin de la cadena
            // +  una o mas veces
            //  \s  espacios en blanco
            // \pL  cualquier tipo de letra (considera acentos, ñ, etc.)
            // u  flag que indica que se esta utilizando unicode
            // i  flag que indica que se esta utilizando case insensitive
            //ejemplo de expresion regular: /^[a-zA-Z0-9._%+-]+@gmail\.com$/i
            //explicacion: 
            // ^[a-zA-Z0-9._%+-]+   debe iniciar con una letra, numero, punto, guion bajo, porcentaje, mas o menos
            // @   debe tener un arroba
            // gmail\.com   debe tener gmail.com
            // $   debe terminar con gmail.com
            // i   case insensitive
            // .\  es un escape de escape para que se considere el punto como un caracter literal

            'nombre' => ['required','string','max:70','regex:/^[\pL\s]+$/u'],
            'email' => ['required','string','email','max:100','regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/i','unique:usuarios'], 
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(5) // Mínimo 8 caracteres
                    ->letters()  // Debe contener al menos una letra
                    ->mixedCase() // Debe contener al menos una mayúscula y una minúscula
                    ->numbers()   // Debe contener al menos un número
                    ->symbols()   // Debe contener al menos un símbolo especial (@, $, #, etc.)
            ],

        ],
        [
            //mensajes amigables con el usuario
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser texto',
            'nombre.max' => 'Se excede el numero de caracteres para el nombre',
            'nombre.regex' => 'El nombre debe contener solo letras',
            'email.required' => 'El correo es obligatorio',
            'email.string' => 'El correo debe ser texto',
            'email.email' => 'El correo debe ser un correo valido',
            'email.max' => 'El correo debe tener maximo 100 caracteres',
            'email.regex' => 'El correo debe ser de extensión @gmail.com',
            'email.unique' => 'El correo ya esta registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.string' => 'La contraseña debe ser texto',
            'password.confirmed' => 'La contraseña no coincide',
            'password.min' => 'La contraseña debe tener minimo 5 caracteres',
            'password.letters' => 'La contraseña debe contener al menos una letra',
            'password.mixed' => 'La contraseña debe contener al menos una mayuscula y una minuscula',
            'password.numbers' => 'La contraseña debe contener al menos un numero',
            'password.symbols' => 'La contraseña debe contener al menos un simbolo',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::create([
                'nombre' => $request->nombre,
                'tel' => $request->tel ?? null,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => 1
            ]);
            $user->assignRole('usuario');

            return redirect()->route('login')->with('success', 'Registro completado. Por favor ingresa tus credenciales para iniciar sesión.');
        } catch(\Exception $e){
            Log::error('Error al registrar usuario:' . $e->getMessage());
            return back()->withErrors(['main' => 'Ocurrió un problema al crear tu cuenta. Intenta de nuevo más tarde.'])->withInput();
        }
        
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('recepcionista.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuarios,email,' . $user->id,
            'tel' => 'nullable|string|max:10',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo es obligatorio',
            'email.unique' => 'Este correo ya está en uso',
        ]);

        $user->update([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'tel' => $request->tel,
        ]);

        return redirect()->route('home')
            ->with('success', 'Perfil actualizado correctamente');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        if ($userId) {
            \App\Models\Auditoria::registrar(
                accion: 'Cierre de sesión',
                usuarioId: $userId,
                tabla: 'usuarios',
                registroId: (string) $userId,
                request: $request
            );
        }

        Auth::logout();
        // Limpiamos los datos del usuario de la sesión pero mantenemos el registro de sesión/IP en la tabla sessions
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);

        $code = trim($request->code);

        $user = User::where('email', $request->email)
            ->where('lock_code', $code)
            ->first();

        if (!$user) {
            return back()->withErrors(['code' => 'Código inválido']);
        }

        $user->update([
            'failed_attempts' => 0,
            'is_locked' => 0,
            'lock_code' => null
        ]);

        return redirect()->route('login')
            ->with('status', 'Cuenta desbloqueada correctamente');
    }
    public function resendTwoFactorCode(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['code' => 'Sesión expirada. Por favor inicie sesión nuevamente.']);
        }

        // Valida que solo pueda mandar uno cada 5 minutos
        if ($user->hasValidTwoFactorCode()) {
            return redirect()->route('2fa.verify')
                ->with('info', 'El codigo ya ha sido enviado a su correo introdusca el codigo');
        }

        // Si expiraron los 5 minutos, genera nuevo código y lo envía
        $code = $user->generateTwoFactorCode();

        try {
            $user->notify(new TwoFactorCodeNotification($code));
            return redirect()->route('2fa.verify')->with('status', 'Un nuevo código ha sido enviado a su correo.');
        } catch (\Exception $e) {
            Log::error('Error al reenviar código 2FA: ' . $e->getMessage());
            return redirect()->route('2fa.verify')->withErrors(['code' => 'Error al enviar el correo. Por favor intente más tarde.']);
        }
    }
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ], [
            'code.required' => 'El código es obligatorio',
            'code.digits' => 'El código debe tener 6 dígitos',
        ]);

        $userId = $request->session()->get('2fa:user:id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['code' => 'Sesión expirada. Por favor, inicia sesión nuevamente.']);
        }

        if (!$user->validateTwoFactorCode($request->code)) {
            return redirect()->back()
                ->withErrors(['code' => 'Código incorrecto o ha expirado.'])
                ->withInput();
        }

        $user->resetTwoFactorCode();
        $remember = $request->session()->get('2fa:remember', false);

        Auth::login($user, $remember);

        $request->session()->forget(['2fa:user:id', '2fa:remember']);
        $request->session()->regenerate();
        $user->recordSessionDetails($request);

        \App\Models\Auditoria::registrar(
            accion: 'Inicio de sesión con 2FA verificado',
            usuarioId: $user->id,
            tabla: 'usuarios',
            registroId: (string) $user->id,
            request: $request
        );

        try {
            $loginTime = Carbon::now()->format('d/m/Y H:i:s');
            $ipAddress = $request->ip();
            $user->notify(new LoginNotification($loginTime, $ipAddress));
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de login: ' . $e->getMessage());
        }

        return $this->redirectByRole($user);
    }
}


