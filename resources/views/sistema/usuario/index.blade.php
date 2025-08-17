@extends('sistema.layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Page Heading -->
        <div class="page-header-content py-3">

            <div class="d-sm-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0 text-gray-800">Usuários</h1>
                @can('criar usuários')
                    <a href="{{ route('usuario.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus text-white-50"></i> Novo Usuário
                    </a>
                @endcan
            </div>

            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Usuários</li>
            </ol>

        </div>
        <!-- Content Row -->
        <div class="row">
            <!-- Content Column -->
            <div class="col-lg-12 mb-4">
                <!-- Project Card Example -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-device">
                            <!-- Tabela de Usuários -->
                            <table class="table table-bordered" id="table-device">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <!-- Botões de Editar e Deletar, com controle de permissões -->
                                                @can('editar usuário')
                                                    <a href="{{ route('usuario.edit', $user->id) }}" class="btn"
                                                        title="EDITAR">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </a>
                                                @endcan

                                                @can('excluir usuário')
                                                    <form action="{{ route('usuario.destroy', $user->id) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn" title="DELETAR">
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
        // Código JS adicional, se necessário.
    </script>
@endsection
