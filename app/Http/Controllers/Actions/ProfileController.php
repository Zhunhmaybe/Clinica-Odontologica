<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    //Perfil
    public function showProfile()
    {
        $user = Auth::user();
        return view('components.perfil.index', compact('user'));
    }
    public function editProfile()
    {
        $user = Auth::user();
        return view('components.perfil.edit', compact('user'));
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
            ->route('perfil.index')
            ->with('success', 'Perfil actualizado correctamente');
    }

    public function show2FA()
    {
        return redirect()->route('perfil.index');
    }

    public function enable2FA(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->route('perfil.index')->with('info', 'La autenticación de dos factores ya se encuentra activada.');
        }

        $user->two_factor_enabled = true;
        $user->save();

        return redirect()->route('perfil.index')->with('success', '¡Autenticación de dos factores  activada correctamente');
    }

    public function disable2FA(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return redirect()->route('perfil.index')->with('info', 'La autenticación de dos factores ya se encuentra desactivada.');
        }

        $user->two_factor_enabled = false;
        $user->resetTwoFactorCode();
        $user->clearSessions();
        $user->save();

        return redirect()->route('perfil.index')
            ->with('success', 'Autenticación de dos factores (2FA) desactivada correctamente.');
    }

    public function forgetDevices(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->clearSessions();

        return redirect()->route('perfil.index')
            ->with('success', 'Sesiones y dispositivos recordados eliminados de la base de datos.');
    }
}
