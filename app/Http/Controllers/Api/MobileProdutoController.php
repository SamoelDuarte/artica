<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;

class MobileProdutoController extends Controller
{
    public function listar(): JsonResponse
    {
        $produtos = Produto::where('ativo', true)
            ->get();

        return response()->json($produtos);
    }
}
