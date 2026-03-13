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
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLoginForm()
    {
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

        return view('auth.two-factor');
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

            $code = $user->generateTwoFactorCode();

            try {
                $user->notify(new TwoFactorCodeNotification($code));
            } catch (\Exception $e) {
                Log::error('Error al enviar código 2FA: ' . $e->getMessage());
            }

            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $request->filled('remember'));

            return redirect()->route('2fa.verify');
        }

        // PASO 9: LOGIN EXITOSO
        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();

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
            'nombre' => 'required|string|max:100',
            'email' => 'required|string|unique:usuarios',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::select("SELECT validar_fuerza_password(?)", [$request->password]);
            $user = User::create([
                'nombre' => $request->nombre,
                'tel' => $request->tel ?? null,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => 1
            ]);
            $user->assignRole('usuario');

            return redirect()->route('login')->with('success', 'Registro completado. Por favor ingresa tus credenciales para iniciar sesión.');
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2] ?? 'Error en base de datos';
            if (str_contains($errorMessage, 'PL/pgSQL: La contraseña')) {
                $msg = explode('PL/pgSQL:', $errorMessage)[1] ?? 'Contraseña inválida';
                $msg = explode("\n", $msg)[0];
                return back()->withErrors(['password' => trim($msg)])->withInput();
            }
            if (str_contains($errorMessage, 'PL/pgSQL: El correo')) {
                $msg = explode('PL/pgSQL:', $errorMessage)[1] ?? 'Email inválido';
                $msg = explode("\n", $msg)[0];
                return back()->withErrors(['email' => trim($msg)])->withInput();
            }
            return back()->withErrors(['main' => 'Error del sistema: ' . $errorMessage])->withInput();
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
        Auth::logout();
        $request->session()->invalidate();
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
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['code' => 'Sesión expirada.']);
        }

        $code = $user->generateTwoFactorCode();

        try {
            $user->notify(new TwoFactorCodeNotification($code));
            return redirect()->back()->with('success', 'Código reenviado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al reenviar código 2FA: ' . $e->getMessage());
            return redirect()->back()->withErrors(['code' => 'Error al enviar el código.']);
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
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['code' => 'Sesión expirada. Por favor, inicia sesión nuevamente.']);
        }

        if (!$user->validateTwoFactorCode($request->code)) {
            return redirect()->back()
                ->withErrors(['code' => 'Código inválido o expirado.'])
                ->withInput();
        }

        $user->resetTwoFactorCode();
        $remember = $request->session()->get('2fa:remember', false);

        Auth::login($user, $remember);

        $request->session()->forget(['2fa:user:id', '2fa:remember']);
        $request->session()->regenerate();

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
