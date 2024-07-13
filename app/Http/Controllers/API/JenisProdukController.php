<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jenis;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JenisProdukController extends Controller
{
    //
    public function all()
    {
        try {
            $jenis = Jenis::all();
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data ditemukan',
                'data' => $jenis
            ],200);
        } catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'error' => $e,
                'message' => 'Data tidak ditemukan',
                'data' => ''
            ],400);
        }
    }
}
