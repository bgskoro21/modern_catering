<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::with(['paket_prasmanan.kategori','user'])->get();
        return response()->json([
            'status' => true,
            'carts' => $cart
        ],200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'paket_prasmanan_id' => 'required',
            'amount' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();

        if($cart = Cart::where('user_id',$user->id)->where('paket_prasmanan_id', $request->paket_prasmanan_id)->first()){
            $cart->update([
                'user_id' => $user->id,
                'paket_prasmanan_id' => $request->paket_prasmanan_id,
                'amount' => $request->amount,
                'menu' => $request->menu,
                'total_harga' => $request->harga * $request->amount
            ]);
            return response()->json([
                'status' => true,
                'id' => $cart->id,
                'message' => 'Paket Berhasil Dimasukkan Ke Dalam Keranjang!'
            ],200);
        }else{
            $cart = Cart::create([
                'user_id' => $user->id,
                'paket_prasmanan_id' => $request->paket_prasmanan_id,
                'amount' => $request->amount,
                'menu' => $request->menu,
                'total_harga' => $request->harga * $request->amount
            ]);
            return response()->json([
                 'status' => true,
                 'id' => $cart->id,
                'message' => 'Paket Berhasil Dimasukkan Ke Dalam Keranjang!'
            ],200);
        }
    }

    public function showDetail(Request $request)
    {
        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();
        $packageIds = $request->input('packageIds');
        $cart = Cart::with(['user','paket_prasmanan.kategori'])->where('user_id', $user->id)->whereIn('id',$packageIds)->orderBy('updated_at','desc')->get();
            return response()->json([
                'status' => true,
                'cart' => $cart
            ],200);
    }

    public function update(Request $request, $id)
    {
        $cart = Cart::find($id);
        if($cart){
            $cart->update([
                'user_id' => $cart->user_id,
                'paket_prasmanan_id' => $cart->paket_prasmanan_id,
                'amount' => $request->amount,
                'menu' => $cart->menu,
                'total_harga' => $request->total_harga
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data Keranjang Berhasil Diupdate!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Keranjang Gagal Diupdate!'
            ],200);
        }
    }

    public function destroy($id)
    {
        $cart = Cart::find($id);

        if($cart){
            $cart->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data Keranjang Berhasil Dihapus!'
            ],200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Data Keranjang Gagal Dihapus!'
        ],200);
    }
}
