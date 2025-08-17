@extends('sistema.layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="page-header-content py-3">
            <h1 class="h3 mb-0 text-gray-800">Novo Cliente</h1>
            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Início</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cliente.index') }}">Clientes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Novo Cliente</li>
            </ol>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('cliente.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>

                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" required>
                            </div>

                            <div class="mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep" maxlength="9">
                            </div>

                            <div class="mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="estado" name="estado" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea name="observacoes" id="observacoes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/*"
                                    onchange="showPreview(event)">
                            </div>

                            <div id="foto-preview" style="display: none; position: relative;">
                                <img id="preview-img" src="#" alt="Preview da Foto"
                                    style="max-width: 100px; border-radius: 10px;">
                                <span class="remove-preview" onclick="removePreview()"
                                    style="cursor:pointer; color: red; position: absolute; top: -10px; right: -10px; font-size: 20px;">X</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Criar Cliente</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showPreview(event) {
            var file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('foto-preview').style.display = 'flex';
                };
                reader.readAsDataURL(file);
            } else {
                removePreview();
            }
        }

        function removePreview() {
            document.getElementById('foto').value = '';
            document.getElementById('preview-img').src = '';
            document.getElementById('foto-preview').style.display = 'none';
        }

        // Buscar cidade e estado pelo CEP
        document.getElementById('cep').addEventListener('blur', function () {
            let cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('estado').value = data.uf;
                            document.getElementById('cidade').readOnly = true;
                            document.getElementById('estado').readOnly = true;
                        } else {
                            liberarCamposCidadeEstado();
                        }
                    })
                    .catch(() => {
                        liberarCamposCidadeEstado();
                    });
            } else {
                liberarCamposCidadeEstado();
            }
        });

        function liberarCamposCidadeEstado() {
            document.getElementById('cidade').readOnly = false;
            document.getElementById('estado').readOnly = false;
            document.getElementById('cidade').value = '';
            document.getElementById('estado').value = '';
        }
    </script>
@endsection
