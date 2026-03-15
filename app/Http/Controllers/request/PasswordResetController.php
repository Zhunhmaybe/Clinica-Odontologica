<?php

namespace App\Http\Controllers\request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function form()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email'
        ]);

        $token = Str::random(60);

        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        $link = route('password.reset', $token);

        Mail::raw(
            "Restablece tu contraseña aquí:\n$link",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Recuperación de contraseña');
            }
        );
        return back()->with('status', 'Revisa tu correo');
    }

    public function resetForm($token)
    {
        return view('auth.reset-password', compact('token'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ]);

        $valid = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$valid) {
            return back()->withErrors(['email' => 'Token inválido']);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
            'two_factor_code' => null,
            'two_factor_expires_at' => null
        ]);

        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        return redirect('/login')->with('status', 'Contraseña actualizada');
    }
}
