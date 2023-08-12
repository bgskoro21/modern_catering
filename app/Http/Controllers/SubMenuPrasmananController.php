<?php

namespace App\Http\Controllers;

use App\Models\SubMenuPrasmanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubMenuPrasmananController extends Controller
{
    public function index()
    {
        $sub = SubMenuPrasmanan::with('menu_prasmanan')->get();
        return response()->json([
            'status' => true,
            'sub_menu' => $sub
        ],200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_prasmanan_id' => 'required|numeric',
            'sub_menu' => 'required|array'
        ],[
            'menu_prasmanan_id' => 'Menu prasmanan harus diisi terlebih dahulu!',
            'sub_menu.required' => 'Submenu prasmanan harus diisi!',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }

        $subMenu = $request->input('sub_menu');

        foreach($subMenu as $sub){
            SubMenuPrasmanan::create([
                'menu_prasmanan_id' => $request->menu_prasmanan_id,
                'sub_menu' => $sub
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Sub Menu Berhasil Ditambahkan!'
        ], 200);
    }

    public function show($id)
    {
        $sub = SubMenuPrasmanan::find($id);
        if($sub){
            return response()->json([
                'status' => true,
                'sub_menu' => $sub
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Data Sub Menu Tidak Ditemukan!'
            ],200);

        }
    }

    public function update(Request $request, $id)
    {
        $sub = SubMenuPrasmanan::find($id);
        $validator = Validator::make($request->all(), [
            'menu_prasmanan_id' => 'required|numeric|not_in:0',
            'sub_menu' => 'required|array'
        ],[
            'menu_prasmanan_id' => 'Menu prasmanan harus diisi terlebih dahulu!',
            'sub_menu.required' => 'Submenu prasmanan harus diisi!',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'form_validation' => true,
                'message' => $validator->errors()
            ], 422);
        }

        if($sub){
            $sub->update([
                'menu_prasmanan_id' => $request->menu_prasmanan_id,
                'sub_menu' => $request->sub_menu
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data Sub Menu Berhasil Diupdate!'
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Sub Menu Gagal Diupdate!'
        ]);


    }

    public function destroy($id)
    {
        $sub = SubMenuPrasmanan::find($id);
        if($sub){
            $sub->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data Sub Menu Berhasil Dihapus!'
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data Sub Menu Gagal Diupdate!'
        ], 200);
    }
}
