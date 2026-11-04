<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odontograma extends Model
{
    use HasFactory;

    protected $table = 'odontograma_registros';

    protected $fillable = [
        'historia_id',
        'profesional_id',
        'pieza',
        'denticion',
        'superficie',
        'categoria',
        'hallazgo',
        'tipo_registro',
        'color_hex',
        'movilidad',
        'recesion',
        'observaciones',
        'anulado_por_id',
        'anulado_at',
        'origen',
    ];

    protected $casts = [
        'anulado_at' => 'datetime',
    ];

    /**
     * Determina automáticamente si una pieza es permanente (11-48) o temporal (51-85)
     */
    public static function determinarDenticion(int $pieza): string
    {
        return ($pieza >= 51 && $pieza <= 85) ? 'temporal' : 'permanente';
    }

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }

    public function profesional()
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }
}
