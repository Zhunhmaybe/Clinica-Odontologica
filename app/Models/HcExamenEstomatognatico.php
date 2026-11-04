<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcExamenEstomatognatico extends Model
{
    use HasFactory;

    protected $table = 'hc_examen_estomatognatico';

    protected $fillable = [
        'historia_id',
        'sin_patologia',
        'hallazgos',
        'observaciones',
    ];

    protected $casts = [
        'sin_patologia' => 'boolean',
        'hallazgos'     => 'array',
    ];

    /**
     * Regiones anatómicas del Bloque G (HCU-form.033/2021)
     */
    public const REGIONES = [
        1  => 'Labios',
        2  => 'Mejillas',
        3  => 'Maxilar superior',
        4  => 'Maxilar inferior',
        5  => 'Lengua',
        6  => 'Paladar',
        7  => 'Piso de boca',
        8  => 'Carrillos',
        9  => 'Glándulas salivales',
        10 => 'Orofaringe',
        11 => 'ATM (Articulación Temporomandibular)',
        12 => 'Ganglios',
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}

