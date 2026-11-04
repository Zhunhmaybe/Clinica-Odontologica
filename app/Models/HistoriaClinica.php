<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoriaClinica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historias_clinicas';

    protected $fillable = [
        'paciente_id',
        'profesional_id',
        'institucion_sistema',
        'unicodigo',
        'establecimiento_salud',
        'numero_historia',
        'numero_archivo',
        'numero_hoja',
        'edad_al_abrir',
        'condicion_edad',
        'embarazada',
        'fecha_atencion',
        'hora_apertura',
        'vigente_hasta',
        'estado',
        'schema_version',
        'firmado_at',
        'firma_hash',
        'codigo_profesional',
        'observaciones',
    ];

    protected $casts = [
        'fecha_atencion' => 'date',
        'vigente_hasta'  => 'date',
        'firmado_at'     => 'datetime',
        'embarazada'     => 'boolean',
    ];

    // Compatibilidad con vistas anteriores que leían $historia->estado_historia o $historia->motivo_consulta
    public function getEstadoHistoriaAttribute(): string
    {
        return $this->estado ?? 'abierta';
    }

    public function getMotivoConsultaAttribute(): ?string
    {
        return $this->anamnesis?->motivo_consulta;
    }

    public function getEnfermedadActualAttribute(): ?string
    {
        return $this->anamnesis?->enfermedad_actual;
    }

    // --- Relaciones HCU-form.033/2021 ---

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function profesional()
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }

    public function anamnesis()
    {
        return $this->hasOne(HcAnamnesis::class, 'historia_id');
    }

    public function signosVitales()
    {
        return $this->hasMany(HcSignoVital::class, 'historia_id')->orderBy('tomado_at', 'desc');
    }

    public function ultimoSignoVital()
    {
        return $this->hasOne(HcSignoVital::class, 'historia_id')->latestOfMany('tomado_at');
    }

    public function examenEstomatognatico()
    {
        return $this->hasOne(HcExamenEstomatognatico::class, 'historia_id');
    }

    public function odontogramaRegistros()
    {
        return $this->hasMany(Odontograma::class, 'historia_id')
            ->whereNull('anulado_at')
            ->orderBy('created_at', 'asc');
    }

    public function indicadoresSalud()
    {
        return $this->hasOne(IndicesSaludBucal::class, 'historia_id');
    }

    public function examenes()
    {
        return $this->hasMany(HcExamen::class, 'historia_id')->orderBy('fecha_solicitud', 'desc');
    }

    public function diagnosticos()
    {
        return $this->hasMany(Diagnostico::class, 'historia_id')->orderBy('orden', 'asc');
    }

    public function sesionesTratamiento()
    {
        return $this->hasMany(Tratamiento::class, 'historia_id')->orderBy('numero_sesion', 'asc');
    }

    public function tratamientos()
    {
        return $this->sesionesTratamiento();
    }
}
