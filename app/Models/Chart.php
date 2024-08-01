<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chart extends Model
{
    use HasFactory;
    protected $table = 'charts';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public function prod(){
        return $this->belongsTo(Produk::class, 'produk', 'id');
    }
}
