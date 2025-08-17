<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Sorteio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Listar todos os clientes.
     */
    // public function index()
    // {
    //     // Carregar os clientes com seus respectivos sorteios e números da sorte
    //     $clientes = Cliente::with(['sorteio', 'numerosSorte'])->orderBy('id', 'desc')->get();

    //     // Buscar todos os sorteios disponíveis (não encerrados)
    //     $sorteios = Sorteio::where('data_termino', '>=', Carbon::now())->get();

    //     // Retornar a view com os dados
    //     return view('sistema.cliente.index', compact('clientes', 'sorteios'));
    // }



    public function index()
    {
        $clientes = Cliente::all();
        // Retornar a view com os dados
        return view('sistema.cliente.index', compact('clientes'));
    }

    public function create()
    {
        return view('sistema.cliente.create');
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:20',
            'cep' => 'required|string|max:9',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'data_nascimento' => 'nullable|date',
            'referencia' => 'nullable|string|max:255',
            'observacao' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Preencha todos os campos obrigatórios.')
                ->withErrors($validator)
                ->withInput();
        }


        Cliente::create($validator->validated());

        return redirect()->route('cliente.index')->with('success', 'Cliente salvo com sucesso!');
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);

        return view('sistema.cliente.edit', compact('cliente'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:20',
            'cep' => 'required|string|max:9',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'data_nascimento' => 'nullable|date',
            'referencia' => 'nullable|string|max:255',
            'observacao' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Preencha todos os campos obrigatórios.')
                ->withErrors($validator)
                ->withInput();
        }

        $cliente = Cliente::findOrFail($id);
        $cliente->update($validator->validated());

        return redirect()->route('cliente.index')->with('success', 'Cliente atualizado com sucesso!');
    }






    /**
     * Criar um novo cliente.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'telefone' => 'required|string',
    //         'sorteio_id' => 'required|exists:sorteios,id',
    //         'quantidade_numeros' => 'required|integer|min:1',
    //     ]);

    //     Cliente::create([
    //         'telefone' => $request->telefone,
    //         'sorteio_id' => $request->sorteio_id,
    //         'quantidade_numeros' => $request->quantidade_numeros,
    //     ]);

    //     return redirect()->route('cliente.index')->with('success', 'Cliente cadastrado com sucesso!');
    // }

    /**
     * Exibir um cliente específico.
     */
    public function show($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado!'], 404);
        }

        return response()->json($cliente);
    }

    /**
     * Atualizar um cliente.
     */
    // public function update(Request $request, $id)
    // {
    //     $cliente = Cliente::find($id);

    //     if (!$cliente) {
    //         return response()->json(['message' => 'Cliente não encontrado!'], 404);
    //     }

    //     $validated = $request->validate([
    //         'telefone' => 'sometimes|required|string|max:20',
    //         'link' => 'nullable|string|url',
    //         'numero_da_sorte' => 'sometimes|required|integer',
    //     ]);

    //     $cliente->update($validated);

    //     return response()->json([
    //         'message' => 'Cliente atualizado com sucesso!',
    //         'cliente' => $cliente,
    //     ]);
    // }

    /**
     * Deletar um cliente.
     */
    public function destroy($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado!'], 404);
        }

        $cliente->delete();
        return redirect()->route('cliente.index')->with('success', 'Cliente deletado com sucesso!');
    }
    public function buscarPorTelefone(Request $request)
    {
        // Limpa tudo que não for número
        $telefone = preg_replace('/\D/', '', $request->get('telefone'));

        // Codifica para base64 (formato que está salvo no banco)
        $telefoneCodificado = base64_encode($telefone);

        // Busca todos os clientes onde o telefone codificado contém o que foi digitado
        $clientes = Cliente::all()->filter(function ($cliente) use ($telefone) {
            // Descriptografa o telefone
            $telefoneCliente = preg_replace('/\D/', '', $cliente->telefone);

            // Verifica se contém o número digitado
            return str_contains($telefoneCliente, $telefone);
        })->values()->take(10);

        return response()->json($clientes);
    }
}
