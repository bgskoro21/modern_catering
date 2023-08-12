<?php

namespace App\Http\Controllers;

use App\Models\MenuPrasmanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuPrasmananController extends Controller
{

    public function index()
    {
        $menu = MenuPrasmanan::all();
        return response()->json([
            'status' => true,
            'menu' => $menu
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu' => 'required|array|unique:menu_prasmanans,menu'
        ],[
            'menu.unique' => 'Menu sudah ada di dalam database!',
            'menu.required' => 'Menu harus diisi!'
        ]);
        
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }
        $menus = $request->input('menu');
        
        foreach($menus as $menu){
            MenuPrasmanan::create([
                'menu' => $menu
            ]);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Data Menu Berhasil Ditambahkan!'
        ],200);
        // if($tambah){
        // }else{
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Data Menu Gagal Ditambahkan!'
        //     ],200);
        // }
    }

    public function show($id)
    {
        $menu = MenuPrasmanan::with('sub_menu_prasmanans')->find($id);
        if($menu){
            return response()->json([
                'status' => true,
                'menu' => $menu
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Menu Tidak Ditemukan!'
            ],200);
        }
    }

    public function update(Request $request, $id)
    {
        $menu = MenuPrasmanan::find($id);

        $validator = Validator::make($request->all(), [
            'menu' => 'required|unique:menu_prasmanans,menu'
        ],[
            'menu.unique' => 'Menu sudah ada di dalam database!',
            'menu.required' => 'Menu harus diisi!'
        ]);
        
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }

        $update = $menu->update([
            'menu' => $request->menu,
        ]);

        if($update){
            return response()->json([
                'status' => true,
                'message' => 'Data Menu Berhasil Diupdate!'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Menu Gagal Diupdate!'
            ],200);
        }


    }

    public function destroy($id)
    {
        $menu = MenuPrasmanan::find($id);

        $hapus = $menu->delete();

        if($hapus){
            return response()->json([
                'status' => true,
                'message' => 'Data Menu Berhasil Dihapus!'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Menu Gagal Dihapus!'
            ],200);

        }
    }
}
