@extends('sistema.layouts.app')


@section('css')
    <link href="{{ asset('/assets/admin/css/device.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container mt-4">
        <!-- Page Heading -->
        <div class="page-header-content py-3">

            <div class="d-sm-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0 text-gray-800">Dispositivos</h1>
                <div class="btn-group">
                    <a href="{{ route('dispositivo.monitor') }}"
                        class="btn btn-sm btn-info shadow-sm">
                        <i class="fas fa-heartbeat text-white-50"></i> Monitor
                    </a>
                    <a href="{{ route('dispositivo.create') }}"
                        class="btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus text-white-50"></i> Novo Dispositivo
                    </a>
                </div>
            </div>

            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dispositivos</li>
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
                            <table class="table table-bordered" id="table-device">

                                <thead>
                                    <tr>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Última Recarga</th>
                                        <th scope="col">Intervalo Inicial</th>
                                        <th scope="col">Intervalo Final</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>


    <!-- Modal -->
    <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h5 class="py-3 m-0">Tem certeza que deseja excluir este Dispositivo?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Fechar</button>
                    <form action="{{ route('dispositivo.delete') }}" method="post" class="float-right">
                        @csrf
                        <input type="hidden" id="id_device" name="id_device">
                        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Editar Dispositivo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_device_name">Nome do dispositivo:</label>
                        <input type="text" id="edit_device_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Última Recarga:</label>
                        <div class="input-group">
                            <input type="datetime-local" id="edit_data_ultima_recarga_input" class="form-control">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info btn-sm" id="btnAtualizarRecarga">
                                    <i class="fas fa-sync-alt"></i> Agora
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Intervalo Inicial:</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="input-group">
                                    <input type="number" id="edit_start_minutes" class="form-control" min="0" placeholder="Minutos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <input type="number" id="edit_start_seconds" class="form-control" min="0" max="59" placeholder="Segundos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">seg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Intervalo Final:</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="input-group">
                                    <input type="number" id="edit_end_minutes" class="form-control" min="0" placeholder="Minutos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <input type="number" id="edit_end_seconds" class="form-control" min="0" max="59" placeholder="Segundos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">seg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="edit_device_id">
                    <button type="button" class="btn btn-warning" id="btnReconectar">
                        <i class="fas fa-wifi"></i> Reconectar
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEdit">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Reconexão -->
    <div class="modal fade" id="modalReconectar" tabindex="-1" role="dialog" aria-labelledby="modalReconectarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReconectarLabel">Reconectando Dispositivo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div id="reconectar-loading" class="mb-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Carregando...</span>
                        </div>
                        <p class="mt-2">Gerando nova sessão...</p>
                    </div>
                    <div id="reconectar-qr" style="display: none;">
                        <p class="mb-3">Escaneie o QR Code abaixo com seu WhatsApp:</p>
                        <img id="qr-reconectar-img" src="" alt="QR Code Reconexão" class="img-fluid mb-3" style="max-width: 300px;" />
                        <div id="reconectar-timer" class="text-center text-muted">
                            O código expira em <span id="reconectar-countdown">60</span> segundos
                        </div>
                        <div class="text-center text-info mt-2">
                            <small><i class="fas fa-sync fa-spin"></i> Verificando status automaticamente...</small>
                        </div>
                    </div>
                    <div id="reconectar-erro" style="display: none;" class="alert alert-danger">
                        <strong>Erro:</strong> <span id="reconectar-erro-msg"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('/assets/admin/js/dispositivo/index.js') }}"></script>
@endsection
