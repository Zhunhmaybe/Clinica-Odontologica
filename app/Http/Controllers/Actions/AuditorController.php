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
            'hc_anamnesis',
            'odontograma_registros',
            'hc_diagnosticos',
            'hc_sesiones_tratamiento',
            'auditoria',
            'sessions'
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
            'usuarios', 'pacientes', 'citas', 'historias_clinicas', 'hc_anamnesis', 'odontograma_registros', 'hc_diagnosticos', 'hc_sesiones_tratamiento', 'auditoria', 'sessions'
        ];

        if (!in_array($tabla, $tablasDisponibles)) {
            abort(403, 'No autorizado para ver esta tabla.');
        }

        $registros = DB::table($tabla)->paginate(15);

        // Columnas que nunca deben mostrarse (credenciales, tokens, payload de sesión)
        $columnasOcultas = [
            'password', 'remember_token', 'two_factor_code', 'two_factor_expires_at',
            'lock_code', 'payload', 'token',
        ];

        $registros->getCollection()->transform(function ($row) use ($columnasOcultas, $tabla) {
            $fila = (array) $row;
            foreach ($columnasOcultas as $col) {
                unset($fila[$col]);
            }
            // El id de una sesión permite secuestrarla: se enmascara
            if ($tabla === 'sessions' && isset($fila['id'])) {
                $fila['id'] = substr($fila['id'], 0, 6) . '…';
            }
            return (object) $fila;
        });

        return view('auditor.tablas.show', compact('registros', 'tabla'));
    }
}
