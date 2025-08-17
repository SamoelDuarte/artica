<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messagen extends Model
{
    use HasFactory;

    protected $table = 'messagens';

    protected $fillable = [
        'device_id',
        'pedido_id',
        'usuario_id',
        'messagem',
        'number',
        'direcao',
        'enviado',
    ];

    protected $appends = [
        'display_status',
        'display_created_at',
        'image_id'  // Se continua usando isso, senão pode remover
    ];

    // Relação com Device
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    // Relação com ImagemEmMassa (se usar)
    public function imagem()
    {
        return $this->belongsTo(ImagemEmMassa::class, 'image_id');
    }

    // Relação com Pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // Relação com Entregador (vendedor)
    public function entregador()
    {
        return $this->belongsTo(Entregador::class, 'usuario_id');
    }

    public function getDisplayStatusAttribute()
    {
        return $this->device_id === null ? "Pendente" : "Enviado";
    }

    public function getDisplayCreatedAtAttribute()
    {
        return date('d/m/Y H:i', strtotime($this->created_at));
    }
    public function getImageIdAttribute()
{
    return $this->attributes['image_id'] ?? null;
}
}
