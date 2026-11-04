<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    use HasFactory;

    protected $table = 'hc_diagnosticos';

    protected $fillable = [
        'historia_id',
        'cie10_codigo',
        'descripcion',
        'tipo',
        'orden',
        'pieza_dental',
        'origen',
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }

    public function cie10()
    {
        return $this->belongsTo(Cie10::class, 'cie10_codigo', 'codigo');
    }
}
