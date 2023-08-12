<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardAdminController extends Controller
{
    public function index(){
        $pelanggan = User::all();
        $admin = Admin::all();
        $transaksiCount = Order::all()->count();
        $transaksi = Order::where('status','Diproses')->orWhere('status','Booked')->orWhere('status','selesai')->get();
        return response()->json([
            'status' => true,
            'pelanggan' => $pelanggan->count(),
            'admin' => $admin->count(),
            'transaksiCount' => $transaksiCount,
            'transaksi' => $transaksi
        ]);
    }
}
