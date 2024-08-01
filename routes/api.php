<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BayarController;
use App\Http\Controllers\Api\JenisProdukController;
use App\Http\Controllers\API\PaymenController;
use App\Http\Controllers\API\ProdukController;
use App\Http\Controllers\API\TransaksiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('registrasi',[AuthController::class, 'registrasi']);
Route::post('login',[AuthController::class, 'login']);
Route::post('verify-payment',[PaymenController::class, 'edit']);
Route::get('viewImg/{folder}/{fileName}', [ProdukController::class, 'viewImage']);

Route::group(['middleware' => 'auth:api'], function(){
    //end point produk
    Route::post('produk',[ProdukController::class, 'store']);
    Route::post('produk/{id_produk}',[ProdukController::class, 'edit']);
    Route::get('produk',[ProdukController::class, 'all']);
    Route::get('produk/{id_produk}',[ProdukController::class, 'show']);
    Route::delete('produk/{id_produk}',[ProdukController::class, 'destroy']);
    Route::post('status/{id_produk}',[ProdukController::class, 'status']);

    //end point jenis produk
    Route::get('jenis',[JenisProdukController::class, 'all']);

    //get list produk role pegawai
    Route::get('menu',[ProdukController::class, 'menu']);
    Route::post('chart',[ProdukController::class, 'chart']);
    Route::get('order',[ProdukController::class, 'order']);
    Route::get('list',[ProdukController::class, 'list']);
    
    //end point transaksi
    Route::post('transaksi',[TransaksiController::class, 'store']);
    Route::get('transaksi',[TransaksiController::class, 'all']);
    Route::get('transaksi/{kode_transaksi}',[TransaksiController::class, 'show']);

    //end point payment {midtrans}
    Route::post('payment',[PaymenController::class, 'store']);
    Route::post('payment/cencel',[PaymenController::class, 'destroy']);

    //end point bayar
    Route::post('bayar',[BayarController::class, 'store']);
    
    //end point authentikasi
    Route::post('logout',[AuthController::class, 'logout']);
});