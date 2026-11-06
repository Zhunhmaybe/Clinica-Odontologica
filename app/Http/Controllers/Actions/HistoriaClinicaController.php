<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use App\Models\HcAnamnesis;
use App\Models\HcSignoVital;
use App\Models\HcExamenEstomatognatico;
use App\Models\Odontograma;
use App\Models\IndicesSaludBucal;
use App\Models\Cie10;
use App\Models\Diagnostico;
use App\Models\HcExamen;
use App\Models\Tratamiento;
use Carbon\Carbon;

class HistoriaClinicaController extends Controller
{
    /**
     * Listado principal de Historias Clínicas
     */
    public function index(Request $request)
    {
        $query = HistoriaClinica::with(['paciente', 'profesional', 'anamnesis'])
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
     * Formulario Wizard (6 pasos) para crear nueva HC (HCU-form.033/2021)
     */
    public function create(Request $request)
    {
        $paciente_id = $request->query('paciente_id');

        if (!$paciente_id) {
            return redirect()->route('pacientes.index')
                ->with('error', 'Debe seleccionar un paciente del directorio para abrir una Historia Clínica.');
        }

        $paciente = Paciente::findOrFail($paciente_id);
        $cie10Catalogo = Cie10::where('activo', true)->orderBy('codigo')->get();
        $antecedentesCatalogo = HcAnamnesis::CATALOGO_ANTECEDENTES;
        $regionesEstomatognaticas = HcExamenEstomatognatico::REGIONES;
        $gruposHigiene = IndicesSaludBucal::GRUPOS_HIGIENE;
        $tiposExamen = HcExamen::TIPOS_EXAMEN;

        return view('historia_clinica.create', compact(
            'paciente',
            'cie10Catalogo',
            'antecedentesCatalogo',
            'regionesEstomatognaticas',
            'gruposHigiene',
            'tiposExamen'
        ));
    }

    /**
     * Guardar la Historia Clínica completa (Opción C: Híbrido Relacional + JSONB)
     */
    public function store(Request $request)
    {
        $accionGuardar = $request->input('accion_guardar', 'abierta');
        $estadoHistoria = ($accionGuardar === 'borrador') ? 'borrador' : 'abierta';

        $request->validate([
            'paciente_id'     => 'required|exists:pacientes,id',
            'fecha_atencion'  => 'nullable|date',
            'motivo_consulta' => $estadoHistoria === 'borrador' ? 'nullable|string|max:2000' : 'required|string|max:2000',
        ], [
            'motivo_consulta.required' => 'El motivo de consulta (Bloque B) es obligatorio para firmar la historia.',
        ]);

        DB::beginTransaction();

        try {
            $paciente = Paciente::findOrFail($request->paciente_id);
            $count = HistoriaClinica::withTrashed()->where('paciente_id', $paciente->id)->count();
            $numeroHistoria = ($paciente->cedula ?: 'HC-' . $paciente->id) . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $fechaAtencion = $request->filled('fecha_atencion') ? Carbon::parse($request->fecha_atencion) : now();
            $edadAlAbrir = $paciente->fecha_nacimiento ? Carbon::parse($paciente->fecha_nacimiento)->age : null;
            $esEmbarazada = $request->boolean('embarazada');
            $vigenteHasta = $esEmbarazada 
                ? $fechaAtencion->copy()->addMonths(9)->format('Y-m-d')
                : $fechaAtencion->copy()->addDays(365)->format('Y-m-d');

            // 1. Cabecera: historias_clinicas (Bloques A y O)
            $historia = HistoriaClinica::create([
                'paciente_id'           => $paciente->id,
                'profesional_id'        => Auth::id(),
                'institucion_sistema'   => $request->input('institucion_sistema', 'RED PRIVADA COMPLEMENTARIA'),
                'unicodigo'             => $request->input('unicodigo', '000000'),
                'establecimiento_salud' => $request->input('establecimiento_salud', 'DANNY LARA DENTAL SOLUTIONS'),
                'numero_historia'       => $numeroHistoria,
                'numero_archivo'        => $request->input('numero_archivo', $paciente->cedula),
                'numero_hoja'           => (int) $request->input('numero_hoja', 1),
                'edad_al_abrir'         => $edadAlAbrir,
                'condicion_edad'        => 'A',
                'embarazada'            => $esEmbarazada,
                'fecha_atencion'        => $fechaAtencion->format('Y-m-d'),
                'hora_apertura'         => $request->input('hora_apertura', now()->format('H:i')),
                'vigente_hasta'         => $vigenteHasta,
                'estado'                => $estadoHistoria,
                'schema_version'        => '033/2021',
                'codigo_profesional'    => $request->input('codigo_profesional', Auth::user()->cedula ?? 'MSP-OD'),
                'firmado_at'            => $estadoHistoria === 'abierta' ? now() : null,
                'firma_hash'            => $estadoHistoria === 'abierta' ? hash('sha256', $numeroHistoria . '|' . Auth::id() . '|' . now()) : null,
                'observaciones'         => $request->input('observaciones'),
            ]);

            // 2. Anamnesis y Antecedentes JSONB (Bloques B, C, D, E)
            $antPersonales = $this->construirAntecedentesJson(
                $request->boolean('ant_per_no_refiere'),
                $request->input('ant_per_checks', []),
                $request->input('ant_per_desc', [])
            );

            $antFamiliares = $this->construirAntecedentesJson(
                $request->boolean('ant_fam_no_refiere'),
                $request->input('ant_fam_checks', []),
                $request->input('ant_fam_desc', [])
            );

            HcAnamnesis::create([
                'historia_id'                    => $historia->id,
                'motivo_consulta'                => $request->input('motivo_consulta') ?: 'En elaboración (Borrador)',
                'enfermedad_actual'              => $request->input('enfermedad_actual') ?: 'NA',
                'enfermedad_actual_estructurada' => [
                    'cronologia'      => $request->input('enf_cronologia'),
                    'localizacion'    => $request->input('enf_localizacion'),
                    'caracteristicas' => $request->input('enf_caracteristicas'),
                    'intensidad'      => $request->input('enf_intensidad'),
                    'causa_aparente'  => $request->input('enf_causa'),
                ],
                'antecedentes_personales'        => $antPersonales,
                'antecedentes_familiares'        => $antFamiliares,
            ]);

            // 3. Signos Vitales (Bloque F)
            $paSistolica = $request->input('presion_sistolica');
            $paDiastolica = $request->input('presion_diastolica');
            $paTexto = $request->input('presion_arterial');
            if (!$paTexto && ($paSistolica || $paDiastolica)) {
                $paTexto = ($paSistolica ?: '-') . '/' . ($paDiastolica ?: '-');
            }

            HcSignoVital::create([
                'historia_id'             => $historia->id,
                'profesional_id'          => Auth::id(),
                'presion_sistolica'       => $paSistolica ?: null,
                'presion_diastolica'      => $paDiastolica ?: null,
                'presion_arterial'        => $paTexto,
                'pulso'                   => $request->input('pulso') ?: null,
                'temperatura'             => $request->input('temperatura') ?: null,
                'frecuencia_respiratoria' => $request->input('frecuencia_respiratoria') ?: null,
                'tomado_at'               => now(),
            ]);

            // 4. Examen Estomatognático JSONB (Bloque G)
            $sinPatologiaEstoma = $request->boolean('estoma_sin_patologia', true);
            $hallazgosEstoma = [];
            if (!$sinPatologiaEstoma) {
                $checksEstoma = $request->input('estoma_checks', []);
                $descEstoma = $request->input('estoma_desc', []);
                foreach ($checksEstoma as $cod) {
                    $codInt = (int) $cod;
                    if (isset(HcExamenEstomatognatico::REGIONES[$codInt])) {
                        $hallazgosEstoma[] = [
                            'codigo'      => $codInt,
                            'region'      => HcExamenEstomatognatico::REGIONES[$codInt],
                            'descripcion' => trim($descEstoma[$codInt] ?? '') ?: 'Con patología descrita',
                        ];
                    }
                }
                if (empty($hallazgosEstoma)) {
                    $sinPatologiaEstoma = true;
                }
            }

            HcExamenEstomatognatico::create([
                'historia_id'   => $historia->id,
                'sin_patologia' => $sinPatologiaEstoma,
                'hallazgos'     => $hallazgosEstoma,
                'observaciones' => $request->input('estoma_observaciones'),
            ]);

            // 5. Odontograma Append-Only (Bloque H)
            $odontogramaItems = [];
            if ($request->filled('odontograma_json')) {
                $decoded = json_decode($request->input('odontograma_json'), true);
                if (is_array($decoded)) {
                    $odontogramaItems = $decoded;
                }
            }

            foreach ($odontogramaItems as $item) {
                $pieza = (int) ($item['pieza'] ?? 0);
                if ($pieza <= 0) continue;

                $caras = $item['caras'] ?? [null];
                if (empty($caras)) $caras = [null];

                foreach ($caras as $cara) {
                    Odontograma::create([
                        'historia_id'    => $historia->id,
                        'profesional_id' => Auth::id(),
                        'pieza'          => $pieza,
                        'denticion'      => Odontograma::determinarDenticion($pieza),
                        'superficie'     => $cara,
                        'categoria'      => $item['categoria'] ?? 'General',
                        'hallazgo'       => $item['tratamiento'] ?? 'caries',
                        'tipo_registro'  => ($item['estado'] ?? 'malo') === 'malo' ? 'patologia' : 'realizado',
                        'color_hex'      => $item['color'] ?? '#dc3545',
                        'movilidad'      => !empty($item['movilidad']) ? (int) $item['movilidad'] : null,
                        'recesion'       => !empty($item['recesion']) ? (int) $item['recesion'] : null,
                        'observaciones'  => $item['observacion'] ?? null,
                        'origen'         => 'manual',
                    ]);
                }
            }

            // 6. Indicadores de Salud Bucal e Índices CPO-ceo (Bloques I y J)
            $higieneGrupos = [];
            $sumaPlaca = 0; $cntPlaca = 0;
            $sumaCalculo = 0; $cntCalculo = 0;
            $sumaGingivitis = 0; $cntGingivitis = 0;

            $higieneInput = $request->input('higiene', []);
            foreach (IndicesSaludBucal::GRUPOS_HIGIENE as $clave => $meta) {
                $fila = $higieneInput[$clave] ?? [];
                $piezaUsada = $fila['pieza'] ?? $meta['piezas'][0];
                $placa = isset($fila['placa']) && $fila['placa'] !== '' ? (int) $fila['placa'] : null;
                $calculo = isset($fila['calculo']) && $fila['calculo'] !== '' ? (int) $fila['calculo'] : null;
                $gingivitis = isset($fila['gingivitis']) && $fila['gingivitis'] !== '' ? (int) $fila['gingivitis'] : null;

                if ($placa !== null) { $sumaPlaca += $placa; $cntPlaca++; }
                if ($calculo !== null) { $sumaCalculo += $calculo; $cntCalculo++; }
                if ($gingivitis !== null) { $sumaGingivitis += $gingivitis; $cntGingivitis++; }

                $higieneGrupos[] = [
                    'grupo'       => $clave,
                    'pieza_usada' => (int) $piezaUsada,
                    'placa'       => $placa,
                    'calculo'     => $calculo,
                    'gingivitis'  => $gingivitis,
                ];
            }

            $promPlaca = $cntPlaca > 0 ? round($sumaPlaca / $cntPlaca, 2) : (float) $request->input('placa_total', 0);
            $promCalculo = $cntCalculo > 0 ? round($sumaCalculo / $cntCalculo, 2) : (float) $request->input('calculo_total', 0);
            $promGingivitis = $cntGingivitis > 0 ? round($sumaGingivitis / $cntGingivitis, 2) : (float) $request->input('gingivitis_total', 0);

            $cpoC = (int) $request->input('cpo_c', 0);
            $cpoP = (int) $request->input('cpo_p', 0);
            $cpoO = (int) $request->input('cpo_o', 0);
            $ceoC = (int) $request->input('ceo_c', 0);
            $ceoE = (int) $request->input('ceo_e', 0);
            $ceoO = (int) $request->input('ceo_o', 0);

            IndicesSaludBucal::create([
                'historia_id'            => $historia->id,
                'profesional_id'         => Auth::id(),
                'higiene_oral'           => [
                    'piezas_evaluadas'    => $higieneGrupos,
                    'promedio_placa'      => $promPlaca,
                    'promedio_calculo'    => $promCalculo,
                    'promedio_gingivitis' => $promGingivitis,
                ],
                'placa_total'            => $promPlaca,
                'calculo_total'          => $promCalculo,
                'gingivitis_total'       => $promGingivitis,
                'enfermedad_periodontal' => $request->input('enfermedad_periodontal', 'sana'),
                'maloclusion'            => $request->input('maloclusion', 'normal'),
                'fluorosis'              => $request->input('fluorosis', 'normal'),
                'cpo_c'                  => $cpoC,
                'cpo_p'                  => $cpoP,
                'cpo_o'                  => $cpoO,
                'cpo_total'              => $cpoC + $cpoP + $cpoO,
                'ceo_c'                  => $ceoC,
                'ceo_e'                  => $ceoE,
                'ceo_o'                  => $ceoO,
                'ceo_total'              => $ceoC + $ceoE + $ceoO,
            ]);

            // 7. Plan de Diagnóstico y Exámenes (Bloques L y M)
            $examenesInput = $request->input('examenes', []);
            if (is_array($examenesInput)) {
                foreach ($examenesInput as $ex) {
                    if (empty($ex['tipo']) && empty($ex['pedido_descripcion'])) continue;
                    HcExamen::create([
                        'historia_id'        => $historia->id,
                        'profesional_id'     => Auth::id(),
                        'tipo'               => $ex['tipo'] ?? 'rayos_x',
                        'nombre_examen'      => HcExamen::TIPOS_EXAMEN[$ex['tipo'] ?? 'rayos_x'] ?? 'Examen complementario',
                        'pedido_descripcion' => $ex['pedido_descripcion'] ?? null,
                        'informe_resultado'  => $ex['informe_resultado'] ?? null,
                        'estado'             => !empty($ex['informe_resultado']) ? 'informado' : 'solicitado',
                        'fecha_solicitud'    => $fechaAtencion->format('Y-m-d'),
                        'fecha_resultado'    => !empty($ex['informe_resultado']) ? $fechaAtencion->format('Y-m-d') : null,
                    ]);
                }
            }

            // 8. Diagnósticos CIE-10 (Bloque N)
            $diagnosticosInput = $request->input('diagnosticos', []);
            if (is_array($diagnosticosInput)) {
                $orden = 1;
                foreach ($diagnosticosInput as $diag) {
                    if (empty($diag['cie10_codigo']) && empty($diag['descripcion'])) continue;

                    $desc = $diag['descripcion'] ?? '';
                    if (empty($desc) && !empty($diag['cie10_codigo'])) {
                        $cieModel = Cie10::find($diag['cie10_codigo']);
                        $desc = $cieModel ? $cieModel->nombre : $diag['cie10_codigo'];
                    }

                    Diagnostico::create([
                        'historia_id'  => $historia->id,
                        'cie10_codigo' => !empty($diag['cie10_codigo']) ? $diag['cie10_codigo'] : null,
                        'descripcion'  => $desc,
                        'tipo'         => in_array($diag['tipo'] ?? 'PRE', ['PRE', 'DEF']) ? $diag['tipo'] : 'PRE',
                        'orden'        => $orden++,
                        'pieza_dental' => !empty($diag['pieza_dental']) ? (int) $diag['pieza_dental'] : null,
                        'origen'       => 'manual',
                    ]);
                }
            }

            // 9. Primera Sesión de Tratamiento (Bloque P) si fue llenada en el paso 6
            if ($request->filled('sesion_procedimientos') || $request->filled('sesion_prescripciones')) {
                Tratamiento::create([
                    'historia_id'                 => $historia->id,
                    'profesional_id'              => Auth::id(),
                    'numero_sesion'               => 1,
                    'fecha'                       => $fechaAtencion->format('Y-m-d'),
                    'diagnosticos_complicaciones' => $request->input('sesion_diagnosticos_complicaciones') ?: 'Sin complicaciones',
                    'procedimientos'              => $request->input('sesion_procedimientos'),
                    'prescripciones'              => $request->input('sesion_prescripciones'),
                    'codigo_profesional'          => $request->input('codigo_profesional', Auth::user()->cedula ?? 'MSP-OD'),
                    'firma_profesional'           => Auth::user()->nombre . ' ' . (Auth::user()->apellido ?? ''),
                    'firmado_at'                  => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('historia_clinica.show', $historia->id)
                ->with('success', $estadoHistoria === 'borrador'
                    ? 'Borrador de Historia Clínica guardado. Puede continuar editándolo o firmarlo cuando esté listo.'
                    : 'Historia Clínica HCU-033 firmada y almacenada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar la historia clínica: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Editar un borrador existente en el Wizard de 6 pasos
     */
    public function edit($id)
    {
        $historia = HistoriaClinica::with([
            'paciente',
            'anamnesis',
            'signosVitales',
            'examenEstomatognatico',
            'odontogramaRegistros',
            'indicadoresSalud',
            'examenes',
            'diagnosticos',
            'sesionesTratamiento',
        ])->findOrFail($id);

        if ($historia->estado !== 'borrador') {
            return redirect()->route('historia_clinica.show', $historia->id)
                ->with('error', 'Esta Historia Clínica ya fue firmada y activada, por lo que su apertura es inmutable según normativa MSP.');
        }

        $paciente = $historia->paciente;
        $cie10Catalogo = Cie10::where('activo', true)->orderBy('codigo')->get();
        $antecedentesCatalogo = HcAnamnesis::CATALOGO_ANTECEDENTES;
        $regionesEstomatognaticas = HcExamenEstomatognatico::REGIONES;
        $gruposHigiene = IndicesSaludBucal::GRUPOS_HIGIENE;
        $tiposExamen = HcExamen::TIPOS_EXAMEN;

        // Reconstruir JSON del odontograma para precargar el SVG en el Paso 4
        $odontogramaWizard = [];
        foreach ($historia->odontogramaRegistros->groupBy('pieza') as $pieza => $regs) {
            $ultimo = $regs->last();
            $caras = $regs->pluck('superficie')->filter()->values()->all();
            $odontogramaWizard[] = [
                'pieza'       => (int) $pieza,
                'categoria'   => $ultimo->categoria ?? 'General',
                'tratamiento' => $ultimo->hallazgo,
                'estado'      => $ultimo->tipo_registro === 'patologia' ? 'malo' : 'bueno',
                'caras'       => $caras,
                'movilidad'   => $ultimo->movilidad ? (string) $ultimo->movilidad : '',
                'recesion'    => $ultimo->recesion ? (string) $ultimo->recesion : '',
                'observacion' => $ultimo->observaciones ?? '',
                'color'       => $ultimo->color_hex ?: ($ultimo->tipo_registro === 'patologia' ? '#dc3545' : '#0d6efd'),
            ];
        }
        $odontogramaWizardJson = json_encode($odontogramaWizard);

        return view('historia_clinica.create', compact(
            'paciente',
            'historia',
            'odontogramaWizardJson',
            'cie10Catalogo',
            'antecedentesCatalogo',
            'regionesEstomatognaticas',
            'gruposHigiene',
            'tiposExamen'
        ));
    }

    /**
     * Actualizar un borrador existente (y opcionalmente firmarlo para pasarlo a 'abierta')
     */
    public function update(Request $request, $id)
    {
        $historia = HistoriaClinica::findOrFail($id);

        if ($historia->estado !== 'borrador') {
            return redirect()->route('historia_clinica.show', $historia->id)
                ->with('error', 'No se puede modificar la apertura de una Historia Clínica que ya ha sido firmada.');
        }

        $accionGuardar = $request->input('accion_guardar', 'abierta');
        $estadoHistoria = ($accionGuardar === 'borrador') ? 'borrador' : 'abierta';

        $request->validate([
            'fecha_atencion'  => 'nullable|date',
            'motivo_consulta' => $estadoHistoria === 'borrador' ? 'nullable|string|max:2000' : 'required|string|max:2000',
        ], [
            'motivo_consulta.required' => 'El motivo de consulta (Bloque B) es obligatorio para firmar la historia.',
        ]);

        DB::beginTransaction();

        try {
            $paciente = $historia->paciente;
            $fechaAtencion = $request->filled('fecha_atencion') ? Carbon::parse($request->fecha_atencion) : ($historia->fecha_atencion ?? now());
            $esEmbarazada = $request->boolean('embarazada');
            $vigenteHasta = $esEmbarazada
                ? $fechaAtencion->copy()->addMonths(9)->format('Y-m-d')
                : $fechaAtencion->copy()->addDays(365)->format('Y-m-d');

            // 1. Actualizar Cabecera (Bloques A y O)
            $historia->update([
                'profesional_id'        => Auth::id(),
                'institucion_sistema'   => $request->input('institucion_sistema', 'RED PRIVADA COMPLEMENTARIA'),
                'unicodigo'             => $request->input('unicodigo', '000000'),
                'establecimiento_salud' => $request->input('establecimiento_salud', 'DANNY LARA DENTAL SOLUTIONS'),
                'numero_archivo'        => $request->input('numero_archivo', $paciente->cedula),
                'embarazada'            => $esEmbarazada,
                'fecha_atencion'        => $fechaAtencion->format('Y-m-d'),
                'hora_apertura'         => $request->input('hora_apertura', now()->format('H:i')),
                'vigente_hasta'         => $vigenteHasta,
                'estado'                => $estadoHistoria,
                'codigo_profesional'    => $request->input('codigo_profesional', Auth::user()->cedula ?? 'MSP-OD'),
                'firmado_at'            => $estadoHistoria === 'abierta' ? now() : null,
                'firma_hash'            => $estadoHistoria === 'abierta' ? hash('sha256', $historia->numero_historia . '|' . Auth::id() . '|' . now()) : null,
                'observaciones'         => $request->input('observaciones'),
            ]);

            // 2. Actualizar Anamnesis y Antecedentes (Bloques B, C, D, E)
            $antPersonales = $this->construirAntecedentesJson(
                $request->boolean('ant_per_no_refiere'),
                $request->input('ant_per_checks', []),
                $request->input('ant_per_desc', [])
            );

            $antFamiliares = $this->construirAntecedentesJson(
                $request->boolean('ant_fam_no_refiere'),
                $request->input('ant_fam_checks', []),
                $request->input('ant_fam_desc', [])
            );

            HcAnamnesis::updateOrCreate(
                ['historia_id' => $historia->id],
                [
                    'motivo_consulta'                => $request->input('motivo_consulta') ?: 'En elaboración (Borrador)',
                    'enfermedad_actual'              => $request->input('enfermedad_actual') ?: 'NA',
                    'enfermedad_actual_estructurada' => [
                        'cronologia'      => $request->input('enf_cronologia'),
                        'localizacion'    => $request->input('enf_localizacion'),
                        'caracteristicas' => $request->input('enf_caracteristicas'),
                        'intensidad'      => $request->input('enf_intensidad'),
                        'causa_aparente'  => $request->input('enf_causa'),
                    ],
                    'antecedentes_personales'        => $antPersonales,
                    'antecedentes_familiares'        => $antFamiliares,
                ]
            );

            // 3. Actualizar Signos Vitales (Bloque F)
            $paSistolica = $request->input('presion_sistolica');
            $paDiastolica = $request->input('presion_diastolica');
            $paTexto = $request->input('presion_arterial');
            if (!$paTexto && ($paSistolica || $paDiastolica)) {
                $paTexto = ($paSistolica ?: '-') . '/' . ($paDiastolica ?: '-');
            }

            $signoVital = $historia->signosVitales()->first();
            if ($signoVital) {
                $signoVital->update([
                    'profesional_id'          => Auth::id(),
                    'presion_sistolica'       => $paSistolica ?: null,
                    'presion_diastolica'      => $paDiastolica ?: null,
                    'presion_arterial'        => $paTexto,
                    'pulso'                   => $request->input('pulso') ?: null,
                    'temperatura'             => $request->input('temperatura') ?: null,
                    'frecuencia_respiratoria' => $request->input('frecuencia_respiratoria') ?: null,
                ]);
            } else {
                HcSignoVital::create([
                    'historia_id'             => $historia->id,
                    'profesional_id'          => Auth::id(),
                    'presion_sistolica'       => $paSistolica ?: null,
                    'presion_diastolica'      => $paDiastolica ?: null,
                    'presion_arterial'        => $paTexto,
                    'pulso'                   => $request->input('pulso') ?: null,
                    'temperatura'             => $request->input('temperatura') ?: null,
                    'frecuencia_respiratoria' => $request->input('frecuencia_respiratoria') ?: null,
                    'tomado_at'               => now(),
                ]);
            }

            // 4. Actualizar Examen Estomatognático (Bloque G)
            $sinPatologiaEstoma = $request->boolean('estoma_sin_patologia', true);
            $hallazgosEstoma = [];
            if (!$sinPatologiaEstoma) {
                $checksEstoma = $request->input('estoma_checks', []);
                $descEstoma = $request->input('estoma_desc', []);
                foreach ($checksEstoma as $cod) {
                    $codInt = (int) $cod;
                    if (isset(HcExamenEstomatognatico::REGIONES[$codInt])) {
                        $hallazgosEstoma[] = [
                            'codigo'      => $codInt,
                            'region'      => HcExamenEstomatognatico::REGIONES[$codInt],
                            'descripcion' => trim($descEstoma[$codInt] ?? '') ?: 'Con patología descrita',
                        ];
                    }
                }
                if (empty($hallazgosEstoma)) {
                    $sinPatologiaEstoma = true;
                }
            }

            HcExamenEstomatognatico::updateOrCreate(
                ['historia_id' => $historia->id],
                [
                    'sin_patologia' => $sinPatologiaEstoma,
                    'hallazgos'     => $hallazgosEstoma,
                    'observaciones' => $request->input('estoma_observaciones'),
                ]
            );

            // 5. Actualizar Odontograma del borrador (Bloque H)
            Odontograma::where('historia_id', $historia->id)->delete();
            $odontogramaItems = [];
            if ($request->filled('odontograma_json')) {
                $decoded = json_decode($request->input('odontograma_json'), true);
                if (is_array($decoded)) {
                    $odontogramaItems = $decoded;
                }
            }

            foreach ($odontogramaItems as $item) {
                $pieza = (int) ($item['pieza'] ?? 0);
                if ($pieza <= 0) continue;

                $caras = $item['caras'] ?? [null];
                if (empty($caras)) $caras = [null];

                foreach ($caras as $cara) {
                    Odontograma::create([
                        'historia_id'    => $historia->id,
                        'profesional_id' => Auth::id(),
                        'pieza'          => $pieza,
                        'denticion'      => Odontograma::determinarDenticion($pieza),
                        'superficie'     => $cara,
                        'categoria'      => $item['categoria'] ?? 'General',
                        'hallazgo'       => $item['tratamiento'] ?? 'caries',
                        'tipo_registro'  => ($item['estado'] ?? 'malo') === 'malo' ? 'patologia' : 'realizado',
                        'color_hex'      => $item['color'] ?? '#dc3545',
                        'movilidad'      => !empty($item['movilidad']) ? (int) $item['movilidad'] : null,
                        'recesion'       => !empty($item['recesion']) ? (int) $item['recesion'] : null,
                        'observaciones'  => $item['observacion'] ?? null,
                        'origen'         => 'manual',
                    ]);
                }
            }

            // 6. Actualizar Indicadores de Salud Bucal e Índices CPO-ceo (Bloques I y J)
            $higieneGrupos = [];
            $sumaPlaca = 0; $cntPlaca = 0;
            $sumaCalculo = 0; $cntCalculo = 0;
            $sumaGingivitis = 0; $cntGingivitis = 0;

            $higieneInput = $request->input('higiene', []);
            foreach (IndicesSaludBucal::GRUPOS_HIGIENE as $clave => $meta) {
                $fila = $higieneInput[$clave] ?? [];
                $piezaUsada = $fila['pieza'] ?? $meta['piezas'][0];
                $placa = isset($fila['placa']) && $fila['placa'] !== '' ? (int) $fila['placa'] : null;
                $calculo = isset($fila['calculo']) && $fila['calculo'] !== '' ? (int) $fila['calculo'] : null;
                $gingivitis = isset($fila['gingivitis']) && $fila['gingivitis'] !== '' ? (int) $fila['gingivitis'] : null;

                if ($placa !== null) { $sumaPlaca += $placa; $cntPlaca++; }
                if ($calculo !== null) { $sumaCalculo += $calculo; $cntCalculo++; }
                if ($gingivitis !== null) { $sumaGingivitis += $gingivitis; $cntGingivitis++; }

                $higieneGrupos[] = [
                    'grupo'       => $clave,
                    'pieza_usada' => (int) $piezaUsada,
                    'placa'       => $placa,
                    'calculo'     => $calculo,
                    'gingivitis'  => $gingivitis,
                ];
            }

            $promPlaca = $cntPlaca > 0 ? round($sumaPlaca / $cntPlaca, 2) : (float) $request->input('placa_total', 0);
            $promCalculo = $cntCalculo > 0 ? round($sumaCalculo / $cntCalculo, 2) : (float) $request->input('calculo_total', 0);
            $promGingivitis = $cntGingivitis > 0 ? round($sumaGingivitis / $cntGingivitis, 2) : (float) $request->input('gingivitis_total', 0);

            $cpoC = (int) $request->input('cpo_c', 0);
            $cpoP = (int) $request->input('cpo_p', 0);
            $cpoO = (int) $request->input('cpo_o', 0);
            $ceoC = (int) $request->input('ceo_c', 0);
            $ceoE = (int) $request->input('ceo_e', 0);
            $ceoO = (int) $request->input('ceo_o', 0);

            IndicesSaludBucal::updateOrCreate(
                ['historia_id' => $historia->id],
                [
                    'profesional_id'         => Auth::id(),
                    'higiene_oral'           => [
                        'piezas_evaluadas'    => $higieneGrupos,
                        'promedio_placa'      => $promPlaca,
                        'promedio_calculo'    => $promCalculo,
                        'promedio_gingivitis' => $promGingivitis,
                    ],
                    'placa_total'            => $promPlaca,
                    'calculo_total'          => $promCalculo,
                    'gingivitis_total'       => $promGingivitis,
                    'enfermedad_periodontal' => $request->input('enfermedad_periodontal', 'sana'),
                    'maloclusion'            => $request->input('maloclusion', 'normal'),
                    'fluorosis'              => $request->input('fluorosis', 'normal'),
                    'cpo_c'                  => $cpoC,
                    'cpo_p'                  => $cpoP,
                    'cpo_o'                  => $cpoO,
                    'cpo_total'              => $cpoC + $cpoP + $cpoO,
                    'ceo_c'                  => $ceoC,
                    'ceo_e'                  => $ceoE,
                    'ceo_o'                  => $ceoO,
                    'ceo_total'              => $ceoC + $ceoE + $ceoO,
                ]
            );

            // 7. Actualizar Exámenes (Bloques L y M)
            HcExamen::where('historia_id', $historia->id)->delete();
            $examenesInput = $request->input('examenes', []);
            if (is_array($examenesInput)) {
                foreach ($examenesInput as $ex) {
                    if (empty($ex['tipo']) && empty($ex['pedido_descripcion'])) continue;
                    HcExamen::create([
                        'historia_id'        => $historia->id,
                        'profesional_id'     => Auth::id(),
                        'tipo'               => $ex['tipo'] ?? 'rayos_x',
                        'nombre_examen'      => HcExamen::TIPOS_EXAMEN[$ex['tipo'] ?? 'rayos_x'] ?? 'Examen complementario',
                        'pedido_descripcion' => $ex['pedido_descripcion'] ?? null,
                        'informe_resultado'  => $ex['informe_resultado'] ?? null,
                        'estado'             => !empty($ex['informe_resultado']) ? 'informado' : 'solicitado',
                        'fecha_solicitud'    => $fechaAtencion->format('Y-m-d'),
                        'fecha_resultado'    => !empty($ex['informe_resultado']) ? $fechaAtencion->format('Y-m-d') : null,
                    ]);
                }
            }

            // 8. Actualizar Diagnósticos CIE-10 (Bloque N)
            Diagnostico::where('historia_id', $historia->id)->delete();
            $diagnosticosInput = $request->input('diagnosticos', []);
            if (is_array($diagnosticosInput)) {
                $orden = 1;
                foreach ($diagnosticosInput as $diag) {
                    if (empty($diag['cie10_codigo']) && empty($diag['descripcion'])) continue;

                    $desc = $diag['descripcion'] ?? '';
                    if (empty($desc) && !empty($diag['cie10_codigo'])) {
                        $cieModel = Cie10::find($diag['cie10_codigo']);
                        $desc = $cieModel ? $cieModel->nombre : $diag['cie10_codigo'];
                    }

                    Diagnostico::create([
                        'historia_id'  => $historia->id,
                        'cie10_codigo' => !empty($diag['cie10_codigo']) ? $diag['cie10_codigo'] : null,
                        'descripcion'  => $desc,
                        'tipo'         => in_array($diag['tipo'] ?? 'PRE', ['PRE', 'DEF']) ? $diag['tipo'] : 'PRE',
                        'orden'        => $orden++,
                        'pieza_dental' => !empty($diag['pieza_dental']) ? (int) $diag['pieza_dental'] : null,
                        'origen'       => 'manual',
                    ]);
                }
            }

            // 9. Actualizar Primera Sesión de Tratamiento (Bloque P)
            $primeraSesion = $historia->sesionesTratamiento()->where('numero_sesion', 1)->first();
            if ($request->filled('sesion_procedimientos') || $request->filled('sesion_prescripciones')) {
                if ($primeraSesion) {
                    $primeraSesion->update([
                        'profesional_id'              => Auth::id(),
                        'fecha'                       => $fechaAtencion->format('Y-m-d'),
                        'diagnosticos_complicaciones' => $request->input('sesion_diagnosticos_complicaciones') ?: 'Sin complicaciones',
                        'procedimientos'              => $request->input('sesion_procedimientos'),
                        'prescripciones'              => $request->input('sesion_prescripciones'),
                        'codigo_profesional'          => $request->input('codigo_profesional', Auth::user()->cedula ?? 'MSP-OD'),
                        'firma_profesional'           => Auth::user()->nombre . ' ' . (Auth::user()->apellido ?? ''),
                        'firmado_at'                  => now(),
                    ]);
                } else {
                    Tratamiento::create([
                        'historia_id'                 => $historia->id,
                        'profesional_id'              => Auth::id(),
                        'numero_sesion'               => 1,
                        'fecha'                       => $fechaAtencion->format('Y-m-d'),
                        'diagnosticos_complicaciones' => $request->input('sesion_diagnosticos_complicaciones') ?: 'Sin complicaciones',
                        'procedimientos'              => $request->input('sesion_procedimientos'),
                        'prescripciones'              => $request->input('sesion_prescripciones'),
                        'codigo_profesional'          => $request->input('codigo_profesional', Auth::user()->cedula ?? 'MSP-OD'),
                        'firma_profesional'           => Auth::user()->nombre . ' ' . (Auth::user()->apellido ?? ''),
                        'firmado_at'                  => now(),
                    ]);
                }
            }

            \App\Models\Auditoria::registrar(
                accion: $estadoHistoria === 'abierta'
                    ? 'Firma y activación de borrador de Historia Clínica HCU-033'
                    : 'Actualización de borrador de Historia Clínica HCU-033',
                usuarioId: Auth::id(),
                tabla: 'historias_clinicas',
                registroId: (string) $historia->id,
                request: $request
            );

            DB::commit();

            return redirect()->route('historia_clinica.show', $historia->id)
                ->with('success', $estadoHistoria === 'borrador'
                    ? 'Cambios en el borrador guardados exitosamente.'
                    : 'Historia Clínica HCU-033 firmada digitalmente y activada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el borrador: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Firmar directamente un borrador desde la vista del expediente (show.blade.php)
     */
    public function firmarBorrador(Request $request, $id)
    {
        $historia = HistoriaClinica::findOrFail($id);

        if ($historia->estado !== 'borrador') {
            return redirect()->route('historia_clinica.show', $historia->id)
                ->with('error', 'Esta Historia Clínica ya se encuentra firmada y activa.');
        }

        $historia->update([
            'estado'             => 'abierta',
            'profesional_id'     => Auth::id(),
            'codigo_profesional' => $request->input('codigo_profesional', $historia->codigo_profesional ?: (Auth::user()->cedula ?? 'MSP-OD')),
            'firmado_at'         => now(),
            'firma_hash'         => hash('sha256', $historia->numero_historia . '|' . Auth::id() . '|' . now()),
        ]);

        \App\Models\Auditoria::registrar(
            accion: 'Firma digital directa de borrador de Historia Clínica HCU-033',
            usuarioId: Auth::id(),
            tabla: 'historias_clinicas',
            registroId: (string) $historia->id,
            request: $request
        );

        return redirect()->route('historia_clinica.show', $historia->id)
            ->with('success', 'Historia Clínica HCU-033 firmada digitalmente con sello SHA-256 y activada.');
    }

    /**
     * Construye la estructura JSONB limpia para Antecedentes D y E
     */
    private function construirAntecedentesJson(bool $noRefiere, array $checks, array $descripciones): array
    {
        if ($noRefiere || empty($checks)) {
            return [
                'no_refiere' => true,
                'items'      => [],
            ];
        }

        $items = [];
        foreach ($checks as $cod) {
            $codInt = (int) $cod;
            if (isset(HcAnamnesis::CATALOGO_ANTECEDENTES[$codInt])) {
                $items[] = [
                    'codigo'      => $codInt,
                    'etiqueta'    => HcAnamnesis::CATALOGO_ANTECEDENTES[$codInt],
                    'descripcion' => trim($descripciones[$codInt] ?? '') ?: 'Refiere antecedente',
                ];
            }
        }

        return [
            'no_refiere' => empty($items),
            'items'      => $items,
        ];
    }

    public function show($id)
    {
        $historia = HistoriaClinica::with([
            'paciente',
            'profesional',
            'anamnesis',
            'signosVitales',
            'examenEstomatognatico',
            'odontogramaRegistros',
            'indicadoresSalud',
            'examenes',
            'diagnosticos.cie10',
            'sesionesTratamiento.profesional',
        ])->findOrFail($id);

        $cie10Catalogo = Cie10::where('activo', true)->orderBy('codigo')->get();

        return view('historia_clinica.show', compact('historia', 'cie10Catalogo'));
    }

    public function editarOdontograma($id)
    {
        $historia = HistoriaClinica::with(['paciente', 'indicadoresSalud'])->findOrFail($id);
        return view('historia_clinica.odontograma', compact('historia'));
    }

    /**
     * Guarda nuevos registros en odontograma_registros (Append-Only) y actualiza CPO-ceo
     */
    public function guardarOdontograma(Request $request, $id)
    {
        $request->validate([
            'odontograma' => 'nullable|array',
            'indices'     => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $historia = HistoriaClinica::findOrFail($id);

            if ($request->filled('odontograma')) {
                // Anular registros previos si se envía la foto actualizada desde el editor interactivo
                Odontograma::where('historia_id', $historia->id)
                    ->whereNull('anulado_at')
                    ->update([
                        'anulado_at'     => now(),
                        'anulado_por_id' => Auth::id(),
                    ]);

                foreach ($request->odontograma as $dienteData) {
                    $pieza = (int) ($dienteData['numero_pieza'] ?? 0);
                    if ($pieza <= 0) continue;

                    $detalle = [];
                    if (!empty($dienteData['observaciones']) && str_starts_with($dienteData['observaciones'], '{')) {
                        $detalle = json_decode($dienteData['observaciones'], true) ?: [];
                    }

                    $caras = $detalle['caras'] ?? [null];
                    if (empty($caras)) $caras = [null];

                    foreach ($caras as $cara) {
                        Odontograma::create([
                            'historia_id'    => $historia->id,
                            'profesional_id' => Auth::id(),
                            'pieza'          => $pieza,
                            'denticion'      => Odontograma::determinarDenticion($pieza),
                            'superficie'     => $cara,
                            'categoria'      => $detalle['categoria'] ?? 'General',
                            'hallazgo'       => $detalle['tratamiento'] ?? ($dienteData['estado'] ?? 'caries'),
                            'tipo_registro'  => ($detalle['estado'] ?? 'malo') === 'malo' ? 'patologia' : 'realizado',
                            'color_hex'      => $detalle['color'] ?? '#dc3545',
                            'movilidad'      => $dienteData['movilidad'] ?? null,
                            'recesion'       => $dienteData['recesion'] ?? null,
                            'observaciones'  => $dienteData['observaciones'] ?? null,
                            'origen'         => 'manual',
                        ]);
                    }
                }
            }

            if ($request->has('indices')) {
                $ind = $request->indices;
                $cpoC = (int) ($ind['cpo_cariados'] ?? 0);
                $cpoP = (int) ($ind['cpo_perdidos'] ?? 0);
                $cpoO = (int) ($ind['cpo_obturados'] ?? 0);
                $ceoC = (int) ($ind['ceo_cariados'] ?? 0);
                $ceoE = (int) ($ind['ceo_extraccion'] ?? 0);
                $ceoO = (int) ($ind['ceo_obturados'] ?? 0);

                IndicesSaludBucal::updateOrCreate(
                    ['historia_id' => $historia->id],
                    [
                        'profesional_id'   => Auth::id(),
                        'cpo_c'            => $cpoC,
                        'cpo_p'            => $cpoP,
                        'cpo_o'            => $cpoO,
                        'cpo_total'        => $cpoC + $cpoP + $cpoO,
                        'ceo_c'            => $ceoC,
                        'ceo_e'            => $ceoE,
                        'ceo_o'            => $ceoO,
                        'ceo_total'        => $ceoC + $ceoE + $ceoO,
                        'placa_total'      => (float) ($ind['placa_bacteriana'] ?? 0),
                        'calculo_total'    => (float) ($ind['calculo_dental'] ?? 0),
                        'gingivitis_total' => (float) ($ind['gingivitis'] ?? 0),
                        'fluorosis'        => $ind['nivel_fluorosis'] ?? 'normal',
                        'maloclusion'      => $ind['tipo_oclusion'] ?? 'normal',
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Odontograma e índices actualizados correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function obtenerOdontogramaJSON($id)
    {
        $registros = Odontograma::where('historia_id', $id)
            ->whereNull('anulado_at')
            ->orderBy('id', 'asc')
            ->get();

        // Agrupar por pieza para compatibilidad con odontograma.js
        $respuesta = [];
        foreach ($registros->groupBy('pieza') as $pieza => $items) {
            $ultimo = $items->last();
            $caras = $items->pluck('superficie')->filter()->values()->all();
            $detalle = [
                'categoria'   => $ultimo->categoria ?? 'Restauradora',
                'tratamiento' => $ultimo->hallazgo,
                'estado'      => $ultimo->tipo_registro === 'patologia' ? 'malo' : 'bueno',
                'caras'       => !empty($caras) ? $caras : ['oclusal'],
                'observacion' => $ultimo->observaciones,
                'color'       => $ultimo->color_hex ?: ($ultimo->tipo_registro === 'patologia' ? '#dc3545' : '#0d6efd'),
            ];

            $respuesta[] = [
                'numero_pieza'  => $pieza,
                'estado'        => $ultimo->hallazgo,
                'observaciones' => json_encode($detalle),
            ];
        }

        return response()->json($respuesta);
    }

    public function agregarDiagnostico(Request $request, $id)
    {
        $request->validate([
            'tipo'        => 'required',
            'descripcion' => 'required|string',
        ]);

        $historia = HistoriaClinica::findOrFail($id);
        $orden = $historia->diagnosticos()->count() + 1;

        $tipoNorm = strtoupper(substr($request->tipo, 0, 3)); // PRE o DEF
        if (!in_array($tipoNorm, ['PRE', 'DEF'])) {
            $tipoNorm = 'PRE';
        }

        $historia->diagnosticos()->create([
            'cie10_codigo' => $request->input('cie10_codigo') ?: null,
            'tipo'         => $tipoNorm,
            'descripcion'  => $request->descripcion,
            'pieza_dental' => $request->input('pieza_dental') ?: null,
            'orden'        => $orden,
            'origen'       => 'manual',
        ]);

        return back()->with('success', 'Diagnóstico CIE-10 agregado correctamente.');
    }

    public function agregarTratamiento(Request $request, $id)
    {
        $request->validate([
            'fecha'         => 'required|date',
            'procedimiento' => 'required|string',
        ]);

        $historia = HistoriaClinica::findOrFail($id);
        $numSesion = $historia->sesionesTratamiento()->count() + 1;

        $historia->sesionesTratamiento()->create([
            'profesional_id'              => Auth::id(),
            'numero_sesion'               => $numSesion,
            'fecha'                       => $request->fecha,
            'diagnosticos_complicaciones' => $request->input('diagnosticos_complicaciones', 'Sin complicaciones'),
            'procedimientos'              => $request->procedimiento,
            'prescripciones'              => $request->prescripcion ?? null,
            'codigo_profesional'          => Auth::user()->cedula ?? 'MSP-OD',
            'firma_profesional'           => (Auth::user()->nombre ?? 'Doctor') . ' ' . (Auth::user()->apellido ?? ''),
            'firmado_at'                  => now(),
        ]);

        return back()->with('success', 'Sesión de tratamiento #' . $numSesion . ' registrada y firmada.');
    }
}
