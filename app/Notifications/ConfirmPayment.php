<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmPayment extends Notification
{
    use Queueable;
    public $gross_amount, $order, $type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($gross_amount, $order, $type)
    {
        $this->order = $order;
        $this->gross_amount = $gross_amount;
        $this->type = $type;
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
        $message = '';
        $order = $this->order;
        if($this->type === 'Lunas'){
            $message .= "Terimakasih $order->nama_pemesan, telah melakukan pembayaran dengan lunas sebesar Rp. $this->gross_amount untuk pesanan nomor #$order->id. Anda masih bisa melakukan pembatalan pesanan maksimal 3 hari dari pesanan ini diproses. Jika lewat dari 3 hari akan ada potongan biaya operasional sebesar Rp. 300.000.!";
        }else{
            $message .= "Terimakasih $order->nama_pemesan, telah melakukan pembayaran DP sebesar Rp. $this->gross_amount untuk pesanan nomor #$order->id. Anda masih bisa melakukan pembatalan pesanan maksimal 3 hari dari pesanan ini diproses. Jika lewat dari 3 hari akan ada potongan biaya operasional sebesar Rp. 300.000.!";
        }
        $order = $this->order;
        return (new MailMessage)
                    ->line('Notifikasi: Pembayaran '.$this->type.' Sukses')
                    ->action('Lihat Pesanan', url('http://localhost:3000/user/purchase'))
                    ->line($message);
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
        ];
    }

    public function toWhacenter($notifiable){
        $message = '';
        $order = $this->order;
        if($this->type === 'Lunas'){
            $message .= "Terimakasih kak $order->nama_pemesan, telah melakukan pembayaran dengan lunas sebesar Rp. $this->gross_amount untuk pesanan nomor #$order->id. Pesanan #$order->id sudah masuk ke dalam agenda kami pada ".Carbon::parse($order->tanggal_acara)->locale('id')->isoFormat('dddd, DD MMMM YYYY')." Kami akan terus meningkatkan kualitas pelayanan kami demi kenyamanan pelanggan. Terimakasih kak!";
        }else{
            $message .= "Terimakasih kak $order->nama_pemesan, telah melakukan pembayaran DP sebesar Rp. $this->gross_amount untuk pesanan nomor #$order->id. Pesanan #$order->id sudah masuk ke dalam agenda kami pada ".Carbon::parse($order->tanggal_acara)->locale('id')->isoFormat('dddd, DD MMMM YYYY')." Kami akan terus meningkatkan kualitas pelayanan kami demi kenyamanan pelanggan. Terimakasih kak!";
        }
        return (new WhacenterService())
                ->to($order->no_telp_pemesan)
                ->line($message)
                ->line('')
                ->line('Anda masih bisa membatalkan pesanan sebelum status pesanan anda berubah menjadi diproses!')
                ->line('')
                ->line("Untuk info lebih lanjut silahkan kunjungi website kami")
                ->line("http://modern-catering.com/");
    }
}
