<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use Illuminate\Bus\Queueable;
use App\Services\WhacenterService;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ConfirmPelunasan extends Notification
{
    use Queueable;
    public $gross_amount, $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($gross_amount, $order)
    {
        $this->order = $order;
        $this->gross_amount = $gross_amount;
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
        $order = $this->order;
        return (new MailMessage)
                    ->subject('Konfirmasi Pelunasan')
                    ->line('Notifikasi: Konfirmasi Pelunasan Pesanan')
                    ->action('Lihat Pesanan', url('http://localhost:3000/user/purchase'))
                    ->line("Terimakasih $order->name, telah melakukan pembayaran pelunasan sebesar $order->gross_amount untuk pesanan nomor #$order->id. Modern Catering sangat berharap bisa terus membantu anda dalam memenuhi kebutuhan acara anda. Jangan lupa, tinggalkan penilaian anda terhadap pelayanan Modern Catering yaa! Terimakasih!");
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
        ->line("Terimakasih $order->nama_pemesan, telah melakukan pembayaran pelunasan sebesar Rp. $this->gross_amount untuk pesanan nomor #$order->id. Modern Catering sangat senang dapat memenuhi kebutuhan acara anda dengan baik. Jangan lupa tinggalkan kesan dan pesan anda untuk Modern Catering di website ya!")
        ->line('')
        ->line("Untuk info lebih lanjut silahkan kunjungi website kami")
        ->line("http://modern-catering.com/");
    }
}
