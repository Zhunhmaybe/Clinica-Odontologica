<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';

    protected $fillable = [
        'historia_id',
        'fecha',
        'procedimiento',
        'prescripcion',
        'firma_profesional'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // --- Relaciones ---
    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}
