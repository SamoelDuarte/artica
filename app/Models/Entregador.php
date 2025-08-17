<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entregador extends Model
{
    use HasFactory;

    protected $table = 'entregadores';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'trabalhando',
        'telefone',
        'ativo',
    ];

    protected $hidden = [
        'senha',
    ];

    /**
     * Relacionamento: entregador tem muitos pedidos
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'entregador_id');
    }

    /**
     * Buscar entregadores trabalhando hoje com contagem de pedidos do dia
     */
    public static function entregadoresTrabalhandoHoje()
    {
        return self::where('trabalhando', true)
            ->where('ativo', 1)
            ->withCount(['pedidos as pedidos_do_dia' => function ($query) {
                $query->whereDate('created_at', today())->where('status_pedido_id', 8);
            }])
            ->get();
    }

    // Relação com mensagens enviadas
    public function mensagens()
    {
        return $this->hasMany(Messagen::class, 'usuario_id');
    }
}
