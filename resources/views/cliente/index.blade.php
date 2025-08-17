<!-- resources/views/cliente/index.blade.php -->

@extends('layouts.app')
@section('css')
    <!-- Inclua os estilos do DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Clientes</li>
        </ol>
    </nav>

    <!-- Header com botão adicionar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Lista de Clientes</h1>
        <!-- Botão para abrir o Modal -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addClienteModal">Adicionar
            Cliente</button>
    </div>

    <!-- Tabela de Clientes -->
    <table id="clientes-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Telefone</th>
                <th>Número(s) da Sorte</th>
                <th>Sorteio</th>
                <th>Link</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->id }}</td>
                    <td>{{ $cliente->telefone }}</td>
                    <td>
                        @if ($cliente->numerosSorte->count() > 0)
                            @foreach ($cliente->numerosSorte as $numero)
                                ({{ $numero->numero }})
                                <!-- Exibindo cada número da sorte -->
                            @endforeach
                        @else
                            <span>Sem número da sorte</span>
                        @endif
                    </td>
                    <td>{{ $cliente->sorteio->nome }}</td>
                    <td>
                        @if ($cliente->link)
                            <!-- Mostrar apenas uma parte do link -->
                            {{ substr($cliente->link, 0, 30) }}...
                        @else
                            <span>Sem link</span>
                        @endif
                    </td>
                    <td>
                        <!-- Botão de Copiar Link -->
                        @if ($cliente->link)
                            <button class="btn btn-primary btn-sm copiar-link" data-link="{{ $cliente->link }}">
                                Copiar
                            </button>
                        @endif

                        <!-- Botão de Deletar com Alerta de Confirmação -->
                        <form action="{{ route('cliente.destroy', $cliente->id) }}" method="POST" style="display:inline;"
                            onsubmit="return confirmDelete()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Deletar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal para Adicionar Cliente -->
    <div class="modal fade" id="addClienteModal" tabindex="-1" aria-labelledby="addClienteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('cliente.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addClienteModalLabel">Adicionar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <!-- Campo de Telefone -->
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone"
                                placeholder="Digite o telefone" required>
                        </div>
                        <!-- Campo de Quantidade de Números da Sorte -->
                        <div class="mb-3">
                            <label for="quantidade_numeros" class="form-label">Quantidade de Números</label>
                            <input type="number" class="form-control" id="quantidade_numeros" name="quantidade_numeros"
                                placeholder="Digite a quantidade" min="1" required>
                        </div>

                        <!-- Campo de Seleção de Sorteio -->
                        <div class="mb-3">
                            <label for="sorteio_id" class="form-label">Selecione o Sorteio</label>
                            <select class="form-select" id="sorteio_id" name="sorteio_id" required>
                                <option value="">Selecione um sorteio</option>
                                @foreach ($sorteios as $sorteio)
                                    <option value="{{ $sorteio->id }}">
                                        {{ $sorteio->nome }} - Início: {{ $sorteio->data_inicio_formatada }} - Fim:
                                        {{ $sorteio->data_fim_formatada }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <!-- Inclua os scripts do DataTables -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    <script>
        // Função de confirmação antes de deletar
        function confirmDelete() {
            return confirm('Tem certeza de que deseja excluir este cliente?');
        }
    </script>

    <script>
        $(document).ready(function() {
            // Inicializar o DataTable
            $('#clientes-table').DataTable({
                order: [
                    [0, 'desc']
                ], // Ordena pela primeira coluna (ID) em ordem decrescente
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json"
                }
            });


            // Configurar ClipboardJS para o botão de copiar link
            new ClipboardJS('.copiar-link', {
                text: function(trigger) {
                    return $(trigger).data('link');
                }
            });

            // Mensagem ao copiar
            $('.copiar-link').on('click', function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                Toast.fire({
                    icon: 'success',
                    title: "Link copiado !",
                })
            });
        });
    </script>
@endsection
