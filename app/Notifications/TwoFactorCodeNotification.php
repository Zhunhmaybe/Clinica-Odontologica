<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Código de Verificación 2FA')
            ->greeting('¡Hola ' . $notifiable->nombre . '!')
            ->line('Has solicitado iniciar sesión en tu cuenta.')
            ->line('Tu código de verificación es:')
            ->line('**' . $this->code . '**')
            ->line('Este código expirará en 10 minutos.')
            ->line('Si no fuiste tú, ignora este mensaje.')
            ->line('¡Gracias por mantener tu cuenta segura!');
    }
}
