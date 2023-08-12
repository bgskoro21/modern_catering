<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function getAllInvoice(){
        $invoice = DB::table('invoices')->join('orders','invoices.order_id','=','orders.id')->select('invoices.no_invoice','orders.id','orders.tanggal_pemesanan','orders.nama_pemesan','orders.tanggal_acara','orders.total','orders.status')->orderBy('invoices.id','desc')->get();
        return response()->json([
            'status' => true,
            'invoice' => $invoice
        ]);
    }

    public function printInvoice($id){

        $invoice = Invoice::with((['order.transactions.paket_prasmanan.kategori']))->where('order_id',$id)->first();

        if($invoice){
           $pdf = Pdf::loadView('invoice',[
            'invoice' => $invoice
           ]);

        //    Simpan File PDF di Server
        $pdfPath = storage_path('app/public/pdf/'.$invoice->nama_pemesan.'_'.$invoice->no_invoice.'.pdf');
        file_put_contents($pdfPath, $pdf->output());
        //    Storage::put('public/pdf/epermit.pdf', $pdf->output());
        $fileName = $invoice->order->nama_pemesan.'_'.$invoice->no_invoice.'.pdf';
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        return response()->download($pdfPath,$invoice->order->nama_pemesan.'_'.$invoice->no_invoice.'.pdf',$headers);
        }
    }

    public function getInvoiceByOrderId(Request $request){
        $invoice = Invoice::with('order.transactions.paket_prasmanan.kategori')->where('order_id',$request->order_id)->first();
        if($invoice){
            return response()->json([
                'status' => true,
                'invoice' => $invoice
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invoice tidak ditemukan!'
            ],404);
        }
    }
}
