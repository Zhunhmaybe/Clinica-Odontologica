<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasRoles;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'tel',
        'email',
        'password',
        'estado',
        'failed_attempts',
        'is_locked',
        'lock_code',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
        ];
    }

    public function esDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function esAdministrador(): bool
    {
        return $this->hasRole('admin');
    }

    public function esAuditor(): bool
    {
        return $this->hasRole('auditor');
    }

    public function esRecepcionista(): bool
    {
        return $this->hasRole('recepcionista');
    }

    public function esUsuario(): bool
    {
        return $this->hasRole('usuario');
    }
    //----------------------------------------------------------------
    // Obtener nombre del estado
    public function getNombreRolAttribute(): string
    {
        $role = $this->roles->first();
        
        if (!$role) {
            return 'Sin rol asignado';
        }

        return match ($role->name) {
            'doctor' => 'Doctor',
            'admin' => 'Administrador',
            'auditor' => 'Auditor',
            'recepcionista' => 'Recepcionista',
            'usuario' => 'Usuario',
            default => 'Desconocido',
        };
    }
    //metodo para tener compatibilidad de rol int
    public function getRolCodeAttribute(): ?int
    {
        $role = $this->roles->first();
        
        if (!$role) {
            return null;
        }

        return match ($role->name) {
            'doctor' => 0,
            'admin' => 1,
            'auditor' => 2,
            'recepcionista' => 3,
            'usuario' => 4,
            default => null,
        };
    }
    //obtener estado
    public function estaActivo(): bool
    {
        return $this->estado === 1;
    }

    public function getNombreEstadoAttribute(): string
    {
        return match ($this->estado) {
            1 => 'Activo',
            0 => 'Inactivo',
            default => 'Desconocido',
        };
    }
    //----------------------------------------------------------------
    // Métodos para 2FA
    public function generateTwoFactorCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->two_factor_code = $code;
        $this->two_factor_expires_at = Carbon::now()->addMinutes(10);
        $this->save();

        return $code;
    }

    public function resetTwoFactorCode(): void
    {
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    public function validateTwoFactorCode($code): bool
    {
        if (!$this->two_factor_code || !$this->two_factor_expires_at) {
            return false;
        }

        if (Carbon::now()->gt($this->two_factor_expires_at)) {
            return false;
        }

        return $this->two_factor_code === $code;
    }

    //relaciones-----------------------------------------------------
    /*
    public function historiasClinics()
    {
        return $this->hasMany(\App\Models\HistoriaClinica::class, 'profesional_id');
    }


    public function citas()
    {
        return $this->hasMany(\App\Models\Cita::class, 'doctor_id');
    }


    public function consentimientos()
    {
        return $this->hasMany(\App\Models\ConsentimientoInformado::class, 'profesional_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(\App\Models\Auditoria::class, 'usuario_id');
    }
    */
}
