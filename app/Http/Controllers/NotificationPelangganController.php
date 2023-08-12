<?php

namespace App\Http\Controllers;

use App\Models\NotificationPelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationPelangganController extends Controller
{
    public function getNotificationByUser(){
        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();

        $notifikasi = NotificationPelanggan::where('user_id',$user->id)->orderBy('updated_at','desc')->paginate(3);
        
        foreach($notifikasi as $notif){
            $notif->update([
                'status' => 1
            ]);
        }

        return response()->json([
            'status' => true,
            'notifikasi' => $notifikasi
        ]);
    }

    public function getAmountNotification(){
        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();
        $amount = NotificationPelanggan::where('status',0)->where('user_id', $user->id)->get()->count();
        return response()->json([
            'status' => true,
            'amount' => $amount
        ]);
    }

    public function setReadNotification(){
        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();

        $notifikasi = NotificationPelanggan::where('user_id',$user->id)->get();
        foreach($notifikasi as $notif){
            $notif->update([
                'status' => 1
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => "Status notifikasi berhasil diubah!" 
        ]);
    }
}
