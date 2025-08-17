<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entregador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $user = Entregador::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->senha)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        return response()->json(['message' => 'Login realizado com sucesso!','user' => $user], 200);
    }


    // Rota protegida para testar autenticação
    public function teste(Request $request)
    {
        return response()->json([
            'message' => 'Autenticado com sucesso!',
            'user' => $request->user(),
        ]);
    }

    // Opcional: logout para revogar o token atual
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
