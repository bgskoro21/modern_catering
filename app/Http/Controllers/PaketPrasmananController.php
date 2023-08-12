<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\MenuPrasmanan;
use App\Models\PaketGallery;
use App\Models\PaketPrasmanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaketPrasmananController extends Controller
{
    public function index()
    {
        $paket = PaketPrasmanan::with('kategori')->get();
        return response()->json([
            'status' => true,
            'paket' => $paket
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'kategori_id' => 'required',
            'nama_paket' => 'required|max:255',
            'harga' => 'required|numeric|min:1',
            'gambar_paket' => 'required|image|max:4096|mimes:jpeg,png,jpg',
            'min_order' => 'required|numeric',
            'satuan' => 'required',
            'description' => 'required|max:1000'
        ],[
            'kategori_id.required' => 'Kategori harus diisi terlebih dahulu!',
            'nama_paket.required' => 'Nama paket harus diiisi!',
            'nama_paket.max' => 'Ukuran nama paket hanya sampai 255 karakter!',
            'harga.required' => 'Harga paket harus diisi!',
            'harga.numeric' => 'Harga paket harus berupa angka!',
            'harga.min' => 'Harga paket tidak boleh 0!',
            'gambar_paket.required' => 'Gambar paket harus dimasukkan terlebih dahulu!',
            'gambar_paket.image' => 'File yang diupload harus berupa gambar!',
            'gambar_paket.max' => 'Ukuran gambar maksimal 4MB!',
            'gambar_paket.mimes' => 'Jenis file yang diupload harus berupa jpg, png, atau jpeg',
            'description.required' => 'Deskripsi paket harus diiisi!',
            'description.max' => 'Ukuran deskripsi paket maksimal 1000 kata atau karakter!',
            'min_order.required' => "Isi minimal order terlebih dahulu!",
            'min_order.numeric' => 'Isi minimal order dengan bilangan atau angka!',
            'satuan' => 'Satuan harus diisi!'
        ]
    );

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }

        $file = $request->file('gambar_paket');
        $path = time().'_'.$request->nama_paket.'.'.$file->getClientOriginalExtension();
        Storage::disk('local')->put('public/Paket/'.$path, file_get_contents($file));

        $tambah = PaketPrasmanan::create([
            'kategori_id' => $request->kategori_id,
            'nama_paket' => $request->nama_paket,
            'harga' => $request->harga,
            'description' => $request->description,
            'gambar_paket' => url('storage/Paket/'.$path),
            'satuan' => $request->satuan,
            'min_order' => $request->min_order
        ]);

        PaketGallery::create([
            'paket_prasmanan_id' => $tambah->id,
            'gambar' => url('storage/Paket/'.$path),
            'is_default' => true
        ]);

        if($tambah){
            return response()->json([
                'status' => true,
                'message' => 'Data Paket Prasmanan Berhasil Ditambahkan!'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Paket Prasmanan Gagal Ditambahkan!'
            ]);
        }
    }

    public function show($id)
    {
        $paket = PaketPrasmanan::with(['kategori','menu_prasmanan.sub_menu_prasmanans','paket_galleries'])->find($id);
        if($paket){
            return response()->json([
                'status' => true,
                'paket' => $paket,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Paket Prasmanan Tidak Ditemukan!'
            ]);
        }
    }

    public function updatePaket(Request $request, $id)
    {
        $paket = PaketPrasmanan::find($id);
        $gallery = PaketGallery::where('paket_prasmanan_id',$id)->where('is_default',1)->first();

        $validator = Validator::make($request->all(),[
            'kategori_id' => 'required',
            'nama_paket' => 'required|max:255',
            'harga' => 'required|numeric|min:1',
            'gambar_paket' => 'image|max:4096|mimes:jpeg,png,jpg',
            'description' => 'required|max:1000',
            'min_order' => 'required|numeric',
            'satuan' => 'required',
        ],[
            'kategori_id.required' => 'Kategori harus diisi terlebih dahulu!',
            'nama_paket.required' => 'Nama paket harus diiisi!',
            'nama_paket.max' => 'Ukuran nama paket hanya sampai 255 karakter!',
            'harga.required' => 'Harga paket harus diisi!',
            'harga.numeric' => 'Harga paket harus berupa angka!',
            'harga.min' => 'Harga paket tidak boleh 0!',
            'gambar_paket.image' => 'File yang diupload harus berupa gambar!',
            'gambar_paket.max' => 'Ukuran gambar maksimal 4MB!',
            'gambar_paket.mimes' => 'Jenis file yang diupload harus berupa jpg, png, atau jpeg',
            'description.required' => 'Deskripsi paket harus diiisi!',
            'description.max' => 'Ukuran deskripsi paket maksimal 1000 kata atau karakter!',
            'min_order.required' => "Isi minimal order terlebih dahulu!",
            'min_order.numeric' => 'Isi minimal order dengan bilangan atau angka!',
        ]
    );

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }

        if($paket){
            if($request->file('gambar_paket')){
                $explode = explode('/',$paket->gambar_paket);
                Storage::delete('public/Paket/'.$explode[5]);
                $file = $request->file('gambar_paket');
                $path = time().'_'.$request->nama_paket.'.'.$file->getClientOriginalExtension();
                Storage::disk('local')->put('public/Paket/'.$path, file_get_contents($file));
    
                $paket->update([
                    'kategori_id' => $request->kategori_id,
                    'nama_paket' => $request->nama_paket,
                    'harga' => $request->harga,
                    'description' => $request->description,
                    'gambar_paket' => url('storage/Paket/'.$path),
                    'satuan' => $request->satuan,
                    'min_order' => $request->min_order
                ]);

                $gallery->update([
                    'gambar' => url('storage/Paket/'.$path)
                ]);
            }else{
                $paket->update([
                    'kategori_id' => $request->kategori_id,
                    'nama_paket' => $request->nama_paket,
                    'description' => $request->description,
                    'harga' => $request->harga,
                    'satuan' => $request->satuan,
                    'min_order' => $request->min_order
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Paket Prasmanan Gagal Diupdate!'
            ]);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Data Paket Prasmanan Berhasil Diupdate!'
        ]);
    }

    public function destroy($id)
    {
        $paket = PaketPrasmanan::find($id);

        if($paket){
            $explode = explode('/',$paket->gambar_paket);
            $gambar = $explode[5];
            Storage::delete('public/Paket/'.$gambar);
            $paket->delete();
            return response()->json([
                'status' => true,
                'message' => 'Data Paket Prasmanan Berhasil Dihapus!'
            ]);
        }else{
            return response()->json([
                'status' => true,
                'message' => 'Data Paket Prasmanan Gagal Dihapus! Data Tidak Ditemukan!'
            ]);
        }
    }

    public function setPaketAndalan($id){
        $paket = PaketPrasmanan::find($id);
        if($paket){
            $paket->update([
                'is_andalan' => $paket->is_andalan ? 0 : 1,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Status andalan paket atau menu berhasil diubah!'
            ],200);
        }
        return response()->json([
            'status' => true,
            'message' => 'Status andalan paket atau menu gagal diubah!'
        ],200);
    }

    public function setPaketRelease($id){
        $paket = PaketPrasmanan::find($id);
        if($paket){
            $paket->update([
                'is_release' => $paket->is_release ? 0 : 1,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Status release paket atau menu berhasil diubah!'
            ],200);
        }
        return response()->json([
            'status' => true,
            'message' => 'Status release paket atau menu gagal diubah!'
        ],200);
    }

    public function getPaketReleased(){
        $kategoris = Kategori::leftJoin('paket_prasmanans','kategoris.id','=','paket_prasmanans.kategori_id')
                    ->select('kategoris.*',DB::raw('COUNT(paket_prasmanans.id) as jumlah_paket'))
                    ->where('paket_prasmanans.is_release',1)
                    ->groupBy('kategoris.nama_kategori')
                    ->get();
        $paket = PaketPrasmanan::with('kategori')->where('is_release',1)->get();
        return response()->json([
            'status' => true,
            'paket' => $paket,
            'kategori' => $kategoris
        ],200);
    }

    public function showPaketMenu($id){
        $paket = DB::table('paket_menu_olahan')
                ->join('paket_prasmanans','paket_menu_olahan.paket_prasmanan_id','=','paket_prasmanans.id')
                ->join('menu_prasmanans','paket_menu_olahan.menu_prasmanan_id','=','menu_prasmanans.id')
                ->select('paket_menu_olahan.id','paket_prasmanans.nama_paket','paket_prasmanans.id as id_paket','menu_prasmanans.menu','menu_prasmanans.id as id_menu')
                ->where('paket_prasmanans.id',$id)
                ->get();
        if($paket){
            return response()->json([
                'status' => true,
                'paket_menu' => $paket
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Paket Menu Tidak Ditemukan!'
            ],404);
        }
    }

    public function insertMenu(Request $request, $id){
        $paket = PaketPrasmanan::find($id);

        $validator = Validator::make($request->all(),[
            'menu_id' => 'required'
        ],[
            'menu_id.required' => 'Menu olahan harus diisi!',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }

        $rows = $request->input('menu_id');

        $relasi = $paket->menu_prasmanan()->whereIn('menu_prasmanan_id', $rows)->pluck('menu_prasmanan_id')->toArray();
        // return response()->json(count($relasi));
        if(count($relasi) > 0){
            return response()->json([
                'status' => false,
                'existing' => 'Paket sudah memiliki beberapa menu yang anda inputkan! Silahkan cek kembali!'
            ]);
        }
        
        $addedMenus = array_diff($rows,$relasi);
        
        if(count($addedMenus) > 0){
                $paket->menu_prasmanan()->attach($addedMenus);
            return response()->json([
                'status' => true,
                'message' => 'Menu Berhasil Ditambahkan!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'existing' => 'Paket sudah memiliki semua menu yang anda inputkan! Silahkan cek kembali!'
            ],404);
        }
    }

    public function updateMenu(Request $request, $id){
        $paket = PaketPrasmanan::find($id);
        $validator = Validator::make($request->all(),[
            'menu_id' => 'required|unique:paket_menu_olahan,menu_prasmanan_id'
        ],[
            'menu_id.required' => 'Menu olahan harus diisi!',
            'menu_id.unique' => 'Menu olahan tidak boleh sama!'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ]);
        }

        if($paket){
            $paket->menu_prasmanan()->updateExistingPivot($request->menu_id_lama, ['menu_prasmanan_id' => $request->menu_id]);
            return response()->json([
                'status' => true,
                'message' => 'Menu Berhasil Dirubah!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Menu Gagal Dirubah!'
            ],404);
        }
    }

    public function deleteMenu(Request $request, $id){
        $paket = PaketPrasmanan::find($id);
        if($paket){
            $paket->menu_prasmanan()->detach($request->menu_id);
            return response()->json([
                'status' => true,
                'message' => 'Menu Berhasil Dihapus!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Menu Gagal Dihapus!'
            ],404);
        }
    }

    public function search(Request $request){
        if(empty($request->keyword)){
            $paket = [];
        }else{
            $paket = PaketPrasmanan::with('kategori')->search($request->keyword)->get();
        }
        return response()->json([
            'status' => true,
            'paket' => $paket,
        ]);
    }
}
