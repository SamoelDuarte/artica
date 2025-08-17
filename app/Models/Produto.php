<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'nome',
        'valor',
        'valor_app',
        'valor_min_app',
        'valor_max_app',
        'foto',
        'aplicativo',
        'ativo',
    ];

    protected $casts = [
        'valor' => 'float',
        'valor_app' => 'float',
        'valor_min_app' => 'float',
        'valor_max_app' => 'float',
        'aplicativo' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function setValorAttribute($valor)
    {
        $this->attributes['valor'] = $this->formataDecimal($valor);
    }

    public function setValorAppAttribute($valor)
    {
        $this->attributes['valor_app'] = $this->formataDecimal($valor);
    }

    public function setValorMinAppAttribute($valor)
    {
        $this->attributes['valor_min_app'] = $this->formataDecimal($valor);
    }

    public function setValorMaxAppAttribute($valor)
    {
        $this->attributes['valor_max_app'] = $this->formataDecimal($valor);
    }

    private function formataDecimal($valor)
    {
        // Remove "R$", ponto de milhar e troca vírgula por ponto
        $valor = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $valor);
        return is_numeric($valor) ? floatval($valor) : null;
    }
}
