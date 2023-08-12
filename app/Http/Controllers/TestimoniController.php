<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\PaketPrasmanan;
use App\Models\Testimoni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class TestimoniController extends Controller
{
    public function index(){
        $testimoni = Testimoni::with(['order.user'])->latest()->get();
        return response()->json([
            'status' => true,
            'testimoni' => $testimoni
        ],200);
    }
    
    public function getTestimoniAcc(){
        $testimoni = Testimoni::with(['order','user'])->where('status','terima')->get();
        $paket = PaketPrasmanan::with('kategori')->where('is_release',1)->where('is_andalan',1)->get();
        $banner = Banner::all();
        return response()->json([
            'status' => true,
            'testimoni' => $testimoni,
            'paket' => $paket,
            'banner' => $banner
        ],200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'nilai' => 'required',
            'message' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ],422);
        }
        Auth::setDefaultDriver('customer');

        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();
        
        $testimoni = Testimoni::create([
            'user_id' => $user->id,
            'order_id' => $request->order_id,
            'nilai' => $request->nilai,
            'message' => $request->message,
            'status' => 'tolak'
        ]);

        $order = Order::find($request->order_id);
        $order->update([
            'dinilai' => '1'
        ]);

        if($testimoni){
            return response()->json([
                'status' => true,
                'message' => 'Testimoni anda berhasil ditambahkan!'
            ],200);
        }else{
            return response()->json([
                'status' => true,
                'message' => 'Testimoni anda gagal ditambahkan!'
            ],200);
        }
    }

    public function update(Request $request, $id){
        Auth::setDefaultDriver('admin');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();
        $testimoni = Testimoni::find($id);
        if($testimoni){
            if($request->tipe === 'terima'){
                $tampilTesti = Testimoni::where('user_id',$user->id)->where('status','terima')->first();
                if($tampilTesti){
                    if($request->ok == 'ya'){
                        $tampilTesti->update(['status' => 'tolak']);
                        $testimoni->update([
                            'status' => 'terima'
                        ]);
                    }else{
                        $user = $tampilTesti->user;
                        return response()->json([
                            'status' => false,
                            'message' => "Testimoni $user->name sudah ada yang ditampilkan. Apakah kamu ingin mengganti testimoninya dengan ini?"
                        ],422);
                    }
                }else{
                    $testimoni->update([
                        'status' => 'terima'
                    ]);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Testimoni akan ditampilkan dihalaman pengguna'
                ],200);
            }else{
                $testimoni->update([
                    'status' => 'tolak'
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Testimoni tidak akan ditampilkan dihalaman pengguna'
                ],200);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Status testimoni gagal diubah!'
        ],422);
    }

    public function show(Request $request){
        $testimoni = Testimoni::with(['order.transactions.paket_prasmanan.kategori','user'])->where('order_id',$request->order_id)->first();
        return response()->json([
            'status' => true,
            'testimoni' => $testimoni,
        ]);
    }

    public function destroy($id){
        $testimoni = Testimoni::find($id);
        if($testimoni){
            $testimoni->delete();
            return response()->json([
                'status' => true,
                'message' => 'Testimoni berhasil dihapus!'
            ],200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Testimoni gagal dihapus!'
        ],422);
    }
}
