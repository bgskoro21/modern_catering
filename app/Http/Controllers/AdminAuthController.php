<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AdminAuthController extends Controller
{
    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ],422);
        }
        $credentials = $request->only('email','password');
        Auth::setDefaultDriver('admin');
        try{
            if(!$token = Auth::claims(['user_type' => 'admin'])->attempt($credentials)){
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau Password Salah!'
                ], 200);
            }
        }catch(JWTException $e){
            return response()->json([
                'status' => false,
                'error' => 'Tidak bisa membuat token'
            ], 500);
        }
        $user = auth()->user();

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $user
        ],200);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|unique:admins,email',
            'password' => 'required|confirmed',
            'no_hp' => 'required|numeric',
            'alamat' => 'required',
            'password_confirmation' => 'required'
        ],[
            'name.required' => 'Nama harus diisi!',
            'email.required' => 'Email harus diisi!',
            'email.unique' => 'Email sudah digunakan!', 
            'password.required' => 'Password harus diisi!',
            'password.confirmed' => 'Password tidak cocok dengan konfirmasi password!',
            'no_hp.required' => 'Nomor HP harus diisi!',
            'no_hp.numeric' => 'Nomor HP harus berupa angka!',
            'alamat.required' => 'Alamat harus diisi!',
            'password_confirmation.required' => 'Konfirmasi password harus diisi!'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }

        $register = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'password' => Hash::make($request->password),
        ]);

        if($register){
            return response()->json([
                'status' => true,
                'message' => 'Data User Berhasil Ditambahkan!'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data User Gagal Ditambahkan!'
            ],200);
        }
    }

    public function getAdminAuth(){
        // $token = JWTAuth::getToken();
        Auth::setDefaultDriver('admin');
        // var_dump(config('auth.defaults'));die;
        $token = JWTAuth::getToken();
        // var_dump($token);die;
        try{
            if(!$user = Auth::setToken($token)->authenticate()){
                return response()->json(['error' => 'user not found!'], 404);
            }
        }catch(TokenExpiredException $e){
            return response()->json([
                'status' => false,
                'token_expired'], $e->getCode());
        }catch(TokenInvalidException $e){
            return response()->json([
                'status' => false,
                'token_invalid'], $e->getCode());
        }catch(JWTException $e){
            return response()->json([
                'status' => false,
                'token_absent'], $e->getCode());
        }

        return response()->json([
            'status' => true,
            'user' => $user
        ]);
    }

    public function logout(){
        $removeToken = Auth::invalidate(JWTAuth::getToken());
        // var_dump($removeToken);
        if($removeToken){
            return response()->json([
                'status' => true,
                'messages' => 'Logout Berhasil!'
            ]);
        }
    }
}
