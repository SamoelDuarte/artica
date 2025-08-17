<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\StatusPedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobilePedidoController extends Controller
{
    public function listarPedidos($usuarioId)
    {
        $pedidos = Pedido::with(['cliente', 'entregador', 'statusPedido', 'itens.produto'])
            ->where('entregador_id', $usuarioId)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosFormatados = $pedidos->map(function ($pedido) {
            $pedido->itens = $pedido->itens->map(function ($item) {
                return [
                    'id' => $item->id,
                    'produto_id' => $item->produto_id,
                    'nome_produto' => $item->produto->nome ?? null,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario,
                ];
            });
            return $pedido;
        });

        return response()->json($pedidosFormatados);
    }
    public function atualizarStatus(Request $request, $id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['erro' => 'Pedido não encontrado'], 404);
        }

        $status = $request->input('descricao');
        $motivo = $request->input('motivo');

        // Valida status permitido
        $statusPermitidos = ['aceito', 'recusado', 'cancelado', 'finalizado'];
        if (!in_array($status, $statusPermitidos)) {
            return response()->json(['erro' => 'Status inválido'], 400);
        }

        $statusId = StatusPedido::where('descricao', $status)->value('id');
        if (!$statusId) {
            return response()->json(['erro' => 'Status não encontrado'], 400);
        }

        $pedido->status_pedido_id = $statusId;

        // Se for cancelado, precisa do motivo_cancelamento
        if ($status === 'cancelado') {
            if (!$motivo) {
                return response()->json(['erro' => 'Motivo do cancelamento é obrigatório'], 422);
            }
            $pedido->motivo_cancelamento = $motivo;
        }

        // Se for recusado, precisa do motivo_reculsa
        if ($status === 'recusado') {
            if (!$motivo) {
                return response()->json(['erro' => 'Motivo da recusa é obrigatório'], 422);
            }
            $pedido->motivo_reculsa = $motivo;
        }

        $pedido->save();

        // Busca nome do entregador (relacionado ao pedido)
        $nomeEntregador = $pedido->entregador ? $pedido->entregador->nome : 'Desconhecido';

        // Monta a observação personalizada
        $observacao = null;
        if (in_array($status, ['cancelado', 'recusado'])) {
            $observacao = ucfirst($status) . " por $nomeEntregador. Motivo: $motivo";
        }

        $pedido->historicoStatus()->create([
            'status' => $statusId,
            'data' => now(),
            'observacao' => $observacao,
        ]);


        return response()->json(['sucesso' => true, 'pedido' => $pedido]);
    }

    public function store(Request $request)
    {
        $dados = $request->all();

        // Codificar o telefone informado para buscar no banco de dados
        $telefoneCodificado = base64_encode(preg_replace('/\D/', '', $dados['telefone']));

        // Buscar o cliente pelo telefone codificado
        $cliente = \App\Models\Cliente::where('telefone', $telefoneCodificado)->first();

        // Se cliente não existir, cria um novo
        if (!$cliente) {
            $cliente = \App\Models\Cliente::create([
                'nome'             => $dados['nome'],
                'telefone'         => $dados['telefone'],
                'cep'              => $dados['cep'] ?? null,
                'numero'           => $dados['numero'] ?? null,
                'logradouro'       => $dados['logradouro'] ?? null,
                'bairro'           => $dados['bairro'] ?? null,
                'cidade'           => $dados['cidade'] ?? null,
                'complemento'      => $dados['complemento'] ?? null,
                'referencia'       => $dados['referencia'] ?? null,
                'data_nascimento'  => $dados['data_nascimento'] ?? null,
                'observacao'       => $dados['observacao'] ?? null,
            ]);
        }

        DB::beginTransaction();

        try {
            $pedido = \App\Models\Pedido::create([
                'tipo_pedido'         => $dados['tipo_pedido'] ?? 'mobile',
                'motivo_cancelamento' => $dados['motivo_cancelamento'] ?? null,
                'mensagem'            => $dados['mensagem'] ?? null,
                'valor_total'         => $dados['total'],
                'entregador_id'        => $dados['usuario_id'],
                'desconto'            => $dados['desconto'] ?? 0,
                'notifica_mensagem'   => $dados['notifica_mensagem'] ?? false,
                'cliente_id'          => $cliente->id,
                'status_pedido_id'    => 3, // Status inicial
            ]);

            \App\Models\HistoricoStatusPedido::create([
                'pedido_id'   => $pedido->id,
                'status'      => 3,
                'mudanca_por' => $dados['usuario_id'],
            ]);

            foreach ($dados['produtos'] as $item) {
                \App\Models\ItemPedido::create([
                    'pedido_id'      => $pedido->id,
                    'produto_id'     => $item['codigo'],
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $item['valorUnitario'],
                ]);
            }

            $totalPedido = (float) $dados['total'];
            $totalPago = 0;
            $pagamentoDinheiroIndex = null;

            $pagamentos = $dados['pagamentos'];

            foreach ($pagamentos as $index => $pagamento) {
                $valor = (float) $pagamento['valor'];
                $totalPago += $valor;

                if ($pagamento['formaPagamento'] == "1") {
                    $pagamentoDinheiroIndex = $index;
                }
            }

            $troco = 0;

            if ($pagamentoDinheiroIndex !== null && $totalPago > $totalPedido) {
                $valorTroco = $totalPago - $totalPedido;
                $troco = $valorTroco;

                $pagamentos[$pagamentoDinheiroIndex]['valor_recebido'] = (float) $pagamentos[$pagamentoDinheiroIndex]['valor'];
                $pagamentos[$pagamentoDinheiroIndex]['troco'] = $valorTroco;
            }

            foreach ($pagamentos as $pagamento) {
                \App\Models\PedidoFormaPagamento::create([
                    'pedido_id'          => $pedido->id,
                    'forma_pagamento_id' => $pagamento['formaPagamento'],
                    'valor'              => $pagamento['valor'],
                    'valor_recebido'     => $pagamento['valor_recebido'] ?? null,
                    'troco'              => $pagamento['troco'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pedido criado com sucesso.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
