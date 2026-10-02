<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicesSaludBucal extends Model
{
    use HasFactory;

    protected $table = 'indices_salud_bucal';

    protected $fillable = [
        'historia_id',
        'cpo_c',
        'cpo_p',
        'cpo_o',
        'cpo_total',
        'ceo_c',
        'ceo_e',
        'ceo_o',
        'ceo_total',
        'higiene_oral_simplificada',
        'enfermedad_periodontal',
        'maloclusion',
        'fluorosis',
        'profesional_id'
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}
