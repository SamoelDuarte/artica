<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileClienteController extends Controller
{


    public function getCliente(Request $request): JsonResponse
    {
        $telefone = $request->input('telefone');

        if (!$telefone) {
            return response()->json(['erro' => 'Telefone não informado'], 400);
        }

        // Limpa o telefone e codifica
        $telefoneLimpo = preg_replace('/\D/', '', $telefone);
        $telefoneCodificado = base64_encode($telefoneLimpo);

        // Busca no banco usando o campo codificado
        $cliente = Cliente::where('telefone', $telefoneCodificado)->first();

        if (!$cliente) {
            return response()->json(['erro' => 'Cliente não encontrado'], 404);
        }

        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string',
            'telefone' => 'required|string',
            'logradouro' => 'nullable|string',
            'numero' => 'nullable|string',
            'bairro' => 'nullable|string',
            'cidade' => 'nullable|string',
            'cep' => 'nullable|string',
            'complemento' => 'nullable|string',
            'referencia' => 'nullable|string',
            'observacao' => 'nullable|string',
            'data_nascimento' => 'nullable|date',
        ]);

        $cliente = Cliente::create($validated);

        return response()->json($cliente, 201);
    }
}
