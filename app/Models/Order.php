<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function testimonis(){
        return $this->hasMany(Testimoni::class);
    }

    public function alamat_pelanggan(){
        return $this->belongsTo(AlamatPelanggan::class);
    }

    public function invoice(){
        return $this->hasOne(Invoice::class);
    }
}
