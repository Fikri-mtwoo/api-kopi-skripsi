<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bayar;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PaymenController extends Controller
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
            $bayar = Bayar::where('kode_transaksi', $request->input('transaksi'))->first();
            if(!$bayar){
                return response()->json([
                    'kode' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'error' => '',
                    'message' => 'Data tidak ditemukan',
                    'data' => ''
                ], 404);
            }
            $respons = Http::withHeaders([
                'Accept'=> 'application/json',
                'Content-Type'=> 'application/json'
            ])->withBasicAuth(config('payment.server_key'), '')->post('https://api.sandbox.midtrans.com/v2/charge', [
                "payment_type"=> "gopay",
                "transaction_details" => [
                    "order_id"=> $bayar->kode_transaksi,
                    "gross_amount"=> $bayar->total_belanja
                ]
                ]);
            if ($respons->status() == 200 ) {
                $actions = $respons->json('actions');
                Log::info('payment', $actions);
                if(empty($actions)){
                    return response()->json([
                        'kode' => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'success' => false,
                        'error' => '',
                        'message' => $respons['status_message'],
                        'data' => ''
                    ], 500);
                }
                $bayar->url_bayar = $actions[0]["url"];
                $bayar->save();
                $kode = explode('/', $actions[0]["url"]);
                // $respons['status_message']
                return response()->json([
                    'kode' => Response::HTTP_CREATED,
                    'success' => true,
                    'error' =>'',
                    'message' => "Qr berhasil dibuat",
                    'data' => ["kode" => $kode[5]]
                ],200);
            }
            return response()->json([
                'kode' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'error' => '',
                'message' => $respons['status_message'],
                'data' => ''
            ], 500);
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

    public function edit(Request $request)
    {
        Log::info('incoming-notification', $request->all());
        $order_id = $request->order_id;
        $status_code = $request->status_code;
        $gross_amount = $request->gross_amount;

        $signature = hash('sha512', $order_id.$status_code.$gross_amount.config('payment.server_key'));
        if ($signature != $request->signature_key) {
            Log::info('signature', $signature.'['.$request->signature_key.']');
        }
        $bayar = Bayar::where('kode_transaksi', $order_id)->first();
        if ($bayar){
            $bayar->status_bayar = $request->transaction_status;
            if($request->transaction_status == 'cencel' OR $request->transaction_status == 'deny' OR $request->transaction_status == 'expire' OR $request->transaction_status == 'failure') {
                $bayar->total_bayar = null;
            }else if($request->transaction_status == 'capture' OR $request->transaction_status == 'settlement'){
                $bayar->total_bayar = $request->gross_amount;
            }
            $bayar->save();
        }
    }

    public function destroy(Request $request){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'string' => ':attribute harus string.',
            'numeric' => ':attribute sudah angka'
        ];
        $valid_data = Validator::make($request->all(), [
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
                $kode = explode('/', $transaksi->url_bayar);
                $urlCencel = "https://api.sandbox.midtrans.com/v2/".$kode[5]."/cancel";
                $respons = Http::withHeaders([
                    'Accept'=> 'application/json',
                    'Content-Type'=> 'application/json'
                ])->withBasicAuth(config('payment.server_key'), '')->post($urlCencel);
                if ($respons->status() == 200 ) {
                    $transaksi->url_bayar = null;
                    $transaksi->save();
                    Log::info('cencel-payment', $respons->json());
                    return response()->json([
                        'kode' => Response::HTTP_OK,
                        'success' => true,
                        'error' =>'',
                        'message' => "Payment berhasil dibatalkan",
                        'data' => ''
                    ],200);
                }
                return response()->json([
                    'kode' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'success' => false,
                    'error' => '',
                    'message' => "Payment gagal dibatalkan",
                    'data' => ''
                ], 500);
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
}
