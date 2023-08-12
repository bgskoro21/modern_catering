<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\OrdersCancel;
use App\Models\Order;
use App\Notifications\CanceledOrderPaymentNotification;
use Carbon\Carbon;
use App\Notifications\ProcessNotification;

class Kernel extends ConsoleKernel
{
    protected $commands = [
       OrdersCancel::class
    ];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('orders:cancel')->everyMinute();
        $schedule->call(function(){
            $orders = Order::where('status', 'Belum DP')
                    ->where('updated_at',"<=",Carbon::now()->subMinutes(1))
                    ->get();
    
            foreach($orders as $order){
                $order->status = 'Dibatalkan';
                $order->save();
                $order->user->notify(new CanceledOrderPaymentNotification($order->total,$order));
            }
        })->everyMinute();

        $schedule->call(function(){
            $orders = Order::where('status', 'Booked')
                    ->where('updated_at',"<=",Carbon::now()->subMinutes(1))
                    ->get();
            foreach($orders as $order){
                $order->status = 'Diproses';
                $order->save();
                $order->user->notify(new ProcessNotification($order));
            }
        })->everyMinute();

        $schedule->call(function(){
            $orders = Order::where('status','Dibatalkan')->orWhere('status','Ditolak')->where('updated_at','<=',Carbon::now()->subMinutes(1))->get();
            foreach($orders as $order){
                $order->delete();
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
