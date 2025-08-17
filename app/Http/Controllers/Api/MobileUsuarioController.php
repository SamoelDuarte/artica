<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entregador;

class MobileUsuarioController extends Controller
{
    public function verificaUsuario($usuario_id)
{
    $usuario = Entregador::find($usuario_id);

    if (! $usuario) {
        return response()->json(['message' => 'Usuário não encontrado'], 404);
    }

    if (! $usuario->ativo) {
        return response()->json(['message' => 'Usuário inativo'], 403);
    }

    return response()->json([
        'message' => 'Usuário válido',
        'user' => $usuario
    ], 200);
}

}
