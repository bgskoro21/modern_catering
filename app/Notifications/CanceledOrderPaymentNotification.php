<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CanceledOrderPaymentNotification extends Notification
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
        // return (new MailMessage)
        //             ->subject('Pengembalian Dana')
        //             ->line('Notifikasi: Pembayaran Pesanan Yang Sudah Dibatalkan!')
        //             ->action('Periksa Pesanan', url('http://localhost:3000/user/purchase'))
        //             ->line("Mohon maaf $this->name, pesanan anda sudah dibatalkan secara otomatis pada $this->tanggal karena sudah melewati batas waktu pembayaran DP. Modern Catering akan melakukan pengembalian dana sebesar Rp. $this->gross_amount sesuai nominal yang anda bayarkan. Jika ada kesalahan ataupun dana belum masuk dapat hubungi pihak Modern Catering secara langsung. Atas perhatiannya, kami ucapkan terimakasih!");
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
               ->line('Pembatalan Pesanan Otomatis')
               ->line('')
               ->line("Mohon maaf pesanan anda nomor #$order->id harus kami batalkan karena sudah melewati batas melakukan pembayaran DP. Silahkan lakukan pesanan kembali jika anda masih ingin menggunakan layanan kami.")
               ->line('')
               ->line('Untuk info lebih lanjutnya silahkan kunjungi website kami:')
               ->line('http://www.modern-catering.com');
    }
}
