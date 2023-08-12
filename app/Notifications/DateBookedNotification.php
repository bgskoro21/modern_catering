<?php

namespace App\Notifications;

use App\Services\WhacenterService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\WhacenterChannel;

class DateBookedNotification extends Notification
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
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

    public function toWhacenter($notifiable){
        $order = $this->order;
        return (new WhacenterService())
                    ->to($order->no_telp_pemesan)
                    ->line("Halo kak $order->nama_pemesan, pesanan nomor #$order->id sudah masuk ke agenda kami! Modern Catering akan menyiapkan test food untuk menu yang kakak pesan (untuk kategori prasmanan) paling lambat satu minggu kedepan. Ditunggu ya kak!")
                    ->line('')
                    ->line("Untuk info lebih lanjut silahkan kunjungi website kami")
                    ->line("http://modern-catering.com/");
    }
}
