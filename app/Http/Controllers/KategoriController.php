<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::orderBy('nama_kategori','asc')->get();
        return response()->json([
            'status' => true,
            'kategori' => $kategori
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|max:255',
            'description' => 'required|max:1000',
        ],[
            'nama_kategori.required' => 'Nama kategori harus diisi!',
            'description.required' => 'Deskripsi kategori harus diisi!',
            'description.min' => 'Deskripsi kategori diisi minimal 100 karakter!',
            'description.max' => 'Ukuran deskripsi kategori tidak boleh melebihi 1000 karakter!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }

        $data = [
                'nama_kategori' => $request->nama_kategori,
                'description' => $request->description,
            ];

        $tambah = Kategori::create($data);

        if($tambah){
            return response()->json([
                'status' => true,
                'message' => 'Data Kategori Berhasil Ditambahkan!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Kategori Gagal Ditambahkan!'
            ]);
        }

    }

    public function show($id)
    {
        $kategori = Kategori::with('paket_prasmanan')->find($id);
        if($kategori) {
            // var_dump($kategori);die;
            return response()->json([
                'status' => true,
                'kategori' => $kategori,
                // 'paket_prasmanan' => $kategori->paket_prasmanan
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => "Data nomor $id tidak ditemukan!"
            ]);

        }
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::find($id);
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|max:255',
            'description' => 'required|max:1000',
        ],[
            'nama_kategori.required' => 'Nama kategori harus diisi!',
            'description.required' => 'Deskripsi kategori harus diisi!',
            'description.min' => 'Deskripsi kategori diisi minimal 100 karakter!',
            'description.max' => 'Ukuran deskripsi kategori tidak boleh melebihi 1000 karakter!',
        ]);

        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }


        if($kategori){
            $data = [
                    'nama_kategori' => $request->nama_kategori,
                    'description' => $request->description,
            ];
            $kategori->update($data);
            return response()->json([
                'status' => true,
                'message' => 'Data Kategori Berhasil Diupdate!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Kategori Tidak Ditemukan!'
            ]);
        }


    }

    public function destroy($id)
    {
        $kategori = Kategori::find($id);
        if($kategori){
            $kategori->delete();
            return response()->json([
                'status' => true,
                'message' => 'Data Kategori Berhasil Dihapus!'
            ],200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Data Kategori Gagal Dihapus!'
        ],200);
    }
}
