<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcExamen extends Model
{
    use HasFactory;

    protected $table = 'hc_examenes';

    protected $fillable = [
        'historia_id',
        'profesional_id',
        'tipo',
        'nombre_examen',
        'pedido_descripcion',
        'informe_resultado',
        'archivo_ruta',
        'estado',
        'fecha_solicitud',
        'fecha_resultado',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_resultado' => 'date',
    ];

    public const TIPOS_EXAMEN = [
        'biometria'         => 'Biometría hemática',
        'quimica_sanguinea' => 'Química sanguínea',
        'rayos_x'           => 'Rayos X (Periapical / Panorámica)',
        'otros'             => 'Otros exámenes',
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}

