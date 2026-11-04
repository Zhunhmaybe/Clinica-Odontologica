<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Paciente;
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
     * Módulo de Gestión: Usuarios, Pacientes y Especialidades
     */
    public function usuariosIndex(Request $request)
    {
        $query = User::with(['roles', 'especialidades']);

        // 1. Filtro de búsqueda por texto (nombre, correo o teléfono)
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('tel', 'like', "%{$term}%");
            });
        }

        // 2. Filtro por Rol
        if ($request->filled('rol') && $request->rol !== 'todos') {
            $query->role($request->rol);
        }

        // 3. Ordenamiento: A-Z, Z-A o más recientes
        $sort = $request->get('sort', 'name_asc');
        if ($sort === 'name_desc') {
            $query->orderBy('nombre', 'desc');
        } elseif ($sort === 'latest') {
            $query->latest();
        } else {
            // Predeterminado: A-Z
            $query->orderBy('nombre', 'asc');
        }

        // Paginación a 5 elementos por partición
        $usuarios = $query->paginate(5, ['*'], 'usuarios_page')->withQueryString();
        $roles = Role::where('name', '!=', 'Super_admin')->get();
        
        // Paginación de Especialidades (5 por página)
        $especialidades = \App\Models\Especialidades::withCount(['citas', 'doctores'])
            ->orderBy('nombre')
            ->paginate(5, ['*'], 'especialidades_page')
            ->withQueryString();

        // Todas las especialidades para los modales de asignación (usuarios nuevos y edición)
        $todasEspecialidades = \App\Models\Especialidades::orderBy('nombre')->get();
        
        // Carga de pacientes para la sección de Gestión de Pacientes (5 por página)
        $pacientes = Paciente::withCount('citas')
            ->orderBy('nombres', 'asc')
            ->paginate(5, ['*'], 'pacientes_page')
            ->withQueryString();
        
        return view('admin.usuarios.index', compact('usuarios', 'roles', 'especialidades', 'todasEspecialidades', 'pacientes'));
    }

    /**
     * Módulo de Gestión de Usuarios: Almacenar nuevo usuario
     */
    public function usuariosStore(Request $request)
    {
        $request->validate([
            'nombre'           => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'         => ['required', Password::defaults()],
            'tel'              => ['required', 'string', 'max:20'],
            'rol'              => ['required', 'exists:roles,name'],
            'especialidades'   => ['nullable', 'array'],
            'especialidades.*' => ['exists:especialidades,id'],
        ]);

        $user = User::create([
            'nombre'   => $request->nombre,
            'email'    => $request->email,
            'tel'      => $request->tel,
            'password' => Hash::make($request->password),
            'estado'   => 1,
        ]);

        // Asignar rol usando Spatie
        $user->assignRole($request->rol);

        // Si el rol es clínico (doctor, admin, Super_admin) y seleccionó especialidades
        if (in_array($request->rol, ['doctor', 'admin', 'Super_admin']) && !empty($request->especialidades)) {
            $user->especialidades()->sync($request->especialidades);
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Módulo de Gestión de Usuarios: Actualizar rol / datos
     */
    public function usuariosUpdate(Request $request, User $usuario)
    {
        $rules = [
            'nombre'           => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:usuarios,email,'.$usuario->id],
            'tel'              => ['nullable', 'string', 'max:20'],
            'rol'              => ['required', 'exists:roles,name'],
            'especialidades'   => ['nullable', 'array'],
            'especialidades.*' => ['exists:especialidades,id'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = [Password::defaults()];
        }

        $request->validate($rules);

        $usuarioData = [
            'nombre' => $request->nombre,
            'email'  => $request->email,
        ];

        if ($request->has('tel')) {
            $usuarioData['tel'] = $request->tel;
        }

        if ($request->filled('password')) {
            $usuarioData['password'] = Hash::make($request->password);
        }

        $usuario->update($usuarioData);

        // Sincronizar el nuevo rol
        $usuario->syncRoles([$request->rol]);

        // Manejar especialidades si rol es clínico o no
        if (!in_array($request->rol, ['doctor', 'admin', 'Super_admin'])) {
            $usuario->especialidades()->detach();
        } elseif ($request->has('especialidades')) {
            $usuario->especialidades()->sync($request->especialidades ?? []);
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Módulo de Gestión de Usuarios: Eliminar usuario (CRUD - Delete)
     */
    public function usuariosDestroy(User $usuario)
    {
        // Evitar que el usuario se elimine a sí mismo
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes eliminar tu propia cuenta de usuario.');
        }

        // Si tiene citas asignadas como doctor, prevenir eliminación para no romper registros
        if ($usuario->citasComoDoctor()->exists()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No se puede eliminar a "' . $usuario->nombre . '" porque tiene citas registradas o históricas asociadas. Puedes desactivar su cuenta en su lugar.');
        }

        $nombre = $usuario->nombre;
        $usuario->especialidades()->detach();
        $usuario->roles()->detach();
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'El usuario "' . $nombre . '" fue eliminado correctamente.');
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

    /**
     * Módulo de Gestión: Asignar especialidades a un usuario clínico
     */
    public function usuariosAsignarEspecialidad(Request $request, User $usuario)
    {
        if (!$usuario->hasAnyRole(['doctor', 'admin', 'Super_admin'])) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'Solo los usuarios con rol de Doctor, Administrador o Super Administrador pueden tener especialidades asignadas.');
        }

        $request->validate([
            'especialidades'   => ['nullable', 'array'],
            'especialidades.*' => ['exists:especialidades,id'],
        ]);

        $usuario->especialidades()->sync($request->especialidades ?? []);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Especialidades asignadas correctamente a ' . $usuario->nombre . '.');
    }

    /**
     * Compatibilidad: Asignar especialidades a doctor
     */
    public function doctoresAsignarEspecialidad(Request $request, User $doctor)
    {
        return $this->usuariosAsignarEspecialidad($request, $doctor);
    }

    /**
     * Compatibilidad: Registrar doctor directamente
     */
    public function doctoresStore(Request $request)
    {
        return $this->usuariosStore($request);
    }
}
