<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    use HasFactory;
    protected $table = 'jeniss';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function produk(){
        return $this->hasMany(Produk::class);
    }
}
