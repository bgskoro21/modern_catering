<?php

namespace App\Http\Controllers;

use App\Models\NotificationPelanggan;
use App\Models\Order;
use App\Models\Payment;
use App\Notifications\CanceledOrderPaymentNotification;
use App\Notifications\ConfirmPayment;
use App\Notifications\ConfirmPelunasan;
use App\Notifications\DateBookedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\NotificationAdmin;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    // Mengambil snap token
    public function paymentMidtransDP(Request $request){
        $order = Order::find($request->order_id);
        $payment = Payment::where('order_id',$request->order_id)->latest()->first();
        if($payment){
            if($request->jenis_bayar === "DP Pesanan"){
                $gross_amount = $payment->sisa / 2;
                // return response()->json($gross_amount);
                $order_id = 'DP-'.$request->order_id.'-'.time();
            }else if($request->jenis_bayar === "Pelunasan"){
                $gross_amount = $payment->sisa;
                $order_id = 'L-'.$request->order_id.'-'.time();
            }
        }else{
            if($request->jenis_bayar === "DP Pesanan"){
                $gross_amount = $order->total / 2;
                $order_id = 'DP-'.$request->order_id.'-'.time();
            }else if($request->jenis_bayar === "DP Tanggal"){
                if($order->total > 1000000){
                    $gross_amount = 1000000;
                    $order_id = 'DPT-'.$request->order_id.'-'.time();
                }else{
                    $gross_amount = $order->total /2;
                    $order_id = 'DPT-'.$request->order_id.'-'.time();
                }
            }else{
                $gross_amount = $order->total;
                $order_id = 'L-'.$request->order_id.'-'.time();
            }
        }
        // Set konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
        // Buat request pembayaran ke Midtrans
        
        $snap_token = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => intval($gross_amount)
            ],
            'customer_details' => [
                'first_name' => $order->nama_pemesan,
                'email' => $order->user->email,
                'phone_number' => $order->no_telp_pemesan
            ],
            'enabled_payments' => [
                'bca_va',
                'bri_va'
            ]
        ]);
        return response()->json([
            'snap_token' => $snap_token
        ]);
    }


    // Callback Midtrans Response
    public function callback(Request $request){
         // Set konfigurasi Midtrans
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $orderId = explode('-',$request->order_id);
        $order = Order::find($orderId[1]);
        $payment = Payment::where('order_id',$orderId[1])->latest()->first();
        // create signature
        $hashed = hash('sha512',$request->order_id.$request->status_code.$request->gross_amount.$serverKey);
        if($hashed == $request->signature_key){
            $notification = NotificationPelanggan::where('order_id',$order->id)->first();
            $notificationAdmin = NotificationAdmin::where('order_id',$order->id)->first();
            if($request->transaction_status === 'settlement'){
                if($order->status === 'Date Book is Pending'){
                        $order->update([
                            'status' => 'Tanggal Booked',
                            'pending_url' => null
                        ]);
                        Payment::create([
                            'order_id' => intval($orderId[1]),
                            'tanggal_pembayaran' => $request->transaction_time,
                            'metode_pembayaran' => ucwords(str_replace('_',' ',$request->payment_type)),
                            'jumlah_bayar' => intval($request->gross_amount),
                            'sisa' => $order->total - intval($request->gross_amount),
                            'jenis_pembayaran' => 'DP Tanggal',
                        ]);

                        // Notifikasi Pelanggan
                        $notification->update([
                            'status' => 0,
                            'message' => "Terimakasih kak ".$order->user->name." telah melakukan pembayaran DP Tanggal. Pesanan anda sudah masuk ke dalam agenda kami ya kak. Kami akan mengirimkan test food untuk kakak paling lambat seminggu dari sekarang kak.",
                            "title" => "DP Tanggal Berhasil!",
                            "date" => Carbon::now()->locale('id')
                        ]);

                        // Notifikasi Admin
                        $notificationAdmin->update([
                            'status' => 0,
                            'message' => "Hai Admin, ".$order->user->name." telah melakukan pembayaran DP Tanggal untuk pesanan nomor #$order->id. Silahkan cek detail pesanan!",
                            'title' => "Pembayaran DP Tanggal",
                            "date" => Carbon::now()->locale('id')
                        ]);

                        $order->user->notify(new DateBookedNotification($order));
                    }else if($order->status === "Book is Pending"){
                        $order->update([
                        'status' => 'Booked',
                        'pending_url' => null
                    ]);

                    if($orderId[0] === "DP"){
                        $jenis_pembayaran = "DP Pesanan";
                    }else{
                        $jenis_pembayaran = "Pelunasan";
                    }
                    if($payment){
                        Payment::create([
                            'order_id' => intval($orderId[1]),
                            'tanggal_pembayaran' => $request->transaction_time,
                            'metode_pembayaran' => ucwords(str_replace('_',' ',$request->payment_type)),
                            'jumlah_bayar' => intval($request->gross_amount),
                            'sisa' => $payment->sisa - intval($request->gross_amount),
                            'jenis_pembayaran' => $jenis_pembayaran,
                        ]);

                        // Notifikasi Pelanggan
                        $notification->update([
                            'status' => 0,
                            'message' => "Terimakasih kak ".$order->user->name." telah melakukan pembayaran $jenis_pembayaran. Pesanan anda sudah masuk ke dalam agenda kami ya kak. Kami akan mengirimkan test food untuk kakak paling lambat seminggu dari sekarang kak.",
                            "title" => "$jenis_pembayaran Berhasil!",
                            "date" => Carbon::now()->locale('id')
                        ]);

                        // Notifikasi Admin
                        $notificationAdmin->update([
                            'status' => 0,
                            'message' => "Hai Admin, ".$order->user->name." telah melakukan pembayaran $jenis_pembayaran untuk pesanan nomor #$order->id. Silahkan cek detail pesanan!",
                            'title' => "Pembayaran $jenis_pembayaran",
                            "date" => Carbon::now()->locale('id')
                        ]);

                        $order->user->notify(new ConfirmPayment($request->gross_amount, $order, $payment->sisa == intval($request->gross_amount) ? 'Lunas' : 'DP'));
                    }else{
                        Payment::create([
                            'order_id' => intval($orderId[1]),
                            'tanggal_pembayaran' => $request->transaction_time,
                            'metode_pembayaran' => ucwords(str_replace('_',' ',$request->payment_type)),
                            'jumlah_bayar' => intval($request->gross_amount),
                            'sisa' => $order->total - intval($request->gross_amount),
                            'jenis_pembayaran' => $jenis_pembayaran,
                        ]);

                        // Notifikasi Pelanggan
                        $notification->update([
                            'status' => 0,
                            'message' => "Terimakasih kak ".$order->user->name." telah melakukan pembayaran $jenis_pembayaran. Pesanan anda sudah masuk ke dalam agenda kami ya kak. Kami akan mengirimkan test food untuk kakak paling lambat seminggu dari sekarang kak.",
                            "title" => "$jenis_pembayaran Berhasil!",
                            "date" => Carbon::now()->locale('id')
                        ]);

                        // Notifikasi Admin
                        $notificationAdmin->update([
                            'status' => 0,
                            'message' => "Hai Admin, ".$order->user->name." telah melakukan pembayaran $jenis_pembayaran untuk pesanan nomor #$order->id. Silahkan cek detail pesanan!",
                            'title' => "Pembayaran $jenis_pembayaran",
                            "date" => Carbon::now()->locale('id')
                        ]);

                        $order->user->notify(new ConfirmPayment($request->gross_amount, $order, $order->total == intval($request->gross_amount) ? 'Lunas' : 'DP'));
                    }
                } else if($order->status === "Pelunasan is Pending"){
                    $order->update([
                        'status' => 'Selesai',
                        'pending_url' => null
                    ]);
                    Payment::create([
                        'order_id' => intval($orderId[1]),
                        'tanggal_pembayaran' => $request->transaction_time,
                        'metode_pembayaran' => ucwords(str_replace('_',' ',$request->payment_type)),
                        'jumlah_bayar' => intval($request->gross_amount),
                        'sisa' => 0,
                        'jenis_pembayaran' => 'Pelunasan',
                    ]);

                    // Notifikasi Pelanggan
                    $notification->update([
                        'status' => 0,
                        'message' => "Terimakasih kak ".$order->user->name." telah melakukan pembayaran Pelunasan. Pesanan anda sudah selesai ya kak. Senang bisa terlibat membantu acara kakak. Jangan lupa tinggalkan kesan dan pesan kakak yaa kak!",
                        "title" => "Pesanan Selesai!",
                        "date" => Carbon::now()->locale('id')
                    ]);
                    
                    // Notifikasi admin
                    $notificationAdmin->update([
                        'status' => 0,
                        'message' => "Halo Admin, ".$order->user->name." telah melakukan pembayaran Pelunasan. Silahkan cek detail transaksinya ya!",
                        "title" => "Pesanan Selesai!",
                        "date" => Carbon::now()->locale('id')
                    ]);

                    $order->user->notify(new ConfirmPelunasan($request->gross_amount, $order));
                }
            }else if($request->transaction_status === 'pending'){
                if($orderId[0] === "DPT"){
                    $order->update([
                        'status' => 'Date Book is Pending'
                    ]);
                }else if(($orderId[0] === "DP" || $orderId[0] === "L") && ($order->status === "Belum DP" || $order->status === "Tanggal Booked") ){
                    $order->update([
                        'status' => 'Book is Pending'
                    ]);
                }else if($orderId[0] === "L" && $order->status === "Menunggu Pelunasan"){
                    $order->update([
                        'status' => 'Pelunasan is Pending'
                    ]);
                }
            }else if($request->transaction_status === 'failure'){
                if($order->status === 'DP is Pending'){
                    $order->update([
                        'status' => 'Dibatalkan',
                        'pending_url' => null 
                    ]);
                    $order->user->notify(new CanceledOrderPaymentNotification($request->gross_amount, $order));
                }else if($order->status === 'Pelunasanan is Pending'){
                    $order->update([
                        'status' => 'Menunggu Pelunasan',
                        'pending_url' => null 
                    ]);
                }
            }
        }
    }

    public function manualPayment(Request $request){
        $payment = Payment::with('order')->where('order_id',$request->order_id)->latest()->first();
        $order = Order::find($request->order_id);
        $validator = Validator::make($request->all(),[
            'jumlah_bayar' => 'required|numeric',
            'jenis_bayar' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ],422);
        }
        
        if($payment){
            $notification = NotificationPelanggan::where('order_id',$order->id)->first();
            if($request->jenis_bayar === "DP Pesanan"){
                $order->update([
                    'status' => 'Booked'
                ]);
                $notification->update([
                    'status' => 0,
                    'title' => 'Reservasi Berhasil',
                    'message' => "Halo kak ".$order->user->name." Terimakasih telah melakukan pembayaran DP Pesanan, pesanan anda sudah masuk ke dalam agenda kami yaaa. Kami akan mengantarkan test food paling lambat seminggu dari sekarang yaa kak. Terimakasih.",
                    'date' => Carbon::now()->locale('id')
                ]);
                $order->user->notify(new ConfirmPayment($request->jumlah_bayar, $order,'DP'));
            }else if($request->jenis_bayar === "Pelunasan"){
                if($order->status === "Belum DP" || $order->status === "Tanggal Booked"){
                    $order->update([
                        'status' => 'Booked'
                    ]);
                    $notification->update([
                        'status' => 0,
                        'title' => 'Reservasi Berhasil',
                        'message' => "Halo kak ".$order->user->name." Terimakasih telah melakukan pembayaran dengan langsung lunas, pesanan anda sudah masuk ke dalam agenda kami yaaa. Kami akan mengantarkan test food paling lambat seminggu dari sekarang yaa kak. Terimakasih.",
                        'date' => Carbon::now()->locale('id')
                    ]);
                    $order->user->notify(new ConfirmPayment($request->jumlah_bayar, $order,'Lunas'));
                }else{
                    $order->update([
                        'status' => 'Selesai'
                    ]);
                    $notification->update([
                        'status' => 0,
                        'title' => 'Pesanan Selesai',
                        'message' => "Halo kak ".$order->user->name." Pesanan anda sudah selesai yaa kak. Terimakasih telah mempercayai Modern Catering dalam pemenuhan kebutuhan konsumsi acara anda. Jangan lupa tinggalkan kesan dan pesan anda yaa kak.",
                        'date' => Carbon::now()->locale('id')
                    ]);
                    $order->user->notify(new ConfirmPelunasan($request->jumlah_bayar, $order));
                }
            }
            if($payment->sisa - $request->jumlah_bayar <= 0){
                $sisa = 0;
            }else{
                $sisa = $payment->sisa - $request->jumlah_bayar;
            }
        }else{
            $notification = NotificationPelanggan::where('order_id',$order->id)->first();
            if($request->jenis_bayar === "DP Pesanan" || $request->jenis_bayar === "Pelunasan"){
                $order->update([
                    'status' => 'Booked'
                ]);
                $notification->update([
                    'status' => 0,
                    'title' => 'Reservasi Berhasil',
                    'message' => "Halo kak ".$order->user->name." Terimakasih telah melakukan pembayaran dengan langsung lunas, pesanan anda sudah masuk ke dalam agenda kami yaaa. Kami akan mengantarkan test food paling lambat seminggu dari sekarang yaa kak. Terimakasih.",
                    'date' => Carbon::now()->locale('id')
                ]);
                $order->user->notify(new ConfirmPayment($request->jumlah_bayar, $order, $request->jenis_bayar === "Pelunasan" ? 'Lunas' : 'DP'));
            }else if($request->jenis_bayar === "DP Tanggal"){
                $order->update([
                    'status' => 'Tanggal Booked'
                ]);
                $notification->update([
                    'status' => 0,
                    'title' => 'Reservasi Tanggal Berhasil',
                    'message' => "Halo kak ".$order->user->name." Terimakasih telah melakukan pembayaran DP Tanggal, pesanan anda sudah masuk ke dalam agenda kami yaaa. Kami akan mengantarkan test food paling lambat seminggu dari sekarang yaa kak. Terimakasih.",
                    'date' => Carbon::now()->locale('id')
                ]);
                $order->user->notify(new DateBookedNotification($order));
            }
            if($order->total - $request->jumlah_bayar <= 0){
                $sisa = 0;
            }else{
                $sisa = $order->total - $request->jumlah_bayar;
            }
        }
        
        $tambah = Payment::create([
            'order_id' => $request->order_id,
            'tanggal_pembayaran' => Carbon::now(),
            'metode_pembayaran' => 'Manual',
            'jumlah_bayar' => $request->jumlah_bayar,
            'sisa' => $sisa,
            'jenis_pembayaran' => $request->jenis_bayar
        ]);

        if($tambah){
            return response()->json([
                'status' => true,
                'message' => 'Data pembayaran berhasil ditambahkan!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data pembayaran gagal ditambahkan!'
            ],422);
        }
    }

    public function printDetailPayment(Request $request){
        $order = Order::with('payments')->find($request->order);

        if($order){
            $pdf = Pdf::loadView('detail-pembayaran',[
                'order' => $order
            ]);
            //    Simpan File PDF di Server
            $pdfPath = storage_path('app/public/pdf/detail-payment_'.$order->nama_pemesan.'_'.$order->id.'.pdf');
            file_put_contents($pdfPath, $pdf->output());
            //    Storage::put('public/pdf/epermit.pdf', $pdf->output());
            $fileName = 'detail-payment_'.$order->nama_pemesan.'_'.$order->id.'.pdf';
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];
            return response()->download($pdfPath,'detail-payment_'.$order->nama_pemesan.'_'.$order->id.'.pdf',$headers);
        }
    }
}
