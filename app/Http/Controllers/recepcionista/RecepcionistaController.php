<?php

namespace App\Http\Controllers\recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Citas;

class RecepcionistaController extends Controller
{
    /**
     * Dashboard principal de Recepcionista
     */
    public function index()
    {
        // Métricas básicas para el dashboard de recepción
        $citasHoy = Citas::whereDate('fecha_inicio', today())->count();
        $pacientesRegistrados = Paciente::count();
        $citasPendientes = Citas::where('estado', 'pendiente')->whereDate('fecha_inicio', '>=', today())->count();

        return view('recepcionista.index', compact('citasHoy', 'pacientesRegistrados', 'citasPendientes'));
    }
}