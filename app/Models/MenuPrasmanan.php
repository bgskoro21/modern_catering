<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPrasmanan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function paket_prasmanan(){
        return $this->belongsToMany(PaketPrasmanan::class,'paket_menu_olahan');
    }

    public function sub_menu_prasmanans(){
        return $this->hasMany(SubMenuPrasmanan::class);
    }
}
