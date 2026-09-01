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
        $role = Auth::user()->rol;

        return match ($role) {
            'doctor' => view('doctor.2fa'),
            'admin' => view('admin.2fa'),
            'auditor' => view('auditor.2fa'),
            'recepcionista' => view('recepcionista.2fa'),
            default => redirect()->back()
        };
    }

    public function enable2FA(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->back()->with('error', 'La autenticación de dos factores ya está habilitada.');
        }

        $user->two_factor_enabled = true;
        $user->save();

        return redirect()->back()->with('success', '¡Autenticación de dos factores habilitada! Se solicitará un código en tu próximo inicio de sesión.');
    }

    public function disable2FA(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return redirect()->back()->with('error', 'La autenticación de dos factores no está habilitada.');
        }

        $user->two_factor_enabled = false;
        $user->resetTwoFactorCode();
        $user->save();

        return redirect()->back()->with('success', 'Autenticación de dos factores deshabilitada.');
    }
}
