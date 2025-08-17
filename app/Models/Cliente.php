<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'clientes';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'cep',
        'telefone',
        'link',
        'sorteio_id',
        'quantidade_numeros',
        'referencia',
        'observacao',
        'data_nascimento',
    ];

    /**
     * Mutator para formatar e codificar o telefone e gerar o link.
     */
    public function setTelefoneAttribute($value)
    {
        // Limpa o telefone (remove qualquer coisa que não for número)
        $telefoneLimpo = preg_replace('/\D/', '', $value);

        // Codifica o telefone com base64
        $this->attributes['telefone'] = base64_encode($telefoneLimpo);

        // Gera o link com o telefone codificado
        $this->attributes['link'] = env('APP_URL') . "/sorteio/" . $this->attributes['telefone'];
    }

    /**
     * Accessor para decodificar o telefone ao acessar.
     */
    public function getTelefoneAttribute($value)
    {
        return base64_decode($value);
    }

    /**
     * Relacionamento com os números da sorte.
     */
    public function numerosSorte()
    {
        return $this->hasMany(NumeroDaSorte::class, 'cliente_id');
    }

    /**
     * Pega os números da sorte de um sorteio específico.
     */
    public function getNumerosSortePorSorteio($sorteioId)
    {
        return $this->numerosSorte()->where('sorteio_id', $sorteioId)->pluck('numero');
    }

    /**
     * Relacionamento com Sorteio.
     */
    public function sorteio()
    {
        return $this->belongsTo(Sorteio::class);
    }
}
