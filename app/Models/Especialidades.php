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
        'descripcion',
        'activa',
    ];

    /**
     * Relación: una especialidad tiene muchas citas
     */
    public function citas()
    {
        return $this->hasMany(Citas::class, 'especialidad_id');
    }

    /**
     * Relación: doctores que ejercen esta especialidad (únicamente usuarios con rol doctor,admin y super_admin)
     */
    public function doctores()
    {
        return $this->belongsToMany(User::class, 'usuario_especialidades', 'especialidad_id', 'usuario_id')
            ->role(['doctor', 'admin', 'Super_admin']);
    }
}
