<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('unit')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        // Revoke existing tokens for cleaner token state
        $user->tokens()->delete();
        $token = $user->createToken('bumdes_auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->nama,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->id_unit,
                'id_unit' => $user->id_unit,
                'unit_name' => $user->unit ? $user->unit->nama_unit : 'Kantor Pusat',
                'nama_unit' => $user->unit ? $user->unit->nama_unit : 'Kantor Pusat',
                'kode_unit' => $user->unit ? $user->unit->kode_unit : 'PUSAT',
                'unit' => $user->unit,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('unit');

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->nama,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role,
                'unit_id' => $user->id_unit,
                'id_unit' => $user->id_unit,
                'unit_name' => $user->unit ? $user->unit->nama_unit : 'Kantor Pusat',
                'nama_unit' => $user->unit ? $user->unit->nama_unit : 'Kantor Pusat',
                'kode_unit' => $user->unit ? $user->unit->kode_unit : 'PUSAT',
                'unit' => $user->unit,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dicabut.',
        ]);
    }
}
