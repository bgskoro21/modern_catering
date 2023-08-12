<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CheckoutNotification extends Notification
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

    public function toWhacenter($notifiable){
        $order = $this->order;
        return (new WhacenterService())
               ->to($this->order->no_telp_pemesan)
               ->line("Halo $order->nama_pemesan, terimakasih telah melakukan pemesanan layanan catering di Modern Catering. Pesanan anda sudah masuk ke waiting list kami. Pesanan anda akan divalidasi oleh Admin Modern Catering paling lambat 1x24 Jam")
               ->line('')
               ->line('Detail Order:')
               ->line('')
               ->line("Nomor Pesanan: $order->id")
               ->line("Nama Pemesan: $order->nama_pemesan")
               ->line("Tanggal Pemesanan: ".Carbon::parse($order->tanggal_pemesanan)->locale('id')->isoFormat('dddd, D MMMM YYYY'))
               ->line("Tanggal Acara: ".Carbon::parse($order->tanggal_acara)->locale('id')->isoFormat('dddd, D MMMM YYYY'))
               ->line("Waktu Acara: $order->waktu_acara s/d $order->waktu_selesai_acara")
               ->line("Jenis Acara: $order->jenis_acara")
               ->line("Lokasi Acara: $order->lokasi_acara")
               ->line('')
               ->line('Untuk info selengkapnya silahkan kunjungi website kami:')
               ->line('http://www.modern-catering.com');
    }
}
