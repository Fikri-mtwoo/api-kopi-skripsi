<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bayar extends Model
{
    use HasFactory;
    protected $table = 'bayars';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    // protected $hidden = ['id'];
}
