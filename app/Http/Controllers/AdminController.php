<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index(){
        $admin = Admin::all();
        if($admin){
            return response()->json([
                'status' => true,
                'admin' => $admin
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada data admin!'
            ],404);
        }
    }

    public function destroy($id){
        $admin = Admin::find($id);
        if($admin){
            if($admin->profile_picture){
                $explode = explode("/",$admin->profile_picture);
                Storage::delete('/public/Profile/'.$explode[5]);
            }
            $admin->delete();
            return response()->json([
                'status' => true,
                'message' => 'Data Admin Berhasil Dihapus!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Admin Gagal Dihapus!'
            ],404);
        }
    }

    public function updateProfile(Request $request, $id){
        $admin = Admin::find($id);
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'profile_pic' => 'image|mimes:jpg,png,jpeg',
            'alamat' => 'required',
            'no_hp' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }

        if($admin){
            if($request->file('profile_pic')){
                $file = $request->file('profile_pic');
                $path = time().'_'.$admin->email.'.'.$file->getClientOriginalExtension();
                Storage::disk('local')->put('public/Profile/'.$path, file_get_contents($file));
                if($admin->profile_pic){
                    $explode = explode('/',$admin->profile_pic);
                    $gambar = $explode[5];
                    Storage::delete('public/Profile/'.$gambar);
                }
                $admin->update([
                    'name' => $request->name,
                    'profile_pic' => url('storage/Profile/'.$path),
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp
                ]);
            }else{
                $admin->update([
                    'name' => $request->name,
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Profil berhasil diupdate!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Profil gagal diupdate!'
            ]);
        }
    }

    public function deleteProfilePicture($id){
        $admin = Admin::find($id);
        if($admin){
            $gambar = explode('/', $admin->profile_pic)[5];
            Storage::delete('/public/Profile/'.$gambar);
            $admin->update([
                'profile_pic' => null
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Foto profil berhasil dihapus!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Foto profil gagal dihapus!'
            ]);
        }
    }

    public function changePassword(Request $request, $id){
        $admin = Admin::find($id);
        $validator = Validator::make($request->all(),[
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }
        
        if($admin){
            if(Hash::check($request->old_password, $admin->password)){
                $admin->update([
                    'password' => Hash::make($request->password)
                ]);
    
                return response()->json([
                    'status' => true,
                    'message' => 'Ganti password berhasil!'
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => 'Ganti password gagal! Password lama salah!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Ganti password gagal!'
            ]);
        }
    }

    public function show($id){
        $admin = Admin::find($id);
        if($admin){
            return response()->json([
                'status' => true,
                'admin' => $admin
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data admin tidak ditemukan!'
            ],404);
        }
    }
}
