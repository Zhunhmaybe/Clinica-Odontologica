<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Especialidades extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'color',
    ];

    /**
     * Relación: una especialidad tiene muchas citas
     */
    public function citas()
    {
        return $this->hasMany(Citas::class);
    }
}
