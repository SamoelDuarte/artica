@extends('sistema.layouts.app')

@section('css')
    <link href="{{ asset('/assets/admin/css/styles.css') }}" rel="stylesheet">
@endsection
<style>
    /* Estilo para o input de arquivo */
    .custom-file-input::-webkit-file-upload-button {
        visibility: hidden;
    }

    .custom-file-input::before {
        content: 'Selecionar arquivo';
        display: inline-block;
        background: #007bff;
        color: #fff;
        border: 1px solid #007bff;
        border-radius: 5px;
        padding: 8px 12px;
        outline: none;
        cursor: pointer;
    }

    .custom-file-input:hover::before {
        background: #0056b3;
    }

    .custom-file-input:active::before {
        background: #0056b3;
    }

    .custom-file-input:focus::before {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .radio-img {
        display: inline-block;
        margin-right: 10px;
    }

    .radio-img input[type="radio"] {
        display: none;
    }

    .radio-img img {
        width: 123px;
        height: 123px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .radio-img input[type="radio"]:checked+img {
        border-color: #007bff;
        /* Cor de destaque quando selecionado */
        width: 150px;
        height: 150px;
        /* Torna a borda mais redonda */
        border-radius: 50%;
        /* Adiciona uma sombra */
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        /* Remove o contorno padrão */
        outline: none;
    }

    .radio-btn {
        display: inline-block;
        margin: 5px;
    }

    .radio-btn input[type="radio"] {
        display: none;
    }

    .radio-btn label {
        display: inline-block;
        padding: 10px 20px;
        background: #28a745;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .radio-btn input[type="radio"]:checked+label {
        background: #265330;
        box-shadow: 0 0 0 0.2rem rgb(0 0 0 / 92%);
        animation: shake 0.5s;
    }

    @keyframes shake {
        0% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        50% {
            transform: translateX(5px);
        }

        75% {
            transform: translateX(-5px);
        }

        100% {
            transform: translateX(0);
        }
    }
</style>

@section('content')
    <div class="container mt-4">
        <div class="page-header-content py-3">
            <h1 class="h3 mb-0 text-gray-800">Envio em Massa</h1>
            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Início</a></li>
                <li class="breadcrumb-item"><a href="{{ route('message.index') }}">Relatório de Envio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Envio em Massa</li>
            </ol>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- Formulário de Upload de Imagem --}}
                <form id="formImagem" action="{{ route('upload.imagem') }}" method="POST" enctype="multipart/form-data"
                    class="mb-4">
                    @csrf
                    <div class="mb-3 d-flex align-items-center">
                        <input type="file" class="form-control me-2" id="inputImagem" name="imagem" accept="image/*"
                            onchange="document.getElementById('formImagem').submit()">
                        <label class="btn btn-primary mb-0" for="inputImagem">Inserir Mais Imagem</label>
                    </div>
                </form>

                {{-- Formulário principal --}}
                <form id="myForm" action="{{ route('message.bulk') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        {{-- Coluna Esquerda --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título da Campanha</label>
                                <input type="text" name="titulo" id="titulo"
                                    class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}">
                                @error('titulo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="texto" class="form-label">Mensagem</label>
                                <textarea name="texto" id="texto" class="form-control @error('texto') is-invalid @enderror" rows="3">{{ old('texto') }}</textarea>
                                @error('texto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Selecione a Lista de Contato</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($contacts as $contact)
                                        <div>
                                            <input type="radio" class="btn-check" name="contact_id"
                                                id="contact{{ $contact->id }}" value="{{ $contact->id }}"
                                                autocomplete="off"
                                                {{ old('contact_id') == $contact->id ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success" for="contact{{ $contact->id }}">
                                                {{ $contact->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- <div class="mb-3">
                                <label for="csvFile" class="form-label">Importar CSV (opcional)</label>
                                <input type="file" class="form-control" id="csvFile" name="csvFile">
                            </div> --}}

                            <button type="submit" class="btn btn-success">Enviar</button>
                        </div>

                        {{-- Coluna Direita --}}
                        <div class="col-md-6">
                            <div class="right-input" style="display: flex; align-items: center;">
                                <!-- Aqui estão os radio buttons com as imagens -->
                                @foreach ($imagens as $imagem)
                                    <label class="radio-img">
                                        <input type="radio" name="imagem_id" value="{{ $imagem->id }}"
                                            {{ old('imagem_id') == $imagem->id ? 'checked' : '' }}>
                                        <img src="{{ asset($imagem->caminho) }}" alt="Imagem">
                                    </label>
                                @endforeach
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Selecione os Dispositivos</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($devices as $device)
                                        <div>
                                            <input type="checkbox" class="btn-check" name="devices[]"
                                                id="device{{ $device->id }}" value="{{ $device->id }}"
                                                {{ is_array(old('devices')) && in_array($device->id, old('devices')) ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary" for="device{{ $device->id }}">
                                                {{ $device->alias ?? "TEL: #$device->name" }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>


                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
