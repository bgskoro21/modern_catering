<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;
use Carbon\Carbon;

class CancelOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = Order::find($this->orderId);
        if($order && $order->status === 'Belum DP' && now()->diffInMinutes($order->updated_at) >= 1){
            $order->update([
                'status' => 'Dibatalkan'
            ]);
            Notification::create([
                'tipe_notifikasi' => 'Pesanan Dibatalkan',
                'tanggal' => Carbon::now(),
                'message' => 'Mohon maaf, pesanan anda dibatalkan secara otomatis karena sudah melewati batas pembayaran DP!',
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'status' => 'Belum Dibaca'
            ]);
        }
    }
}
