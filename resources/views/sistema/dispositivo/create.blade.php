@extends('sistema.layouts.app')

@section('css')
@endsection

@section('content')
    <div class="container mt-4">
        <div class="page-header-content py-3">

            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Início</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dispositivo.index') }}">Dispositivos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Novo Dispositivo</li>
            </ol>

        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card-header">
                </div>
                <div class="card qr-code" id="qrCard">
                    <div id="preload" style="display: none;">
                        <div class="loader"></div>
                    </div>

                    <img id="qrcode-img" src="" alt="QR Code" style="display: none;" />


                    <div id="qr-timer" class="text-center text-muted mt-2" style="display: none;">
                        O código expira em <span id="countdown">10</span> segundos
                    </div>
                    <div id="qr-expired" class="text-center text-danger mt-2" style="display: none;">
                        QR Code expirado. Clique novamente para gerar outro.
                    </div>

                    <div class="form-group p-3">
                        <label for="device_name">Nome do dispositivo:</label>
                        <input type="text" id="device_name" class="form-control" placeholder="Ex: Celular João" required>
                    </div>

                    <div class="form-group px-3">
                        <label for="data_ultima_recarga">Última Recarga:</label>
                        <input type="datetime-local" id="data_ultima_recarga" class="form-control" required>
                    </div>

                    <div class="form-group px-3">
                        <label>Intervalo Inicial:</label>
                        <div class="row">
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="number" id="start_minutes"  class="form-control" min="0" placeholder="Minutos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="number" id="start_seconds" value="0" class="form-control" min="0" max="59" placeholder="Segundos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">seg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group px-3 pb-3">
                        <label>Intervalo Final:</label>
                        <div class="row">
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="number" id="end_minutes" class="form-control" min="0" placeholder="Minutos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="number" id="end_seconds" value="0" class="form-control" min="0" max="59" placeholder="Segundos" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">seg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-3">
                        <button id="createDeviceBtn" class="btn btn-primary" disabled>
                            <i class="fas fa-qrcode"></i> Gerar QR Code
                        </button>
                    </div>

                    <div class="card-footer server_connect" id="footer-qr-code" style="display: none;">
                        <div>
                            Conectado 😎
                        </div>
                    </div>
                </div>


            </div>
            <div class="col-md-6">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Como Escanear?</h4>
                            <div class="card-header-action">
                                <a href="#" class="btn btn-sm btn-neutral">
                                    <i class="fas fa-lightbulb"></i>&nbspGuia
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <img src="{{ asset('assets/images/scan-demo.gif') }}" class="w-100">
                        </div>
                        <div class="card-footer">
                            <div class="activities">
                                <div class="activity">
                                    <div class="activity-icon bg-primary text-white shadow-primary">
                                        <i class="ni ni-mobile-button"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-primary">Passo 1</span>
                                            <span class="bullet"></span>
                                        </div>
                                        <p>Abra o WhatsApp no seu celular</p>
                                    </div>
                                </div>
                                <div class="activity">
                                    <div class="activity-icon bg-primary text-white shadow-primary">
                                        <i class="ni ni-active-40"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-primary">Passo 2</span>
                                            <span class="bullet"></span>
                                        </div>
                                        <p>Toque no Menu ou Configurações e selecione Dispositivos Vinculados</p>
                                    </div>
                                </div>
                                <div class="activity">
                                    <div class="activity-icon bg-primary text-white shadow-primary">
                                        <i class="ni ni-active-40"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-primary">Passo 3</span>
                                            <span class="bullet"></span>
                                        </div>
                                        <p>Toque em Vincular um Dispositivo</p>
                                    </div>
                                </div>
                                <div class="activity">
                                    <div class="activity-icon bg-primary text-white shadow-primary">
                                        <i class="fa fa-qrcode"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-primary">Passo 4</span>
                                            <span class="bullet"></span>
                                        </div>
                                        <p>Aponte seu celular para esta tela para capturar o código</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="id" id="id_device" value="{{ $device->id }}">
                <input type="hidden" name="session" id="session_device" value="{{ $device->session }}">
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('/assets/admin/js/dispositivo/create.js') }}"></script>
@endsection
