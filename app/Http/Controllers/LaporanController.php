<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function penjualanReport(Request $request){
        $order = Order::with('transactions.paket_prasmanan')->whereMonth('tanggal_pemesanan','=',$request->bulan)->whereYear('tanggal_pemesanan',$request->tahun)->where('status', 'Selesai')->get();
        
            $date = Carbon::createFromFormat('m',$request->bulan);
            $month = $date->locale('id')->isoFormat('MMMM');

            $pdf = Pdf::loadView('laporan-penjualan', [
                'bulan' => $month,
                'tahun' => $request->tahun,
                'transaksi' => $order,
                ]);

            return $pdf->setPaper('a4', 'landscape')->stream();
        
    }

    public function paket_terlaris_report(Request $request){
        $paket = DB::table('paket_prasmanans')
                ->join('kategoris', 'paket_prasmanans.kategori_id','=','kategoris.id')
                ->select('kategoris.nama_kategori as kategori', 'paket_prasmanans.nama_paket as paket','paket_prasmanans.terjual as terjual','paket_prasmanans.satuan as satuan')
                ->where('paket_prasmanans.terjual','>',0)
                ->orderByDesc('terjual')
                ->limit(10)
                ->get();
        
            $pdf = Pdf::loadView('laporan-paket', [
                'paket' => $paket,
                ]);
            return $pdf->setPaper('a4', 'landscape')->stream();
        // }
        
    }

    public function customerReport(){
        $customerOrders = DB::table('orders')
                        ->join('users', 'orders.user_id','=','users.id')
                        ->select('users.name as name','users.email as email',DB::raw('count(*) as total_pemesanan'), DB::raw('sum(total) as total_biaya'))
                        ->where('status','Selesai')
                        ->groupBy('users.name','users.email')
                        ->get();
        $pdf = Pdf::loadView('laporan-pelanggan', [
                'customers' => $customerOrders,
               ]);
        return $pdf->setPaper('a4', 'landscape')->stream();
    }
}
