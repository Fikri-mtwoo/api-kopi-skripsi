<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class BayarController extends Controller
{
    //
    public function store(Request $request)
    {
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'string' => ':attribute harus string.',
            'numeric' => ':attribute sudah angka'
        ];
        $valid_data = Validator::make($request->all(), [
            'bayar' => 'required|numeric',
            'transaksi' => 'required|string',
        ], $custom_rules);

        if($valid_data->fails()){
            return response()->json([
                'kode' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'error' => $valid_data->errors(),
                'message' => 'Data tidak valid',
                'data' => ''
            ], 400);
        }

        try {
            $transaksi = Bayar::where('kode_transaksi', $request->input('transaksi'))->first();
            if($transaksi) {
                $transaksi->total_bayar = $request->input('bayar');
                $transaksi->status_bayar = 'settlement';
                $transaksi->save();
                return response()->json([
                    'kode' => Response::HTTP_CREATED,
                    'success' => true,
                    'error' =>'',
                    'message' => 'Data berhasil disimpan',
                    'data' => $transaksi
                ],200);
            }else{
                return response()->json([
                    'kode' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'error' =>'',
                    'message' => 'Data tidak ditemukan',
                    'data' => ''
                ],404);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_GATEWAY,
                'success' => false,
                'error' => $e,
                'message' => 'Data tidak valid',
                'data' => ''
            ], 502);
        }
    }
}
