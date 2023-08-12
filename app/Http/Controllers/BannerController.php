<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function index(){
        $banner = Banner::latest()->get();
        return response()->json([
            'status' => true,
            'banner' => $banner
        ],200);
    }

    public function show($id){
        $banner = Banner::find($id);
        if($banner){
            return response()->json([
                'status' => true,
                'banner' => $banner,
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message'=>'Data banner tidak ditemukan!'
            ],404);
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'banner' => 'required|array'
        ],[
            'banner.required' => 'Banner harus diupload terlebih dahulu!'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ],422);
        }

        if($request->hasFile('banner')){
            $files = $request->file('banner');
            foreach($files as $file){
                $path = time().'_Banner'.random_int(1,1000).'.'.$file->getClientOriginalExtension();
                Storage::disk('local')->put('public/Banner/'.$path, file_get_contents($file));
                Banner::create([
                    'banner' => url('storage/Banner/'.$path)
                ]);
            }
            return response()->json([
                'status' => true,
                'message'=>'Data banner berhasil ditambahkan!'
            ],200);
        }

            return response()->json([
                'status' => false,
                'message'=>'Data banner gagal ditambahkan!'
            ],422);
    }

    public function update(Request $request, $id){
        $banner = Banner::find($id);
        $validator = Validator::make($request->all(),[
            'banner' => 'required|array'
        ],[
            'banner.required' => 'Banner harus diupload terlebih dahulu!'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ],422);
        }
        if($banner){
            $explode = explode('/',$banner->banner);
            Storage::delete('public/Banner/'.$explode[5]);
            $files = $request->file('banner');
            foreach($files as $file){
                $path = time().'_Banner'.random_int(1,1000).'.'.$file->getClientOriginalExtension();
                Storage::disk('local')->put('public/Banner/'.$path, file_get_contents($file));
                $banner->update(['banner' => url('storage/Banner/'.$path)]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Data banner berhasil diubah!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data banner gagal diubah!'
            ],200);
        }
    }

    public function destroy($id){
        $banner = Banner::find($id);
        if($banner){
            if($banner->banner){
                $explode = explode('/',$banner->banner);
                Storage::delete('public/Banner/'.$explode[5]);
            }
            $banner->delete();
            return response()->json([
                'status' => true,
                'message' => 'Data banner Berhasil Dihapus!'
            ],200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Data banner Gagal Dihapus!'
        ],200);
    }
}
