<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sorteio extends Model
{
    use HasFactory;

    // Campos preenchíveis para facilitar a inserção e atualização
    protected $fillable = [
        'nome',
        'numero_sorteio',
        'data_inicio',
        'data_termino',
        'numero_min',
        'numero_max',
    ];

    protected $dates = ['data_inicio', 'data_termino']; // Isso já garante a conversão

    /**
     * Relacionamento: Sorteio possui muitos Clientes.
     */
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    /**
     * Accessor para formatar a data de início.
     *
     * @return string
     */
    public function getDataInicioFormatadaAttribute()
    {
        // Garantir que seja uma instância de Carbon antes de chamar format
        return $this->data_inicio ? Carbon::parse($this->data_inicio)->format('d/m/Y') : null;
    }

    /**
     * Accessor para formatar a data de término.
     *
     * @return string
     */
    public function getDataTerminoFormatadaAttribute()
    {
        return $this->data_termino ? Carbon::parse($this->data_termino)->format('d/m/Y') : null;
    }

    // Relacionamento: Sorteio possui muitos números da sorte.
public function numerosDaSorte()
{
    return $this->hasMany(NumeroDaSorte::class);
}

}
