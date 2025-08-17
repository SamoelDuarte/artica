<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PedidoFormaPagamento extends Model
{
    use HasFactory;

    protected $table = 'pedido_forma_pagamento';

    protected $fillable = [
        'pedido_id',
        'forma_pagamento_id',
        'valor',
        'valor_recebido',
        'troco',
    ];

    public function getValorLiquidoAttribute()
    {
        if ($this->forma_pagamento_id == 1) { // Dinheiro
            return $this->valor_recebido - $this->troco;
        }

        return $this->valor;
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }
}
