<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcAnamnesis extends Model
{
    use HasFactory;

    protected $table = 'hc_anamnesis';

    protected $fillable = [
        'historia_id',
        'motivo_consulta',
        'enfermedad_actual',
        'enfermedad_actual_estructurada',
        'antecedentes_personales',
        'antecedentes_familiares',
    ];

    protected $casts = [
        'enfermedad_actual_estructurada' => 'array',
        'antecedentes_personales'        => 'array',
        'antecedentes_familiares'        => 'array',
    ];

    /**
     * Catálogo oficial Bloque D y E del Formulario HCU-033/2021
     */
    public const CATALOGO_ANTECEDENTES = [
        1  => 'Alergia antibiótico',
        2  => 'Alergia anestesia',
        3  => 'Hemorragias',
        4  => 'VIH / SIDA',
        5  => 'Tuberculosis',
        6  => 'Asma',
        7  => 'Diabetes',
        8  => 'Hipertensión arterial',
        9  => 'Enf. cardíaca',
        10 => 'Otro',
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}

