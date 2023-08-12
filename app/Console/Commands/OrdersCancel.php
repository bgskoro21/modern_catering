<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Notification;

class OrdersCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel orders that have been approved and exceeded the time limit';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = Order::where('status','Belum DP')
                ->where('updated_at','<',Carbon::now()->subMinute(1))
                ->get();
        foreach($orders as $order){
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

        $this->info('Dibatalkan '.count($orders).' orderan');
    }
}
