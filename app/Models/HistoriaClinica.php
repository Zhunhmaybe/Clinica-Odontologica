<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinica extends Model
{
    use HasFactory;

    protected $table = 'historias_clinicas';

    protected $fillable = [
        'paciente_id',
        'numero_historia',
        'fecha_atencion',
        'estado_historia',
        'motivo_consulta',
        'enfermedad_actual',
        'profesional_id',

        // Campos booleanos antiguos (mantener compatibilidad)
        'alergias',
        'cardiopatias',
        'diabetes',
        'hipertension',
        'tuberculosis',
        'fam_diabetes',
        'fam_hipertension',
        'fam_cancer',
        'fam_tuberculosis',
        'antecedentes_otros',
        'observaciones',

        // NUEVOS CAMPOS JSON
        'patologias_personales',
        'alergias_lista',
        'patologias_familiares',

        // Vitales y Examen físico
        'temperatura',
        'presion_arterial',
        'pulso',
        'frecuencia_respiratoria',
        'labios',
        'lengua',
        'paladar',
        'piso_boca',
        'encias',
        'carrillos',
        'orofaringe',
        'atm',
    ];

    protected $casts = [
        'fecha_atencion' => 'date',
        'cardiopatias' => 'boolean',
        'diabetes' => 'boolean',
        'hipertension' => 'boolean',
        'tuberculosis' => 'boolean',
        'fam_diabetes' => 'boolean',
        'fam_hipertension' => 'boolean',
        'fam_cancer' => 'boolean',
        'fam_tuberculosis' => 'boolean',
        // Casts para los nuevos campos
        'patologias_personales' => 'array',
        'alergias_lista' => 'array',
        'patologias_familiares' => 'array',
    ];

    // --- Relaciones ---
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function profesional()
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }

    /*
    public function odontograma()
    {
        return $this->hasMany(Odontograma::class, 'historia_id');
    }

    public function indicesSaludBucal()
    {
        return $this->hasOne(IndicesSaludBucal::class, 'historia_id');
    }
    */

    public function diagnosticos()
    {
        return $this->hasMany(Diagnostico::class, 'historia_id');
    }

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'historia_id');
    }
}
