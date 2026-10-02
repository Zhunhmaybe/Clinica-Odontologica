<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citas;
use App\Models\HistoriaClinica;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Dashboard principal del Doctor
     */
    public function index()
    {
        $doctorId = Auth::id();

        // Citas de hoy asignadas a este doctor
        $citasHoy = Citas::with('paciente')
            ->where('doctor_id', $doctorId)
            ->whereDate('fecha_inicio', today())
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        // Historias clínicas recientes atendidas por este doctor
        $historiasRecientes = HistoriaClinica::with('paciente')
            ->where('profesional_id', $doctorId)
            ->latest()
            ->take(5)
            ->get();

        return view('doctor.index', compact('citasHoy', 'historiasRecientes'));
    }
}
