<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $appends = [
        'display_status',
        'message_count_last_hour',
        'data_ultima_recarga_formatada',
    ];
    protected $fillable = [
        'name',
        'picture',
        'jid',
        'session',
        'status',
        'data_ultima_recarga',
        'start_minutes',
        'start_seconds',
        'end_minutes',
        'end_seconds',
    ];

    protected $attributes = [
        'start_minutes' => 0,
        'start_seconds' => 0,
        'end_minutes' => 1,
        'end_seconds' => 0,
    ];

    // Método para contar mensagens enviadas pelo dispositivo nas últimas horas
    public function getMessageCountLastHourAttribute()
    {
        // Data e hora atual
        $now = Carbon::now();

        // Subtrai uma hora da data e hora atual para obter a hora anterior
        $oneHourAgo = $now->subHour();

        // Contagem das mensagens enviadas pelo dispositivo nas últimas horas
        return $this->messages()
            ->where('device_id', $this->id) // Somente mensagens relacionadas a este dispositivo
            ->where('created_at', '>=', $oneHourAgo)
            ->count();
    }

    // Relacionamento um-para-muitos com Message
    public function messages()
    {
        return $this->hasMany(Messagen::class, 'device_id');
    }

    public function getDisplayStatusAttribute()
    {
        if ($this->status == "open") {
            return "Conectado";
        }

        if ($this->status == "connecting") {
            return "Conectando";
        }

        if ($this->status == "DISCONNECTED" || $this->status == "disconnected") {
            return "Desconectado";
        }
        
        return "Desconectado"; // Valor padrão se status for null
    }

    public function getDataUltimaRecargaFormatadaAttribute()
    {
        if ($this->data_ultima_recarga) {
            return Carbon::parse($this->data_ultima_recarga)->format('d/m/Y H:i:s');
        }
        return 'Nunca';
    }
    public static function deleteDevicesWithNullJid()
    {
        self::whereNull('jid')->delete();
    }

    public function campaigns()
{
    return $this->belongsToMany(Campaign::class, 'campaign_device');
}
}
