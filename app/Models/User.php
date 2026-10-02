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

    public function esSuperAdmin(): bool
    {
        return $this->hasRole('Super_admin');
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
            'Super_admin' => 'Súper Administrador',
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
            'Super_admin' => 5, 
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
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->two_factor_code = $code;
        $this->two_factor_expires_at = Carbon::now()->addMinutes(5);
        $this->save();

        return $code;
    }

    public function resetTwoFactorCode(): void
    {
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    public function hasValidTwoFactorCode(): bool
    {
        if (empty($this->two_factor_code) || empty($this->two_factor_expires_at)) {
            return false;
        }

        $expiresAt = is_string($this->two_factor_expires_at)
            ? Carbon::parse($this->two_factor_expires_at)
            : $this->two_factor_expires_at;

        return Carbon::now()->lt($expiresAt);
    }

    public function getTwoFactorRemainingSeconds(): int
    {
        if (!$this->hasValidTwoFactorCode()) {
            return 0;
        }

        $expiresAt = is_string($this->two_factor_expires_at)
            ? Carbon::parse($this->two_factor_expires_at)
            : $this->two_factor_expires_at;

        return max(0, (int) Carbon::now()->diffInSeconds($expiresAt, false));
    }

    public function validateTwoFactorCode($code): bool
    {
        if (!$this->hasValidTwoFactorCode()) {
            return false;
        }

        return (string) $this->two_factor_code === trim((string) $code);
    }

    public function auditorias()
    {
        return $this->hasMany(\App\Models\Auditoria::class, 'usuario_id');
    }

    /**
     * Verifica si existe una sesión previa confiable para este usuario
     * con la misma IP y navegador/dispositivo en la tabla 'sessions'.
     */
    public function hasTrustedSession(\Illuminate\Http\Request $request): bool
    {
        $ip = $request->ip();
        $ua = $request->userAgent();

        if (empty($ip) || empty($ua)) {
            return false;
        }

        // Verifica si en la tabla 'sessions' existe un registro previo de este usuario
        // con la misma IP y User Agent dentro de los últimos 30 días
        $limitTimestamp = Carbon::now()->subDays(30)->timestamp;

        return \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->id)
            ->where('ip_address', $ip)
            ->where('user_agent', $ua)
            ->where('last_activity', '>=', $limitTimestamp)
            ->exists();
    }

    /**
     * Actualiza o registra los detalles de la sesión actual en la tabla 'sessions'.
     */
    public function recordSessionDetails(\Illuminate\Http\Request $request): void
    {
        $sessionId = $request->session()->getId();
        if ($sessionId) {
            \Illuminate\Support\Facades\DB::table('sessions')
                ->where('id', $sessionId)
                ->update([
                    'user_id'       => $this->id,
                    'ip_address'    => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                    'dispositivo'   => \App\Services\DeviceDetector::getDevice($request->userAgent()),
                    'ubicacion'     => \App\Services\DeviceDetector::getLocation($request->ip()),
                    'last_activity' => time(),
                ]);
        }
    }

    /**
     * Elimina las sesiones del usuario en la tabla 'sessions'.
     */
    public function clearSessions(): void
    {
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->id)
            ->delete();
    }
}
