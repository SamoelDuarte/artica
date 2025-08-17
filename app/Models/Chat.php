<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'service_id',
        'jid',
        'active',
        'erro',
        'await_answer',
        'flow_stage', // novo campo
    ];

    protected $appends = [
        'display_status',
        'flow_stage_label', // novo atributo virtual
    ];


    public function getDisplayStatusAttribute()
    {
        switch ($this->await_answer) {
            case 'await_human':
                return "Aguardando Atendimento";
            case 'in_service':
                return "Em Atendimento";
            case 'finish':
                return "Finalizado";
            default:
                return "Sem Status";
        }
    }

    public function getFlowStageLabelAttribute()
    {
        return match ($this->flow_stage) {
            'aguardando' => '🕐 Aguardando resposta',
            'fazendo_pedido' => '🍕 Escolhendo produtos',
            'fazendo_cadastro' => '📝 Preenchendo cadastro',
            'confirmando' => '💳 Confirmando pagamento',
            'finalizado' => '✅ Pedido finalizado',
            'eu_iniciei' => '👋 Pizzaria iniciou a conversa',
            default => '🔍 Etapa desconhecida',
        };
    }
}
