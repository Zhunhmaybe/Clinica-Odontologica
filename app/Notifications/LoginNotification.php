<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginNotification extends Notification
{
    use Queueable;

    protected $loginTime;
    protected $ipAddress;

    public function __construct($loginTime, $ipAddress)
    {
        $this->loginTime = $loginTime;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Notificación de Inicio de Sesión')
            ->greeting('¡Hola ' . $notifiable->nombre . '!')
            ->line('Se ha detectado un inicio de sesión en tu cuenta.')
            ->line('Hora de inicio: ' . $this->loginTime)
            ->line('Dirección IP: ' . $this->ipAddress)
            ->line('Si no fuiste tú quien inició sesión, por favor cambia tu contraseña inmediatamente.')
            ->action('Ir a mi cuenta', url('/home'))
            ->line('¡Gracias por usar nuestra aplicación!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
