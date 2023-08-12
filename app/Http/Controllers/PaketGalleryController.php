<?php

namespace App\Http\Controllers;

use App\Models\PaketGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaketGalleryController extends Controller
{
    public function index(Request $request){
        $gallery = PaketGallery::where('paket_prasmanan_id',$request->paket_id)->get();
        return response()->json([
            'status' => true,
            'gallery' => $gallery
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'gallery' => 'required|array'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }

        if($request->hasFile('gallery')){
            $gambar = $request->file('gallery');
            foreach($gambar as $gam){
                $path = time().'_PaketGallery'.random_int(1,1000).'.'.$gam->getClientOriginalExtension();
                Storage::disk('local')->put('public/gallery/'.$path, file_get_contents($gam));
                PaketGallery::create([
                    'paket_prasmanan_id' => $request->paket_id,
                    'gambar' => url('/storage/gallery/'.$path)
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Galeri paket berhasil ditambahkan!'
            ],200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Galeri paket gagal ditambahkan!'
        ],404);

    }

    public function show($id){
        $gallery = PaketGallery::find($id);

        if($gallery){
            return response()->json([
            'status' => true,
            'gallery' => $gallery
            ],200);
        }else{
            return response()->json([
                'staturs' => false,
                'message' => 'Galeri paket tidak ditemukan!'
            ],404);
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(),[
            'gallery' => 'required|array'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }
        $gallery = PaketGallery::find($id);
        if($gallery){
            $explode = explode('/',$gallery->gambar)[5];
            Storage::delete('public/gallery/'.$explode);
            $files = $request->file('gallery');
            foreach($files as $file){
                $path = time().'_PaketGallery'.random_int(1,1000).'.'.$file->getClientOriginalExtension();
                Storage::disk('local')->put('public/gallery/'.$path, file_get_contents($file));
                $gallery->update(['gambar' => url('storage/gallery/'.$path)]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Galeri paket berhasil diubah!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Galeri paket gagal diubah!'
            ],404);
        }
    }

    public function delete($id){
        $gallery = PaketGallery::find($id);
        if($gallery){
            $explode = explode('/',$gallery->gambar)[5];
            Storage::delete('public/gallery/'.$explode);
            $gallery->delete();
            return response()->json([
                'status' => true,
                'message' => 'Galeri paket berhasil dihapus!'
            ],200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Galeri paket gagal dihapus!'
        ],404);
    }
}
