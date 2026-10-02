<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odontograma extends Model
{
    use HasFactory;

    protected $table = 'odontogramas';

    protected $fillable = [
        'historia_id',
        'numero_pieza',
        'estado',
        'necesita_sellante',
        'movilidad',
        'recesion',
        'observaciones',
        'profesional_id'
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}
