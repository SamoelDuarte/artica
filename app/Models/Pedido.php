<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'entregador_id',
        'valor_total',
        'desconto',
        'mensagem',
        'tipo_pedido',
        'notifica_mensagem',
        'motivo_cancelamento',
        'motivo_reculsa',
        'status_pedido_id'
    ];

    // 🔗 Relacionamento com o cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // 🔗 Relacionamento com o entregador
    public function entregador()
    {
        return $this->belongsTo(Entregador::class);
    }

    // 🔗 Itens do pedido
    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }

    // 🔗 Formas de pagamento
    public function formasPagamento()
    {
        return $this->belongsToMany(FormaPagamento::class, 'pedido_forma_pagamento')
                    ->withPivot('valor', 'valor_recebido', 'troco');
    }

    // 🔗 Histórico de status
    public function historicoStatus()
    {
        return $this->hasMany(HistoricoStatusPedido::class);
    }
    public function statusPedido()
{
    return $this->belongsTo(StatusPedido::class, 'status_pedido_id');
}
}
