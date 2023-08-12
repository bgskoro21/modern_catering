<?php

namespace App\Notifications;

use App\Channels\WhacenterChannel;
use App\Services\WhacenterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinishOrderNotification extends Notification
{
    use Queueable;

    private $order,$sisa;
    public function __construct($order,$sisa)
    {
        $this->order = $order;
        $this->sisa = $sisa;
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
        $message = '';
        $order = $this->order;
        if($this->sisa === 0){
            $message .= "Halo $order->nama_pemesan, kami dari Modern Catering mengucapkan selamat atas kelancaran acara anda. Kami sangat senang dapat membantu memenuhi kebutuhan acara anda. Kami harap anda juga senang dan puas dengan pelayanan kami yaa.";
        }else{
            $message .= "Halo $order->nama_pemesan, kami dari Modern Catering mengucapkan selamat atas kelancaran acara anda. Kami sangat senang dapat membantu memenuhi kebutuhan acara anda. Kami harap anda juga senang dan puas dengan pelayanan kami yaa. Selanjutnya, silahkan lakukan pembayaran pelunasan sebesar Rp. ".$this->sisa." paling lambat H+1 dari acara anda!";
        }
        return (new WhacenterService())
                ->to($order->no_telp_pemesan)
                ->line($message)
                ->line('')
                ->line('Jangan lupa tinggalkan pesan dan kesan anda terhadap pelayanan Modern Catering di website kami yaa!')
                ->line('http://www.modern-catering.com')
                ->line('')
                ->line('Terimakasih telah mempercayai Modern Catering dalam memenuhi kebutuhan acara anda.');
    }
}
