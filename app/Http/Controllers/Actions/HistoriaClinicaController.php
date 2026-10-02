<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use App\Models\Odontograma;
use App\Models\IndicesSaludBucal;
use App\Models\Diagnostico;
use App\Models\Tratamiento;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoriaClinicaController extends Controller
{
    /**
     * Listado principal de Historias Clínicas
     */
    public function index(Request $request)
    {
        $query = HistoriaClinica::with(['paciente', 'profesional'])
            ->orderBy('created_at', 'desc');

        $pacienteFiltrado = null;
        if ($request->filled('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
            $pacienteFiltrado = Paciente::find($request->paciente_id);
        }

        $historias = $query->paginate(10)->withQueryString();

        return view('historia_clinica.index', compact('historias', 'pacienteFiltrado'));
    }

    /**
     * Formulario para crear nueva HC
     */
    public function create(Request $request)
    {
        $paciente_id = $request->query('paciente_id');

        if (!$paciente_id) {
            return redirect()->back()->with('error', 'Debe seleccionar un paciente primero.');
        }

        $paciente = Paciente::findOrFail($paciente_id);

        return view('historia_clinica.create', compact('paciente'));
    }

    /**
     * Guardar la HC inicial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_atencion' => 'required|date',
            'motivo_consulta' => 'required|string',
            'enfermedad_actual' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Generar número de historia correlativo
            $count = HistoriaClinica::where('paciente_id', $request->paciente_id)->count();
            $numeroHistoria = 'HC-' . str_pad($request->paciente_id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $historia = new HistoriaClinica();
            $historia->fill($request->all()); 
            $historia->numero_historia = $numeroHistoria;
            $historia->estado_historia = 'abierta';
            $historia->profesional_id = Auth::id(); 
            $historia->save();

            // Inicializar Índices de Salud Bucal
            IndicesSaludBucal::create([
                'historia_id' => $historia->id,
                'profesional_id' => Auth::id()
            ]);

            // Inicializar Odontograma (52 piezas)
            $this->inicializarDientes($historia->id);

            DB::commit();

            return redirect()->route('historia_clinica.odontograma', $historia->id)
                ->with('success', 'Historia creada. Ahora actualice el odontograma.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear historia: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $historia = HistoriaClinica::with([
            'paciente',
            'diagnosticos',
            'tratamientos',
            'profesional'
        ])->findOrFail($id);

        return view('historia_clinica.show', compact('historia'));
    }

    public function editarOdontograma($id)
    {
        $historia = HistoriaClinica::with(['paciente'])->findOrFail($id);
        return view('historia_clinica.odontograma', compact('historia'));
    }

    public function guardarOdontograma(Request $request, $id)
    {
        $request->validate([
            'odontograma' => 'required|array',
            'indices' => 'nullable|array'
        ]);

        DB::beginTransaction();
        try {
            $historia = HistoriaClinica::findOrFail($id);

            foreach ($request->odontograma as $dienteData) {
                Odontograma::updateOrCreate(
                    [
                        'historia_id' => $historia->id,
                        'numero_pieza' => $dienteData['numero_pieza']
                    ],
                    [
                        'estado' => $dienteData['estado'],
                        'necesita_sellante' => $dienteData['necesita_sellante'] ?? false,
                        'movilidad' => $dienteData['movilidad'] ?? null,
                        'recesion' => $dienteData['recesion'] ?? null,
                        'observaciones' => $dienteData['observaciones'] ?? null,
                        'profesional_id' => Auth::id()
                    ]
                );
            }

            if ($request->has('indices')) {
                $indices = IndicesSaludBucal::firstOrNew(['historia_id' => $historia->id]);
                $indices->fill($request->indices);
                $indices->profesional_id = Auth::id();
                $indices->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Odontograma guardado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function obtenerOdontogramaJSON($id)
    {
        $dientes = Odontograma::where('historia_id', $id)->get();
        return response()->json($dientes);
    }

    public function agregarDiagnostico(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required',
            'descripcion' => 'required'
        ]);

        $historia = HistoriaClinica::findOrFail($id);
        $historia->diagnosticos()->create([
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
        ]);

        return back()->with('success', 'Diagnóstico agregado.');
    }

    public function agregarTratamiento(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'procedimiento' => 'required',
        ]);

        $historia = HistoriaClinica::findOrFail($id);
        $historia->tratamientos()->create([
            'fecha' => $request->fecha,
            'procedimiento' => $request->procedimiento,
            'prescripcion' => $request->prescripcion ?? null,
            'firma_profesional' => Auth::user()->nombre ?? 'Doctor'
        ]);

        return back()->with('success', 'Tratamiento registrado.');
    }

    private function inicializarDientes($historiaId)
    {
        $piezas = [
            11, 12, 13, 14, 15, 16, 17, 18,
            21, 22, 23, 24, 25, 26, 27, 28,
            31, 32, 33, 34, 35, 36, 37, 38,
            41, 42, 43, 44, 45, 46, 47, 48,
            // Temporales
            51, 52, 53, 54, 55,
            61, 62, 63, 64, 65,
            71, 72, 73, 74, 75,
            81, 82, 83, 84, 85
        ];

        $data = [];
        foreach ($piezas as $pieza) {
            $data[] = [
                'historia_id' => $historiaId,
                'numero_pieza' => $pieza,
                'estado' => 'sano',
                'profesional_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Odontograma::insert($data);
    }
}
