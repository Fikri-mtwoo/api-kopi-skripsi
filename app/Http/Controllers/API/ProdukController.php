<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ProdukController extends Controller
{
    //
    public function store(Request $request){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'string' => ':attribute harus string.',
            'numeric' => ':attribute sudah angka'
        ];
        $valid_data = Validator::make($request->all(), [
            'nama' => 'required|string',
            'harga' => 'required|numeric',
            'desc' => 'required|string',
            'img' => 'required|mimes:pdf,jpg,jpeg,png|max:2048'
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
            if($request->file('img')){
                $path = $request->file('img')->store('img');
                $pathImg = Storage::path($path);
                $data = [
                    'nama_produk' => $request->input('nama'),
                    'harga_produk' => $request->input('harga'),
                    'desc_produk' => $request->input('desc'),
                    'img_produk' => $pathImg,
                    'status_produk' => 'T'
                ];
    
                $produk = Produk::create($data);
                return response()->json([
                    'kode' => Response::HTTP_CREATED,
                    'success' => true,
                    'error' =>'',
                    'message' => 'Data berhasil disimpan',
                    'data' => $produk
                ],200);
            }
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

    public function show($id_produk){
        try {
            $produk = Produk::where('id', $id_produk)->first();
            if($produk) {
                return response()->json([
                    'kode' => Response::HTTP_OK,
                    'success' => true,
                    'error' =>'',
                    'message' => 'Data ditemukan',
                    'data' => $produk
                ],200);
            }else{
                return response()->json([
                    'kode' => Response::HTTP_OK,
                    'success' => false,
                    'error' =>'',
                    'message' => 'Data tidak ditemukan',
                    'data' => ''
                ],200);
            }
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

    public function edit(Request $request, $id_produk){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'string' => ':attribute harus string.',
            'numeric' => ':attribute sudah angka'
        ];
        $valid_data = Validator::make($request->all(), [
            'nama' => 'required|string',
            'harga' => 'required|numeric',
            'desc' => 'required|string',
            'img' => 'required|mimes:pdf,jpg,jpeg,png|max:2048'
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
            $produk = Produk::where('id', $id_produk)->first();
            if (!$produk) {
                return response()->json([
                    'kode' => Response::HTTP_NO_CONTENT,
                    'success' => false,
                    'error' => '',
                    'message' => 'Data tidak ditemukan',
                    'data' => ''
                ], 400);
            }
            $file = explode('/', $produk->img_produk);
            $tempValue = 'img/'.$file[1];
            if($request->file('img')){
                $path = $request->file('img')->store('img');
                $pathImg = Storage::path($path);

                //update file
                $produk->nama_produk = $request->input('nama');
                $produk->harga_produk = $request->input('harga');
                $produk->desc_produk = $request->input('desc');
                $produk->img_produk = $pathImg;
                $produk->save();

                //hapus file lama
                Storage::delete($tempValue);

                return response()->json([
                    'kode' => Response::HTTP_OK,
                    'success' => true,
                    'error' =>'',
                    'message' => 'Data berhasil dirubah',
                    'data' => $produk
                ],200);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'error' => $e,
                'message' => 'Data gagal dirubah',
                'data' => ''
            ], 400);
        }
    }

    public function all(){
        try {
            $produk = Produk::all();
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data ditemukan',
                'data' => $produk
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

    public function destroy($id_produk){

        try {
            $produk = Produk::where('id', $id_produk)->first();
            if (!$produk) {
                return response()->json([
                    'kode' => Response::HTTP_NO_CONTENT,
                    'success' => false,
                    'error' => '',
                    'message' => 'Data tidak ditemukan',
                    'data' => ''
                ], 400);
            }

            //hapus data
            $file = explode('/', $produk->img_produk);
            $tempValue = 'img/'.$file[1];
            $produk->delete();

            //hapus file lama
            Storage::delete($tempValue);
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data berhasil dihapus',
                'data' => $produk
            ],200);
        } catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'error' => $e,
                'message' => 'Data gagal dihapus',
                'data' => ''
            ],400);
        }
    }

    public function status(Request $request, $id_produk){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'string' => ':attribute harus string.',
            'numeric' => ':attribute sudah angka'
        ];
        $valid_data = Validator::make($request->all(), [
            'status' => 'required|string',
        ], $custom_rules);
        //'img' => 'required|mimes:pdf,jpg,jpeg,png|max:2048'

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
            $data = [
                'status_produk' => $request->input('status'),
            ];

            $produk = Produk::where('id', $id_produk)->update($data);
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data berhasil dirubah',
                'data' => $produk
            ],200);
        } catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'error' => $e,
                'message' => 'Data gagal dirubah',
                'data' => ''
            ], 400);
        }
    }
}
