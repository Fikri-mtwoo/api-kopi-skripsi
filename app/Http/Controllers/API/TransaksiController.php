<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bayar;
use App\Models\Chart;
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
            
            $pajak = 10;
            $bayar_pajak = ($total*$pajak)/100;
            $total = $total+$bayar_pajak;

            $data_bayar = [
                'kode_transaksi' => $kode_transaksi,
                'total_belanja' => $total,
                'pajak' => $pajak,
                'status_bayar' => 'created',
                'pemesan' => $request->input('pesan'),
                'id_pegawai' => auth()->user()->id
            ];
            $transaksi = Transaksi::insert($transaksis);
            $chart = Chart::where('user', auth()->user()->id)->delete();
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
    public function all(){
        try {
            $transaksi = Bayar::where('id_pegawai', auth()->user()->id)->orderBy('id', 'DESC')->get();
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data ditemukan',
                'data' => $transaksi
            ],200);
        } catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_GATEWAY,
                'success' => false,
                'error' => $e,
                'message' => 'Data tidak ditemukan',
                'data' => ''
            ],502);
        }
    }
    public function show($kode_transaksi){
        try {
            $transaksi = Transaksi::with('prod')->where('kode_transaksi', $kode_transaksi)->get();
            if($transaksi) {
                return response()->json([
                    'kode' => Response::HTTP_OK,
                    'success' => true,
                    'error' =>'',
                    'message' => 'Data ditemukan',
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
                'message' => 'Data tidak ditemukan',
                'data' => ''
            ],502);
        }
    }
    public function destroy(){}

    protected function generateInv($total){
        $nomor = str_shuffle(mt_rand(1000, 9999).mt_rand(1000, 9999));
        $inv = 'TR'.(str_pad((int)$total , 4, '0', STR_PAD_LEFT)).date('Y').$nomor;
        return $inv;
    }
}
