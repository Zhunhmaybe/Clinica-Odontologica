<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcSignoVital extends Model
{
    use HasFactory;

    protected $table = 'hc_signos_vitales';

    protected $fillable = [
        'historia_id',
        'profesional_id',
        'presion_sistolica',
        'presion_diastolica',
        'presion_arterial',
        'pulso',
        'temperatura',
        'frecuencia_respiratoria',
        'tomado_at',
    ];

    protected $casts = [
        'temperatura' => 'decimal:1',
        'tomado_at'   => 'datetime',
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}

