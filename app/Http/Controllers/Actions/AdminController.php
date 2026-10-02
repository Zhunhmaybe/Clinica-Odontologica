<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Dashboard principal de Administrador
     */
    public function index()
    {
        // Aquí puedes cargar métricas globales, conteo de pacientes, citas, etc.
        $totalUsuarios = User::count();
        $usuariosRecientes = User::with('roles')->latest()->take(5)->get();
        
        return view('admin.dashboard', compact('totalUsuarios', 'usuariosRecientes'));
    }

    /**
     * Módulo de Gestión de Usuarios: Listado
     */
    public function usuariosIndex()
    {
        // Se muestran todos los usuarios paginados, excluyendo al usuario actual si se desea
        $usuarios = User::with('roles')->paginate(15);
        $roles = Role::where('name', '!=', 'Super_admin')->get(); // Opcional: proteger Super_admin
        
        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    /**
     * Módulo de Gestión de Usuarios: Almacenar nuevo usuario
     */
    public function usuariosStore(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Password::defaults()],
            'tel' => ['required', 'string', 'max:20'],
            'rol' => ['required', 'exists:roles,name']
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'tel' => $request->tel,
            'password' => Hash::make($request->password),
            'estado' => 1,
        ]);

        // Asignar rol usando Spatie
        $user->assignRole($request->rol);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado y rol asignado correctamente.');
    }

    /**
     * Módulo de Gestión de Usuarios: Actualizar rol / datos
     */
    public function usuariosUpdate(Request $request, User $usuario)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:usuarios,email,'.$usuario->id],
            'rol' => ['required', 'exists:roles,name']
        ]);

        // Si intentan asignar Super_admin y el que edita no lo es, podríamos bloquearlo.
        // Por ahora confiamos en el middleware.

        $usuario->update([
            'nombre' => $request->nombre,
            'email' => $request->email,
        ]);

        // Sincronizar el nuevo rol (quita el anterior y pone el nuevo)
        $usuario->syncRoles([$request->rol]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Módulo de Gestión de Usuarios: Alternar Estado (Activo/Inactivo)
     */
    public function usuariosToggleEstado(User $usuario)
    {
        // Evitar que el admin se desactive a sí mismo
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->estado = $usuario->estado == 1 ? 0 : 1;
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Estado del usuario actualizado.');
    }
}
