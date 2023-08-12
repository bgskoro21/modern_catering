<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KonsultasiNotification extends Notification
{
    use Queueable;

    public $konsultasi,$message;


    public function __construct($konsultasi, $message)
    {
        $this->konsultasi = $konsultasi;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WhacenterChannel::class];
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
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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

    public function toWhaCenter($notifiable){
        $konsultasi = $this->konsultasi;
        return (new WhacenterService())
            ->to('088286825931')
            ->line("Halo kak $konsultasi->name, kami dari Modern Catering telah menerima pertanyaan anda yaitu:")
            ->line('')
            ->line("$konsultasi->pesan")
            ->line('')
            ->line($this->message)
            ->line('')
            ->line('Sekian jawaban dari kami kak, silahkan ajukan pertanyaan lain jika masih ada yang belum dimengerti yaa kak! Terimakasih!');
        }
}
