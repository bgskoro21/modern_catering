<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function show($id){
        $user = User::find($id);

        if($user){
            return response()->json([
                'status' => true,
                'user' => $user,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan!'
            ], 200);
        }
    }

    public function updateProfile(Request $request){
        Auth::setDefaultDriver('customer');
        $token = JWTAuth::getToken();
        $user = Auth::setToken($token)->authenticate();

        if($user){
            if(request()->file('profile_picture')){
                if($user->profile_picture){
                    $explode = explode("/",$user->profile_picture);
                    Storage::delete('/public/profile_customer/'.$explode[5]);
                }
                $file = request()->file('profile_picture');
                $path= time().'_'.$request->name.'.'.$file->getClientOriginalExtension();
                Storage::disk('local')->put('public/profile_customer/'.$path, file_get_contents($file));
    
                $user->update([
                    'name' => $request->name,
                    'no_hp' => $request->no_hp,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'profile_picture' => url('storage/profile_customer/'.$path),
                    'tanggal_lahir' => $request->tanggal_lahir
                ]);
            }else{
                $user->update([
                    'name' => $request->name,
                    'no_hp' => $request->no_hp,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tanggal_lahir' => $request->tanggal_lahir
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data Profile Berhasil Diupdate!',
            ],200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Profile Gagal Diupdate!'
        ], 200);
    }

    public function changePassword(Request $request, $id){
        $user = User::find($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        if($user){
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Password Berhasil Diubah!'
            ],200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Password Gagal Diubah!'
        ]);
    }

    public function index(){
        $user = User::all();
        return response()->json([
            'status' => true,
            'pelanggan' => $user
        ]);
    }

    public function delete($id){
        $user = User::find($id);
        if($user){
            if($user->profile_picture){
                $explode = explode("/",$user->profile_picture);
                Storage::delete('/public/profile_customer/'.$explode[5]);
            }
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'Pelanggan berhasil dihapus!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Pelanggan gagal dihapus!'
            ], 403);
        }
    }
}
