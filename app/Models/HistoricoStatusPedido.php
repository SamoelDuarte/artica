<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoStatusPedido extends Model
{
    protected $table = 'historico_status_pedido';
    use HasFactory;
    
    protected $fillable = [
        'pedido_id',
        'status',
        'mudanca_por',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
