<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function paket_prasmanan(){
        return $this->belongsTo(PaketPrasmanan::class);
    }
}
