@extends('sistema.layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="page-header-content py-3">
            <h1 class="h3 mb-0 text-gray-800">Editar Usuários</h1>
            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('usuario.index') }}">Usuários</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Usuário</li>
            </ol>
        </div>
        <!-- Content Row -->
        <div class="row">
            <!-- Content Column -->
            <div class="col-lg-12 mb-4">
                <!-- Project Card Example -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Formulário de Edição de Usuário -->
                        <form action="{{ route('usuario.update', $usuario->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT') <!-- Método PUT para editar no Laravel -->

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="{{ old('name', $usuario->name) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ old('email', $usuario->email) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Senha</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <small class="form-text text-muted">Deixe em branco para manter a senha
                                            atual</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="telefone" class="form-label">Telefone</label>
                                        <input type="text" class="form-control" id="telefone" name="telefone"
                                            value="{{ old('telefone', $usuario->telefone) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Função</label>
                                        <select class="form-control" id="role" name="role">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}"
                                                    {{ $usuario->hasRole($role->name) ? 'selected' : '' }}>
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="entrega_direta" class="form-label">Entrega Direta</label>
                                        <input type="checkbox" class="form-check-input" id="entrega_direta"
                                            name="entrega_direta"
                                            {{ old('ativo', $usuario->entrega_direta ? 'checked' : '') }}>
                                    </div>

                                    <div class="mb-3">
                                        <label for="ativo" class="form-label">Ativo</label>
                                        <input type="checkbox" class="form-check-input" id="ativo" name="ativo"
                                            {{ old('ativo', $usuario->ativo ? 'checked' : '') }}>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="data_admissao" class="form-label">Data de Admissão</label>
                                        <input type="date" class="form-control" id="data_admissao" name="data_admissao"
                                            value="{{ old('data_admissao', $usuario->data_admissao) }}">
                                    </div>

                                    <div class="mb-3">
                                        <label for="data_demissao" class="form-label">Data de Demissão</label>
                                        <input type="date" class="form-control" id="data_demissao" name="data_demissao"
                                            value="{{ old('data_demissao', $usuario->data_demissao) }}">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="foto" class="form-label">Foto</label>
                                                <input type="file" class="form-control" id="foto" name="foto"
                                                    accept="image/*" onchange="showPreview(event)">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div id="foto-preview"
                                                style="display: {{ $usuario->foto ? 'block' : 'none' }}">
                                                <img id="preview-img"
                                                    src="{{ $usuario->foto ? asset('storage/' . $usuario->foto) : '#' }}"
                                                    alt="Preview da Foto" width="100">
                                                <button type="button" class="remove-preview" id="btn-remover"
                                                    onclick="removePreview()">X</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Checkbox oculto para indicar remoção da imagem -->
                                    <input type="hidden" id="remover_foto" name="remover_foto" value="0">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@section('scripts')
    <script>
        var fotoAtual = "{{ $usuario->foto ? asset('storage/' . $usuario->foto) : '' }}";

        document.addEventListener("DOMContentLoaded", function() {
            var btnRemover = document.getElementById('btn-remover');
            var fotoPreview = document.getElementById('foto-preview');

            // Se já houver uma foto, mostrar o botão de remover
            if (fotoAtual) {
                btnRemover.style.display = 'inline-block';
            } else {
                fotoPreview.style.display = 'none'; // Oculta o contêiner da foto se não houver imagem
            }
        });

        function showPreview(event) {
            var file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('foto-preview').style.display = 'block';
                    document.getElementById('btn-remover').style.display = 'inline-block';
                    document.getElementById('remover_foto').value = '0'; // Evita remoção da imagem no backend
                };
                reader.readAsDataURL(file);
            }
        }

        function removePreview() {
            document.getElementById('foto').value = ''; // Limpa o input de arquivo
            document.getElementById('remover_foto').value = '1'; // Indica remoção da imagem no backend
            document.getElementById('preview-img').src = ''; // Remove a imagem do preview
            document.getElementById('foto-preview').style.display = 'none'; // Esconde o contêiner da foto
        }
    </script>
@endsection

@endsection
