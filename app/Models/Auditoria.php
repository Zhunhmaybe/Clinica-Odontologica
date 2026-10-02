<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use App\Services\DeviceDetector;
use Carbon\Carbon;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'tabla_afectada',
        'registro_id',
        'valores_anteriores',
        'valores_nuevos',
        'ip_address',
        'dispositivo',
        'ubicacion',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Registra un evento de auditoría de forma rápida y segura.
     */
    public static function registrar(
        string $accion,
        ?int $usuarioId = null,
        ?string $tabla = null,
        ?string $registroId = null,
        ?array $anteriores = null,
        ?array $nuevos = null,
        ?Request $request = null
    ): self {
        $req = $request ?? request();
        $ip = $req?->ip() ?? '127.0.0.1';
        $ua = $req?->userAgent();

        return self::create([
            'usuario_id'         => $usuarioId ?? auth()->id(),
            'accion'             => $accion,
            'tabla_afectada'     => $tabla,
            'registro_id'        => $registroId,
            'valores_anteriores' => $anteriores,
            'valores_nuevos'     => $nuevos,
            'ip_address'         => $ip,
            'dispositivo'        => DeviceDetector::getDevice($ua),
            'ubicacion'          => DeviceDetector::getLocation($ip),
            'user_agent'         => $ua,
            'created_at'         => Carbon::now(),
        ]);
    }
}
