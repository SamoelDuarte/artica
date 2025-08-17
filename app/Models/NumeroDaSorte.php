<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumeroDaSorte extends Model
{
    use HasFactory;

    protected $table = 'numeros_da_sorte';
    protected $fillable = [
        'cliente_id',
        'sorteio_id',
        'numero',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Relacionamento com Sorteio
    public function sorteio()
    {
        return $this->belongsTo(Sorteio::class);
    }
}
