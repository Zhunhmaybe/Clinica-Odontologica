<?php

namespace App\Http\Controllers;

use App\Models\Especialidades;
use Illuminate\Http\Request;

class EspecialidadesController extends Controller
{
    /**
     * Mostrar listado de especialidades
     */
    public function index()
    {
        $especialidades = Especialidades::orderBy('nombre')->get();

        return view('admin.especialidades.index', compact('especialidades'));
    }

    /**
     * Mostrar formulario crear
     */
    public function create()
    {
        return view('admin.especialidades.create');
    }

    /**
     * Guardar nueva especialidad
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:especialidades,nombre',
            'color'  => 'nullable|string|max:20', // Añadido basado en el nuevo modelo
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique'   => 'Esta especialidad ya existe',
        ]);

        $espe = Especialidades::create([
            'nombre' => $request->nombre,
            'color'  => $request->color ?? '#000000',
        ]);

        // auditar('INSERT', 'especialidades', $espe->id, null, $espe->toArray()); // TODO: Fase 4

        return redirect()
            ->route('admin.especialidades.index')
            ->with('success', 'Especialidad creada correctamente');
    }

    /**
     * Mostrar formulario editar
     */
    public function edit($id)
    {
        $especialidad = Especialidades::findOrFail($id);

        return view('admin.especialidades.edit', compact('especialidad'));
    }

    /**
     * Actualizar especialidad
     */
    public function update(Request $request, $id)
    {
        $especialidad = Especialidades::findOrFail($id);
        $antes = $especialidad->toArray();
        
        $request->validate([
            'nombre' => 'required|string|max:100|unique:especialidades,nombre,' . $especialidad->id,
            'color'  => 'nullable|string|max:20',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique'   => 'Esta especialidad ya existe',
        ]);

        $especialidad->update([
            'nombre' => $request->nombre,
            'color'  => $request->color ?? $especialidad->color,
        ]);

        // auditar('UPDATE', 'especialidades', $especialidad->id, $antes, $especialidad->fresh()->toArray()); // TODO: Fase 4

        return redirect()
            ->route('admin.especialidades.index')
            ->with('success', 'Especialidad actualizada correctamente');
    }

    /**
     * Eliminar especialidad
     */
    public function destroy($id)
    {
        $especialidad = Especialidades::findOrFail($id);

        // Evitar borrar si tiene citas
        if ($especialidad->citas()->count() > 0) {
            return redirect()
                ->route('admin.especialidades.index')
                ->with('error', 'No se puede eliminar la especialidad porque tiene citas asociadas.');
        }

        $especialidad->delete();

        // auditar('DELETE', 'especialidades', $id, $especialidad->toArray(), null); // TODO: Fase 4

        return redirect()
            ->route('admin.especialidades.index')
            ->with('success', 'Especialidad eliminada correctamente');
    }
}
