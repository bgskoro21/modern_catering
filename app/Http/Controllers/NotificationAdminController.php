<?php

namespace App\Http\Controllers;

use App\Models\NotificationAdmin;
use Illuminate\Http\Request;

class NotificationAdminController extends Controller
{
    public function getAdminNotification(){
        $notifikasi = NotificationAdmin::orderBy('updated_at','desc')->paginate(4);

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
        $amount = NotificationAdmin::where('status',0)->get()->count();
        return response()->json([
            'status' => true,
            'amount' => $amount
        ]);
    }

    public function setReadNotification(Request $request){
        $notifikasi = NotificationAdmin::find($request->order_id);
        $notifikasi->update([
            'status' => 1
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Notifikasi berhasil diubah!'
        ]);
    }
}
