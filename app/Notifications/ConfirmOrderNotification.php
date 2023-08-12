<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmOrderNotification extends Notification
{
    use Queueable;

    private $order;

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

    public function toWhacenter(){
        $order = $this->order;
        return (new WhacenterService())
               ->to($order->no_telp_pemesan)
               ->line("Selamat $order->nama_pemesan, pesanan kamu telah diterima oleh Modern Catering. Selanjutnya silahkan melakukan pembayaran untuk pesanan kamu nomor #$order->id. Berikut ini beberapa opsi pembayarannya yaa kak:")
               ->line('')
               ->line('1. DP Tanggal : Rp. 1.000.000 (Prasmanan)')
               ->line("2. DP Pesanan : Rp. ".$order->total/2)
               ->line("3. Bayar Lunas : Rp. ".$order->total)
               ->line('')
               ->line('Batas waktu anda untuk melakukan pembayaran adalah 3 hari setelah pesanan ini disetujui. Silahkan lakukan pembayaran sebelum batas waktu yang ditentukan untuk menghindari pembatalan secara otomatis!')
               ->line('')
               ->line('Untuk melihat informasi lebih lanjut silahkan kunjungi website kami:')
               ->line('http://www.modern-catering.com');
    }
}
