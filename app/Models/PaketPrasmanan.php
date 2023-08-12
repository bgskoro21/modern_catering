<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketPrasmanan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function kategori(){
        return $this->belongsTo(Kategori::class);
    }

    public function menu_prasmanan(){
        return $this->belongsToMany(MenuPrasmanan::class,'paket_menu_olahan');
    }

    public function cart(){
        return $this->hasMany(Cart::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function paket_galleries(){
        return $this->hasMany(PaketGallery::class);
    }

    public function scopeSearch($query, $keyword){
        return $query->select('paket_prasmanans.id','kategoris.nama_kategori','paket_prasmanans.harga','paket_prasmanans.nama_paket','paket_prasmanans.gambar_paket')
                ->join('kategoris', 'paket_prasmanans.kategori_id','=','kategoris.id')
                ->where(function ($query) use ($keyword){
                    $query->where('kategoris.nama_kategori','LIKE',"%$keyword%")
                    ->orWhere('kategoris.description','LIKE',"%$keyword%")
                    ->orWhere('paket_prasmanans.nama_paket','LIKE',"%$keyword%")
                    ->orWhere('paket_prasmanans.description','LIKE',"%$keyword%")
                    ->where('is_release',1);
                    ;
                });
    }
}
