<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chart;
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
            'jenis' => 'required|string',
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
                // $pathImg = Storage::path($path);
                $data = [
                    'nama_produk' => $request->input('nama'),
                    'harga_produk' => $request->input('harga'),
                    'desc_produk' => $request->input('desc'),
                    'jenis_produk' => $request->input('jenis'),
                    'img_produk' => $path,
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
                'kode' => Response::HTTP_BAD_GATEWAY,
                'success' => false,
                'error' => $e,
                'message' => 'Data tidak valid',
                'data' => ''
            ], 502);
        }
    }

    public function show($id_produk){
        try {
            $produk = Produk::with('jenis')->where('id', $id_produk)->first();
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
            'jenis' => 'required|string',
            'img_lama' => 'required|string',
            'img' => 'mimes:pdf,jpg,jpeg,png|max:2048'
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
                    'kode' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'error' => '',
                    'message' => 'Data tidak ditemukan',
                    'data' => ''
                ], 404);
            }
            // $file = explode('/', $produk->img_produk);
            // $tempValue = 'img/'.$file[1];
            $tempValue = $produk->img_produk;

            if($request->file('img')){
                $path = $request->file('img')->store('img');
                // $pathImg = Storage::path($path);

                //update file
                $produk->nama_produk = $request->input('nama');
                $produk->harga_produk = $request->input('harga');
                $produk->desc_produk = $request->input('desc');
                $produk->jenis_produk = $request->input('jenis');
                $produk->img_produk = $path;
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
            }else{
                $produk->nama_produk = $request->input('nama');
                $produk->harga_produk = $request->input('harga');
                $produk->desc_produk = $request->input('desc');
                $produk->jenis_produk = $request->input('jenis');
                $produk->save();
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
                'kode' => Response::HTTP_BAD_GATEWAY,
                'success' => false,
                'error' => $e,
                'message' => 'Data gagal dirubah',
                'data' => ''
            ], 502);
        }
    }

    public function all(){
        try {
            // $produks = [];
            $produk = Produk::with('jenis')->get();
            // $endPointImg = url('').'/APIV1/viewImg/';
            // foreach ($produk as $value) {
            //     $produks[] = [
            //         'id' => $value->id,
            //         'nama_produk' => $value->nama_produk,
            //         'harga_produk' => $value->harga_produk,
            //         'desc_produk' => $value->desc_produk,
            //         'jenis_produk' => $value->jenis_produk,
            //         'status_produk' => $value->status_produk,
            //         'img_produk' => $endPointImg.$value->img_produk
            //     ];
            // }
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data ditemukan',
                'data' => $produk
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

    public function destroy($id_produk){

        try {
            $produk = Produk::where('id', $id_produk)->first();
            if (!$produk) {
                return response()->json([
                    'kode' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'error' => '',
                    'message' => 'Data tidak ditemukan',
                    'data' => ''
                ], 404);
            }

            //hapus data
            // $file = explode('/', $produk->img_produk);
            // $tempValue = 'img/'.$file[1];
            $tempValue = $produk->img_produk;
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
                'kode' => Response::HTTP_BAD_GATEWAY,
                'success' => false,
                'error' => $e,
                'message' => 'Data gagal dihapus',
                'data' => ''
            ],502);
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
                'kode' => Response::HTTP_BAD_GATEWAY,
                'success' => false,
                'error' => $e,
                'message' => 'Data gagal dirubah',
                'data' => ''
            ], 502);
        }
    }

    public function viewImage(string $folder, string $fileName)
    {

        try {
            if ($folder != '') {
                $path = str_replace('../', '', $folder . '/' . $fileName);
                $path = str_replace('./', '', $path);
                $path = Storage::path($path);
                return response()->file($path);

            }
            return abort(404, 'File not found');
        } catch (\Throwable $th) {
            return abort(404, 'File not found');
        }
    }

    //role pegawai
    public function menu(){
        try {
            // $produks = [];
            $produk = Produk::where(['status_produk' => 'T'])->get();
            // $endPointImg = url('').'/APIV1/viewImg/';
            // foreach ($produk as $value) {
            //     $produks[] = [
            //         'id' => $value->id,
            //         'nama_produk' => $value->nama_produk,
            //         'harga_produk' => $value->harga_produk,
            //         'desc_produk' => $value->desc_produk,
            //         'jenis_produk' => $value->jenis_produk,
            //         'status_produk' => $value->status_produk,
            //         'img_produk' => $endPointImg.$value->img_produk
            //     ];
            // }
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data ditemukan',
                'data' => $produk
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

    public function chart(Request $request){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'string' => ':attribute harus string.',
            'numeric' => ':attribute sudah angka'
        ];
        $valid_data = Validator::make($request->all(), [
            'produk' => 'required|string',
            'status' => 'required|string',
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
            if($request->post('status') == 'tambah'){
                $cek = Chart::where(['produk' => $request->post('produk'), 'user' => auth()->user()->id])->first();
                if($cek == null){
                    $produk = Produk::where(['id' => $request->input('produk')])->first();
                    $data = [
                        'produk' => $request->input('produk'),
                        'qty' => 1,
                        'total' => (1 * $produk->harga_produk),
                        'user' => auth()->user()->id
                    ];
        
                    $chart = Chart::create($data);
                    return response()->json([
                        'kode' => Response::HTTP_CREATED,
                        'success' => true,
                        'error' =>'',
                        'message' => 'Data berhasil disimpan',
                        'data' => $chart
                    ],200);
                }else{
                    $produk = Produk::where(['id' => $request->input('produk')])->first();
                    $qty_baru = $cek->qty + 1;
                    $cek->qty = $qty_baru;
                    $cek->total = ($qty_baru * $produk->harga_produk);
                    $cek->save();
                    return response()->json([
                        'kode' => Response::HTTP_CREATED,
                        'success' => true,
                        'error' =>'',
                        'message' => 'Data berhasil disimpan',
                        'data' => $cek
                    ],200);
                }
            }else if($request->post('status') == 'kurang'){
                $cek = Chart::where(['produk' => $request->post('produk'), 'user' => auth()->user()->id])->first();
                if($cek){
                    if($cek->qty > 0){
                        $produk = Produk::where(['id' => $request->input('produk')])->first();
                        $qty_baru = $cek->qty - 1;
                        $cek->qty = $qty_baru;
                        $cek->total = ($qty_baru * $produk->harga_produk);
                        $cek->save();
                        return response()->json([
                            'kode' => Response::HTTP_CREATED,
                            'success' => true,
                            'error' =>'',
                            'message' => 'Data berhasil disimpan',
                            'data' => $cek
                        ],200);
                    }else{
                        $cek->delete();
                        return response()->json([
                            'kode' => Response::HTTP_CREATED,
                            'success' => true,
                            'error' =>'',
                            'message' => 'Data berhasil disimpan',
                            'data' => $cek
                        ],200);
                    }
                }
            }
        }catch (\Throwable $e) {
            return response()->json([
                'kode' => Response::HTTP_BAD_GATEWAY,
                'success' => false,
                'error' => $e,
                'message' => 'Data tidak valid',
                'data' => ''
            ], 502);
        }
    }

    public function list(){
        try {
            $chart = Chart::where(['user' => auth()->user()->id])->get();
            $total_item=$total_byr=0;
            foreach ($chart as $item) {
                $total_item += $item->qty;
                $total_byr += $item->total;
            }
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data ditemukan',
                'data' => [
                    'item' => $total_item,
                    'total' => $total_byr
                ]
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

    public function order(){
        try {
            $chart = Chart::where(['user' => auth()->user()->id])->with('prod')->get();
            return response()->json([
                'kode' => Response::HTTP_OK,
                'success' => true,
                'error' =>'',
                'message' => 'Data ditemukan',
                'data' => $chart
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
}
