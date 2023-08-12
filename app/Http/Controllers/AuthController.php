<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;


class AuthController extends Controller
{
    public function login(Request $request){
        // mengambil email dan password
        $credentials = $request->only('email', 'password');

        try{
            // authentikasi credentials
            if(!$token = JWTAuth::claims(['user_type' => 'customer'])->attempt($credentials)){
                return response()->json([
                    'status' => false,
                    'message' => 'Email dan Password Salah!'
                ],400);
            }
        }
        // Jika jaringan tidak terhubung
        catch(JWTException $e){
            return response()->json([
                'status' => false,
                'message' => 'Tidak bisa membuat token'
            ], 500);
        }

        // jika credentials cocok dan berhasil autentikasi akan mengembalikan token JWT
        $user = User::with('cart.paket_prasmanan.kategori')->where('email', $request->email)->first();
        // Jika email belum diverifikasi
        if(!$user->hasVerifiedEmail()){
            return response()->json([
                'status' => false,
                'message' => 'Email belum diverifikasi!'
            ],403);
        }

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    public function register(Request $request){
        // membuat validasi form input request
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ]);

        // jika validasi gagal
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 400);
        }

        // insert data ke database
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ])->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'messages' => "User $request->name berhasil ditambahkan! Silahkan cek email anda untuk verifikasi!"
        ],201);
    }

    public function getAuthenticatedUser(){
        try{
            if(!$user = JWTAuth::parseToken()->authenticate()){
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

        $newUser = User::with('cart.paket_prasmanan.kategori')->find($user->id);

        return response()->json([
            'status' => true,
            'user' => $newUser
        ]);
    }

    public function verify($id, Request $request){
        if(!$request->hasValidSignature()){
            return response()->json([
                'status' => false,
                'messages' => 'Verifikasi email gagal!'
            ], 400);
        }

        $user = User::find($id);

        if(!$user->hasVerifiedEmail()){
            $user->markEmailAsVerified();
            return response()->json([
                'status' => true,
                'messages' => 'Verifikasi email berhasil!'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'messages' => 'Email sudah diverifikasi!'
            ], 200);
        }
    }

    public function notice(){
        return response()->json([
            'status' => false,
            'message' => 'Email belum diverifikasi!'
        ]);
    }

    public function resend(){
        if(JWTAuth::parseToken()->authenticate()->hasVerifiedEmail()){
            return response()->json([
                'status' => true,
                'messages' => 'Email anda sudah diverifikasi!'
            ], 200);
        }

        JWTAuth::parseToken()->authenticate()->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'messages' => 'Link verifikasi email sudah dikirim ke email anda. Silahkan cek email untuk melakukan verifikasi email!'
        ]);
    }

    public function logout(){
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
        if($removeToken){
            return response()->json([
                'status' => true,
                'messages' => 'Logout Berhasil!'
            ]);
        }
    }

    public function forgotPassword(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status == Password::RESET_LINK_SENT){
            return response()->json([
                'status' => true,
                'message' => 'Link Forgot Password sudah dikirim ke email anda!'
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)]
        ]);
    }

    public function reset(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required','confirmed'],
            'password_confirmation' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }
        

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user) use ($request){
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if($status == Password::PASSWORD_RESET){
            return response()->json([
                'status' => true,
                'messages' => 'Password reset successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'messages' => __($status)
        ]);
    }

    public function loginWithGoogle(){
        $redirectUrl = Socialite::driver('google')->redirect()->getTargetUrl();
        return response()->json([
            'redirectUrl' => $redirectUrl
        ],200);
    }

    public function handleGoogleCallback(Request $request){
        $code = $request->input('code');
        // return response()->json($code);
        $response = Socialite::driver('google')->getAccessTokenResponse($code);
        $accessToken = $response['access_token'];
        // Dapatkan data pengguna dari Google
        $googleUser = Socialite::driver('google')->stateless()->userFromToken($accessToken);

        // Cari pengguna berdasarkan email
        $user = User::where('email', $googleUser->email)->first();

        if(!$user){
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make('dummy-password'),
            ]);
            $user->markEmailAsVerified();
        }

        // Generate token JWT untuk pengguna
        $token = JWTAuth::claims(['user_type' => 'customer'])->fromUser($user);
        $newUser = User::with('cart.paket_prasmanan.kategori')->where('email', $googleUser->email)->first();

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $newUser
        ]);
    }
}
