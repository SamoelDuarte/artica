<?php

namespace App\Http\Controllers;

use App\Models\Sorteio;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $sorteios = Sorteio::with('numerosDaSorte.cliente')->orderBy('id', 'desc')->get();
    
        // Estruturar os dados para exibir no dashboard
        $dados = $sorteios->map(function ($sorteio) {
            // Contagem de clientes (total de inscritos)
            $totalClientes = $sorteio->clientes->count();
    
            // Contagem de participantes distintos
            $totalParticipantes = $sorteio->numerosDaSorte->pluck('cliente_id')->unique()->count();
    
            return [
                'nome' => $sorteio->nome,
                'sorteio_id' => $sorteio->id,
                'numero_sorteio' => $sorteio->numero_sorteio,
                'data_inicio' => $sorteio->getDataInicioFormatadaAttribute(),
                'data_termino' => $sorteio->getDataTerminoFormatadaAttribute(),
                'total_clientes' => $totalClientes,
                'total_participantes' => $totalParticipantes,
            ];
        });
    
        // Retornar para a view com os dados
        return view('dashboard', compact('dados'));
    }

}
