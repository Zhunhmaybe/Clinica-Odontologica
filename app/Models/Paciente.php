<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'cedula',
        'nombres',
        'apellidos',
        'email',
        'telefono',
        'fecha_nacimiento',
        'direccion',
        'consentimiento_lopdp',
        'fecha_firma_lopdp',
        'datos_dinamicos',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_firma_lopdp' => 'datetime',
        'consentimiento_lopdp' => 'boolean',
        'datos_dinamicos' => 'array',
    ];

    public function citas()
    {
        return $this->hasMany(Citas::class)->orderBy('fecha_inicio', 'desc');
    }

}