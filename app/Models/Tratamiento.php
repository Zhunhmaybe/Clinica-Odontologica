<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'hc_sesiones_tratamiento';

    protected $fillable = [
        'historia_id',
        'cita_id',
        'profesional_id',
        'numero_sesion',
        'fecha',
        'diagnosticos_complicaciones',
        'procedimientos',
        'procedimientos_detalle',
        'prescripciones',
        'codigo_profesional',
        'firma_profesional',
        'firmado_at',
    ];

    protected $casts = [
        'fecha'                  => 'date',
        'firmado_at'             => 'datetime',
        'procedimientos_detalle' => 'array',
    ];

    // Compatibilidad con vistas antiguas que leían $tratamiento->procedimiento o $tratamiento->prescripcion
    public function getProcedimientoAttribute(): ?string
    {
        return $this->procedimientos;
    }

    public function getPrescripcionAttribute(): ?string
    {
        return $this->prescripciones;
    }

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }

    public function cita()
    {
        return $this->belongsTo(Citas::class, 'cita_id');
    }

    public function profesional()
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }
}
