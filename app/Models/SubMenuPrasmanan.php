<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMenuPrasmanan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function menu_prasmanan(){
        return $this->belongsTo(MenuPrasmanan::class);
    }
}
