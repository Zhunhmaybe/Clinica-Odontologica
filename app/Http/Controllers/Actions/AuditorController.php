<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditorController extends Controller
{
    /**
     * Dashboard principal del Auditor
     */
    public function index()
    {
        // En una base de datos real, cargarías de la tabla `auditoria`
        $totalLogs = DB::table('auditoria')->count() ?? 0;
        
        // Obtener tablas disponibles para auditar
        $tablasDisponibles = [
            'usuarios',
            'pacientes',
            'citas',
            'historias_clinicas',
            'tratamientos',
            'auditoria',
            'sessions',
            'dispositivos_2fa'
        ];

        return view('auditor.index', compact('totalLogs', 'tablasDisponibles'));
    }

    /**
     * Ver los registros de una tabla específica (solo lectura)
     */
    public function verTabla($tabla)
    {
        // Validación básica de seguridad
        $tablasDisponibles = [
            'usuarios', 'pacientes', 'citas', 'historias_clinicas', 'tratamientos', 'auditoria', 'sessions', 'dispositivos_2fa'
        ];

        if (!in_array($tabla, $tablasDisponibles)) {
            abort(403, 'No autorizado para ver esta tabla.');
        }

        $registros = DB::table($tabla)->paginate(15);

        return view('auditor.tablas.show', compact('registros', 'tabla'));
    }
}
