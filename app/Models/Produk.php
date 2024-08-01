<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produks';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    // protected $hidden = ['id'];

    public function jenis(){
        return $this->belongsTo(Jenis::class, 'jenis_produk', 'id');
    }

    public function chart(){
        return $this->hasOne(Chart::class);
    }

    public function transaksi(){
        return $this->hasOne(Transaksi::class);
    }
}
