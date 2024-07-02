<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bayar;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TransaksiController extends Controller
{
    public function store(Request $request){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'array' => ':attribute harus type array',
            'string' => ':attribute harus string.',
        ];
        $valid_data = Validator::make($request->all(), [
            'produk' => 'required|array',
            'qty' => 'required|array',
            'pesan' => 'required|string',
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
            $total = 0;
            $kode_transaksi = $this->generateInv(count($request->input('produk')));
            foreach ($request->input('produk') as $key => $value) {
                $transaksis[] = array(
                    'kode_transaksi' => $kode_transaksi,
                    'id_produk' => $request->input('produk')[$key],
                    'qty' => $request->input('qty')[$key],
                    'tgl_transaksi' => date('Y-m-d'),
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s')
                );
                $produk = Produk::select('harga_produk')->where('id', $request->input('produk')[$key])->first();
                $total += $produk->harga_produk*$request->input('qty')[$key];
            }
            
            $data_bayar = [
                'kode_transaksi' => $kode_transaksi,
                'total_belanja' => $total,
                'status_bayar' => 'CREATED',
                'pemesan' => $request->input('pesan'),
                'id_pegawai' => 1
            ];
            $transaksi = Transaksi::insert($transaksis);
            $bayar = Bayar::create($data_bayar);
            return response()->json([
                'kode' => Response::HTTP_CREATED,
                'success' => true,
                'error' =>'',
                'message' => 'Data berhasil disimpan',
                'data' => $bayar
            ],200);
        } catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'error' => $e,
                'message' => 'Data tidak valid',
                'data' => ''
            ], 400);
        }
    }
    public function edit(){}
    public function all(){}
    public function show(){}
    public function destroy(){}

    protected function generateInv($total){
        $nomor = str_shuffle(mt_rand(1000, 9999).mt_rand(1000, 9999));
        $inv = 'TR'.(str_pad((int)$total , 4, '0', STR_PAD_LEFT)).date('Y').$nomor;
        return $inv;
    }
}
