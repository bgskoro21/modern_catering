<?php

namespace App\Http\Controllers;

use App\Models\Konsultasi;
use App\Notifications\KonsultasiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KonsultasiController extends Controller
{
    public function index(){
        $konsultasi = Konsultasi::all();
        return response()->json([
            'status' => true,
            'konsultasi' => $konsultasi
        ],200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'nama' => 'required',
            'no_hp' => 'required|numeric',
            'pesan' => 'required'
        ]);
        
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ],422);
        }

        $tambah = Konsultasi::create([
            'name' => $request->nama,
            'no_hp' => $request->no_hp,
            'pesan' => $request->pesan
        ]);

        if($tambah){
            return response()->json([
                'status' => true,
                'message' => 'Pesan anda berhasil dikirim!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Pesan anda gagal dikirim!'
            ],422);
        }
    }

    public function adminReply(Request $request){
        $konsultasi = Konsultasi::find($request->id);
        if($konsultasi){
            $konsultasi->update([
                'status' => 'Dibalas'
            ]);
            $konsultasi->notify(new KonsultasiNotification($konsultasi, $request->message));
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mengirim pesan!'
            ],422);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim pesan!'
            ],422);
        }
    }
}
