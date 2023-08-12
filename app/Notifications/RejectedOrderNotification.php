<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RejectedOrderNotification extends Notification
{
    use Queueable;

    private $order, $message;
    public function __construct($order,$message)
    {
        $this->order = $order;
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

    public function toWhacenter(){
        $order = $this->order;
        return (new WhacenterService())
                ->to($order->no_telp_pemesan)
                ->line('Pesanan Ditolak')
                ->line('')
                ->line("Mohon maaf kak $order->nama_pemesan, pesanan anda nomor #$order->id harus kami tolak.")
                ->line('')
                ->line('Detail Pesanan:')
                ->line('Nomor Pesanan: '.$order->id)
                ->line('Nama Pemesan: '.$order->nama_pemesan)
                ->line('Tanggal Pemesanan: '.$order->tanggal_pemesanan)
                ->line('Status: '.$order->status)
                ->line('Alasan: '.$this->message)
                ->line('')
                ->line('Untuk mengethaui info lebih lanjut dari Modern Catering silahkan kunjungi website kami:')
            ->line('http://www.modern-catering.com');
    }
}
