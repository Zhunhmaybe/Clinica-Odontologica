<?php

namespace App\Http\Controllers\request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class PasswordResetController extends Controller
{
    /** Minutos de validez del enlace de recuperación */
    const TOKEN_EXPIRATION_MINUTES = 5;

    public function form()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100',
        ]);

        // Respuesta genérica para no revelar si el correo existe
        $mensaje = 'Si el correo está registrado, recibirás un enlace válido por ' . self::TOKEN_EXPIRATION_MINUTES . ' minutos.';

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('status', $mensaje);
        }

        $token = Str::random(64);

        DB::table('password_resets')->where('email', $user->email)->delete();
        DB::table('password_resets')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($token), // se guarda hasheado
            'created_at' => now(),
        ]);

        $link = route('password.reset', $token) . '?email=' . urlencode($user->email);

        try {
            Mail::raw(
                "Restablece tu contraseña aquí (válido por " . self::TOKEN_EXPIRATION_MINUTES . " minutos):\n$link",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Recuperación de contraseña');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Error enviando correo de recuperación: ' . $e->getMessage());
        }

        return back()->with('status', $mensaje);
    }

    public function resetForm(Request $request, $token)
    {
        $email = $request->query('email');
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $registro = DB::table('password_resets')->where('email', $request->email)->first();

        $invalido = !$registro
            || !Hash::check($request->token, $registro->token)
            || now()->diffInMinutes($registro->created_at, true) >= self::TOKEN_EXPIRATION_MINUTES;

        if ($invalido) {
            if ($registro) {
                DB::table('password_resets')->where('email', $request->email)->delete();
            }
            return back()->withErrors(['email' => 'El enlace es inválido o ha expirado. Solicita uno nuevo.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'El enlace es inválido o ha expirado. Solicita uno nuevo.']);
        }

        $user->forceFill([
            'password'              => Hash::make($request->password),
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
            'failed_attempts'       => 0,
            'is_locked'             => 0,
            'lock_code'             => null,
            'remember_token'        => Str::random(60),
        ])->save();

        // Cerrar todas las sesiones activas del usuario
        $user->clearSessions();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('/login')->with('status', 'Contraseña actualizada. Inicia sesión con tu nueva clave.');
    }
}
