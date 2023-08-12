<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CancelOrderByUserNotification extends Notification
{
    use Queueable;

    private $order,$last_update, $alasan, $status;
    public function __construct($order, $update, $status, $alasan)
    {
        $this->order = $order;
        $this->last_update = $update;
        $this->status = $status;
        $this->alasan = $alasan;
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
                ->line("Halo kak $order->nama_pemesan, kami dari Modern Catering sangat menyayangkan kakak membatalkan pesanannya. Kami berharap bisa membantu kakak di lain waktu ya kak. Terimakasih kak $order->nama_pemesan. Jika pesanan kakak sudah diberikan test foodnya kita potong Rp. 300.000 (untuk pesanan prasmanan) sesuai dengan perjanjian di awal checkout ya kak!")
                ->line('')
                ->line('Detail Pesanan:')
                ->line('')
                ->line('Nomor Pesanan: '.$order->id)
                ->line('Nama Pemesan: '.$order->nama_pemesan)
                ->line('Tanggal Pemesanan: '.$order->tanggal_pemesanan)
                ->line('Status: '.$order->status)
                ->line('Alasan: '.$this->alasan)
                ->line('')
                ->line('Untuk mengetahui info lebih lanjut dari Modern Catering bisa kunjungi website kami:')
                ->line('http://www.modern-catering.com');
    }
}
