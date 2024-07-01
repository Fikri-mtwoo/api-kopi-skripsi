<?php

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

Route::post('produk',[ProdukController::class, 'store']);
Route::post('produk/{id_produk}',[ProdukController::class, 'edit']);
Route::get('produk',[ProdukController::class, 'all']);
Route::get('produk/{id_produk}',[ProdukController::class, 'show']);
Route::delete('produk/{id_produk}',[ProdukController::class, 'destroy']);
Route::post('status/{id_produk}',[ProdukController::class, 'status']);

Route::post('transaksi',[TransaksiController::class, 'store']);