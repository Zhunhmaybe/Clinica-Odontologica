<?php

namespace App\Services;

class DeviceDetector
{
    /**
     * Obtiene el nombre amigable del dispositivo y navegador a partir del User-Agent.
     */
    public static function getDevice(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Dispositivo desconocido';
        }

        // Detectar Plataforma / Sistema Operativo
        $platform = 'Desconocido';
        if (preg_match('/windows nt 10/i', $userAgent)) {
            $platform = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6.3/i', $userAgent)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6.2/i', $userAgent)) {
            $platform = 'Windows 8';
        } elseif (preg_match('/windows nt 6.1/i', $userAgent)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/iphone/i', $userAgent)) {
            $platform = 'iPhone';
        } elseif (preg_match('/ipad/i', $userAgent)) {
            $platform = 'iPad';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac OS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Detectar Navegador
        $browser = 'Navegador';
        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        return "{$platform} ({$browser})";
    }

    /**
     * Obtiene una descripción amigable de la ubicación a partir de la IP.
     */
    public static function getLocation(?string $ip): string
    {
        if (empty($ip) || in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return 'Red Local / Desarrollo';
        }

        return 'Acceso Remoto (' . $ip . ')';
    }
}
