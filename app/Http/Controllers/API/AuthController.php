<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    //
    public function registrasi(Request $request){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'unique' => ':attribute sudah dipakai',
            'same' => ':attribute tidak sesuai'
        ];
        $valid_data = Validator::make($request->all(), [
            'nama' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
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
            $data = [
                'name' => $request->input('nama'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
                'role' => 'admin',
            ];
            $user = User::create($data);
            return response()->json([
                'kode' => Response::HTTP_CREATED,
                'success' => true,
                'error' =>'',
                'message' => 'Data berhasil disimpan',
                'data' => $user
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

    public function login(Request $request){
        $custom_rules = [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute harus diisi maksimal :max karakter.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'unique' => ':attribute sudah dipakai',
            'same' => ':attribute tidak sesuai'
        ];
        $valid_data = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
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
            if (!auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                return response()->json([
                    'kode' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'error' => '',
                    'message' => 'Username atau Password salah',
                    'data' => ''
                ], 400);
            }
            //generate token
            $token = auth()->user()->createToken('API Token Kedai Kopi')->accessToken;
            $user = auth()->user();
            $user['token'] = $token;
            return response()->json([
                'kode' => Response::HTTP_CREATED,
                'success' => true,
                'error' =>'',
                'message' => 'Berhasil Login',
                'data' => $user
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

    public function logout(Request $request){
        $token = $request->user()->token();
        $token->revoke();
        $user = auth()->user();
        $user->tokens()->delete();
        return response()->json([
            'kode' => Response::HTTP_OK,
            'success' => true,
            'error' =>'',
            'message' => 'Berhasil Logout',
            'data' => $token
        ],200);
    }
}
