@extends('sistema.layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Page Heading -->
        <div class="page-header-content py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0 text-gray-800">Clientes</h1>
                @can('criar clientes')
                    <a href="{{ route('cliente.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus text-white-50"></i> Novo Cliente
                    </a>
                @endcan
            </div>

            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Clientes</li>
            </ol>
        </div>

        <!-- Content Row -->
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="table-clientes">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Telefone</th>
                                        <th>Bairro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clientes as $cliente)
                                        <tr>
                                            <td>{{ $cliente->id }}</td>
                                            <td>{{ $cliente->nome }}</td>
                                            <td>{{ $cliente->telefone }}</td>
                                            <td>{{ $cliente->bairro }}</td>
                                            <td>
                                                @can('editar clientes')
                                                    <a href="{{ route('cliente.edit', $cliente->id) }}" class="btn" title="Editar">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </a>
                                                @endcan

                                                @can('excluir clientes')
                                                    <form action="{{ route('cliente.destroy', $cliente->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn" title="Deletar">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#table-clientes').DataTable();
        });
    </script>
@endsection
