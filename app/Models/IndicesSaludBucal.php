<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicesSaludBucal extends Model
{
    use HasFactory;

    protected $table = 'hc_indicadores_salud';

    protected $fillable = [
        'historia_id',
        'profesional_id',
        'higiene_oral',
        'placa_total',
        'calculo_total',
        'gingivitis_total',
        'enfermedad_periodontal',
        'maloclusion',
        'fluorosis',
        'cpo_c',
        'cpo_p',
        'cpo_o',
        'cpo_total',
        'ceo_c',
        'ceo_e',
        'ceo_o',
        'ceo_total',
    ];

    protected $casts = [
        'higiene_oral'     => 'array',
        'placa_total'      => 'decimal:2',
        'calculo_total'    => 'decimal:2',
        'gingivitis_total' => 'decimal:2',
    ];

    /**
     * Los 6 grupos de piezas índice del Bloque I (Higiene Oral Simplificada)
     */
    public const GRUPOS_HIGIENE = [
        '16_17_55' => ['piezas' => [16, 17, 55], 'label' => '16 / 17 / 55'],
        '11_21_51' => ['piezas' => [11, 21, 51], 'label' => '11 / 21 / 51'],
        '26_27_65' => ['piezas' => [26, 27, 65], 'label' => '26 / 27 / 65'],
        '36_37_75' => ['piezas' => [36, 37, 75], 'label' => '36 / 37 / 75'],
        '31_41_71' => ['piezas' => [31, 41, 71], 'label' => '31 / 41 / 71'],
        '46_47_85' => ['piezas' => [46, 47, 85], 'label' => '46 / 47 / 85'],
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_id');
    }
}
