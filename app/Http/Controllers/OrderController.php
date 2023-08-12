<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Invoice;
use App\Models\NotificationAdmin;
use App\Models\NotificationPelanggan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Notifications\CancelOrderByUserNotification;
use App\Notifications\CheckoutNotification;
use App\Notifications\ConfirmOrderNotification;
use App\Notifications\FinishOrderNotification;
use App\Notifications\ProcessNotification;
use App\Notifications\RejectedOrderNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class OrderController extends Controller
{
    public function index(){
        $orders = Order::get();
        if($orders){
            return response()->json([
                'status' => true,
                'orders' => $orders
            ],200);
        }
    }

    public function getOrderById($id){
        $order = Order::with((['transactions.paket_prasmanan.kategori','payments']))->find($id);
        if($order){
            // Simpan QR code ke dalam file jika diperlukan
            $path = 'storage/qr_code/qrcode'.$order->id.'.png';

            // Berikan respons atau tindakan lain yang sesuai, seperti mengembalikan URL gambar QR code kepada pengguna
            $url = asset($path);

            return response()->json([
                'status' => true,
                'order' => $order,
                'qrcode_url' => $url
            ],200);

        }else{
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan!'
            ],404);
        }
    }

    public function getOrderbyUser(){
        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();
        $orders = Order::with(['transactions.paket_prasmanan'])->where('user_id','=',$user->id)->latest()->paginate(5);

        return response()->json([
            'status' => true,
            'orders' => $orders
        ],200);
    }

    public function cancelOrder(Request $request, $id){
        $order = Order::find($id);
        $last_update = $order->updated_at;
        $status = $order->status;
        if($order){

            $notification = NotificationPelanggan::where('order_id',$order->id)->first();
            $notificationAdmin = NotificationAdmin::where('order_id',$order->id)->first();

            $order->update([
                'status' => 'Dibatalkan'
            ]);

            $notification->update([
                'date' => Carbon::now()->locale('id'),
                'title' => "Pesanan Dibatalkan!",
                'message' => "Pesanan nomor #$order->id berhasil dibatalkan. Semoga Modern Catering dapat membantu anda di lain waktu yaa!",
                'status' => 0
            ]);

            $notificationAdmin->update([
                'status' => 0,
                'date' => Carbon::now()->locale('id'),
                'title' => 'Pesanan Dibatalkan',
                'message' => "Pesanan nomor #$order->id telah dibatalkan oleh pelanggan. Silahkan cek detail transaksi!"
            ]);
            
            $order->user->notify(new CancelOrderByUserNotification($order, $last_update,$status, $request->alasan));
            return response()->json([
                'status' => true,
                'message' => 'Pesanan berhasil dibatalkan!'
            ],200);
        }else{
            return response()->json([
                'status' => true,
                'message' => 'Pesanan gagal dibatalkan!'
            ], 404);
        }
    }

    public function checkout(Request $request){
        $validator = Validator::make($request->all(),[
            'nama_pemesan' => 'required',
            'no_telp_pemesan' => 'required|numeric',
            'tanggal_acara' => 'required',
            'waktu_acara' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ],422);
        }

        $anyOrder = Order::where('tanggal_acara', $request->tanggal_acara)->where('status','Tanggal Booked')->orWhere('status','Booked')->orWhere('status','Diproses')->get()->count();
        // return response()->json($anyOrder);
        if($anyOrder >= 4){
            return response()->json([
                'status' => false,
                'message' => "Mohon maaf pesanan untuk ".Carbon::parse($request->tanggal_acara)->isoFormat('dddd, D MMMM YYYY')." sudah penuh!"
            ]);
        }
        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();
        $packagesId = $request->input('packagesId');
        $carts = Cart::where('user_id',$user->id)->whereIn('id',$packagesId)->get();

        if(!$carts){
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada data dalam keranjang!'
            ], 200);
        }
        
        $order = Order::create([
            'user_id' => $user->id,
            'tanggal_pemesanan' => Carbon::now(),
            'nama_pemesan' => $request->nama_pemesan,
            'no_telp_pemesan' => $request->no_telp_pemesan,
            'alamat_pemesan' => $request->alamat_pemesan,
            'tanggal_acara' => $request->tanggal_acara,
            'lokasi_acara' => $request->lokasi_acara,
            'waktu_acara' => $request->waktu_acara,
            'jenis_acara' => $request->jenis_acara,
            'waktu_selesai_acara' => $request->waktu_selesai_acara,
            'status' => 'Menunggu Persetujuan',
            'total' => $request->total,
            'catatan' => $request->catatan
        ]);

        $no_invoice = 'INV-'.Carbon::now()->format('YmdHis');
        $writer = new PngWriter();
        $url = $no_invoice;
        $qrCode = new QrCode($url);

        // Simpan QR code ke dalam file jika diperlukan
        $path = 'storage/qr_code/qrcode'.$order->id.'.png';
        $result = $writer->write($qrCode);
        $result->saveToFile(public_path($path));
        // Berikan respons atau tindakan lain yang sesuai, seperti mengembalikan URL gambar QR code kepada pengguna
        $qr_url = asset($path);

        Invoice::create([
            'no_invoice' => $no_invoice,
            'order_id' => $order->id,
            'qrcode_url' => $qr_url
        ]);



        foreach($carts as $cart){
            Transaction::create([
                'jumlah_pesanan' => $cart->amount,
                'paket_prasmanan_id' => $cart->paket_prasmanan_id,
                'order_id' => $order->id,
                'menu' => $cart->menu,
                'total_harga' => $cart->total_harga
            ]);
            $cart->delete();
        }

        NotificationPelanggan::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'title' => 'Pemesanan Berhasil',
            'date' => Carbon::now()->locale('id'),
            'message' => "Hai kak $user->name, Terimakasih telah melakukan pemesanan layanan catering di Modern Catering. Admin kami akan validasi pesanan anda paling lambat 1 x 24 Jam. Nomor Pesanan anda adalah #$order->id. Silahkan ditunggu yaa kak!"
        ]);

        NotificationAdmin::create([
            'order_id' => $order->id,
            'title' => 'Pesanan Masuk!',
            'date' => Carbon::now()->locale('id'),
            'message' => "Halo Admin, ada pesanan masuk dari $user->name, silahkan lakukan validasi terhadap pesanannya ya!"
        ]);

        $order->user->notify(new CheckoutNotification($order));

        return response()->json([
            'status' => true,
            'message' => 'Checkout Berhasil!'
        ],200);
    }

    public function pendingPayment(Request $request, $id){
        $order = Order::find($id);
        if($order){
            $order->update([
                'pending_url' => $request->url
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Harap segera melakukan pembayaran!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan'
            ],404);
        }
    }

    public function updateStatusPesanan(Request $request,$id){
        $order = Order::find($id);
        if($order){
            $notification = NotificationPelanggan::where('order_id',$order->id)->first();
            if($request->status === 'Tolak'){
                $order->update([
                    'status' => 'Ditolak'
                ]);
                $notification->update([
                    'date' => Carbon::now()->locale('id'),
                    'title' => "Pesanan Ditolak!",
                    'message' => "Hai kak, Mohon maaf pesanan anda nomor #$order->id harus kami tolak dikarenakan $request->alasan",
                    'status' => 0
                ]);
                $order->user->notify(new RejectedOrderNotification($order,$request->alasan));
            }else{
                $order->update([
                    'status' => 'Belum DP'
                ]);
                $notification->update([
                    'date' => Carbon::now()->locale('id'),
                    'title' => "Pesanan Disetujui!",
                    'message' => "Hai kak, pesanan anda nomor #$order->id, telah disetujui oleh Admin. Selanjutnya, silahkan lakukan pembayaran yaa kak. Kakak bisa bayar DP terlebih dahulu atau langsung lunas. Silahkan lakukan pembayaran sebelum 3 hari dari sekaran untuk menghindari pembatalan secara otomatis.",
                    'status' => 0
                ]);
                $order->user->notify(new ConfirmOrderNotification($order));
            }
            return response()->json([
                'status' => true,
                'message' => 'Status pesanan berhasil diubah!'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Status pesanan gagal diubah!'
            ], 404);
        }
    }

    public function endProcess($id){
        $order = Order::with('transactions.paket_prasmanan')->find($id);
        $payment = Payment::where('order_id',$id)->latest()->first();
        if($order){
            $notification = NotificationPelanggan::where('order_id',$order->id)->first();
            if($payment->sisa === 0){
                $order->update([
                    'status' => 'Selesai'
                ]);
                $notification->update([
                    'date' => Carbon::now()->locale('id'),
                    'title' => "Pesanan Selesai!",
                    'message' => "Pesanan nomor #$order->id telah selesai! Modern Catering sangat senang dapat membantu acara anda. Kami berharap kakak puas yaa dengan pelayanan kami. Jangan lupa tinggal kesan dan pesan kakak yaa kak!",
                    'status' => 0
                ]);
            }else{
                $order->update([
                    'status' => 'Menunggu Pelunasan'
                ]);
                $notification->update([
                    'date' => Carbon::now()->locale('id'),
                    'title' => "Menunggu Pelunasan!",
                    'message' => "Pesanan nomor #$order->id telah selesai! Modern Catering sangat senang dapat membantu acara anda. Selanjutnya, silahkan lakukan pembayaran pelunasan ya kak paling lambat H+1 dari acara anda yaa kak. Kami berharap kakak puas yaa dengan pelayanan kami. Jangan lupa tinggal kesan dan pesan kakak yaa kak!",
                    'status' => 0
                ]);
            }
            foreach($order->transactions as $transaction){
                $transaction->paket_prasmanan->update([
                    'terjual' => $transaction->paket_prasmanan->terjual + $transaction->jumlah_pesanan
                ]);
            }
            $order->user->notify(new FinishOrderNotification($order,$payment->sisa));
            return response()->json([
                'status' => true,
                'message' => 'Status Pesanan berhasil diubah!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Status Pesanan gagal diubah!'
            ],404);
        }
    }

    public function processOrder(Request $request){
        $order = Order::find($request->order_id);
        if($order){
            $notification = NotificationPelanggan::where('order_id',$order->id)->first();

            $order->update([
                'status' => 'Diproses'
            ]);

            $notification->update([
                'date' => Carbon::now()->locale('id'),
                'title' => "Pesanan Diproses!",
                'message' => "Pesanan nomor #$order->id telah diproses! Modern Catering sedang menyiapkan pesanan anda, anda tidak lagi bisa melakukan pembatalan pesanan.",
                'status' => 0
            ]);

            $order->user->notify(new ProcessNotification($order));
            return response()->json([
                'status' => true,
                'message' => 'Pesanan berhasil diproses!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan!'
            ],404);
        }
    }
}
