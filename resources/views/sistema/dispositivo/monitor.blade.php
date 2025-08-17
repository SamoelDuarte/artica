@extends('sistema.layouts.app')

@section('css')
    <link href="{{ asset('/assets/admin/css/device.css') }}" rel="stylesheet">
    <style>
        .status-card {
            transition: all 0.3s ease;
        }
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-online {
            border-left: 4px solid #28a745;
        }
        .status-connecting {
            border-left: 4px solid #ffc107;
        }
        .status-offline {
            border-left: 4px solid #dc3545;
        }
        .auto-refresh {
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .last-check {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="page-header-content py-3">
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-heartbeat text-primary"></i> 
                Monitor de Dispositivos
            </h1>
            <div class="btn-group">
                <button class="btn btn-sm btn-success" id="btnForceCheck">
                    <i class="fas fa-sync-alt" id="syncIcon"></i> Verificar Agora
                </button>
                <button class="btn btn-sm btn-info" id="btnAutoRefresh">
                    <i class="fas fa-play"></i> Auto Refresh
                </button>
                <a href="{{ route('dispositivo.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-list"></i> Lista
                </a>
            </div>
        </div>

        <ol class="breadcrumb mb-0 mt-4">
            <li class="breadcrumb-item"><a href="/">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('dispositivo.index') }}">Dispositivos</a></li>
            <li class="breadcrumb-item active">Monitor</li>
        </ol>
    </div>

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Conectados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="connectedCount">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wifi fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Desconectados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="disconnectedCount">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wifi fa-2x text-danger" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Conectando</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="connectingCount">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Última Verificação</div>
                            <div class="h6 mb-0 text-gray-800" id="lastCheck">Carregando...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Grid -->
    <div class="row" id="devicesGrid">
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Carregando...</span>
            </div>
            <p class="mt-2">Carregando dispositivos...</p>
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
                <div class="mb-3">
                    <strong id="reconectar-device-name"></strong>
                </div>
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
<script>
// Variáveis globais
let autoRefreshInterval;
let isAutoRefresh = false;
let reconectarCountdown;
let statusCheckInterval;
let retryCount = 0;
const maxRetries = 3;

$(document).ready(function() {
    loadDevices();
    
    // Botão de verificação forçada
    $('#btnForceCheck').click(function() {
        forceStatusCheck();
    });
    
    // Botão de auto refresh
    $('#btnAutoRefresh').click(function() {
        toggleAutoRefresh();
    });
});

function loadDevices() {
    console.log('Iniciando loadDevices...');
    
    // Mostrar loading
    $('#devicesGrid').html(`
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Carregando...</span>
            </div>
            <p class="mt-2">Carregando dispositivos...</p>
        </div>
    `);
    
    $.ajax({
        url: '/dispositivo/getStatusAll',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(devices) {
            console.log('Sucesso ao carregar dispositivos:', devices);
            retryCount = 0; // Reset retry count on success
            updateStatistics(devices);
            renderDeviceCards(devices);
            updateLastCheck();
        },
        error: function(xhr) {
            console.error('Erro ao carregar dispositivos:', xhr);
            console.error('Status:', xhr.status);
            console.error('Response:', xhr.responseText);
            
            let errorMsg = 'Erro ao carregar dispositivos';
            
            // Verificar se é uma resposta HTML (redirecionamento para login)
            if (xhr.responseText && xhr.responseText.includes('<!DOCTYPE html>')) {
                errorMsg = 'Sessão expirada. <a href="/login" class="btn btn-sm btn-primary">Fazer Login</a>';
            } else if (xhr.status === 401 || xhr.status === 419) {
                errorMsg = 'Sessão expirada. <a href="/login" class="btn btn-sm btn-primary">Fazer Login</a>';
            } else if (xhr.status === 500) {
                errorMsg = 'Erro interno do servidor. Tente novamente.';
            } else if (xhr.status === 0) {
                errorMsg = 'Erro de conexão. Verifique sua internet.';
            }
            
            // Se não for erro de autenticação, tentar novamente
            if (xhr.status !== 401 && xhr.status !== 419 && !xhr.responseText.includes('<!DOCTYPE html>')) {
                if (retryCount < maxRetries) {
                    retryCount++;
                    console.log(`Tentando novamente... (${retryCount}/${maxRetries})`);
                    setTimeout(() => {
                        loadDevices();
                    }, 2000); // Tentar novamente em 2 segundos
                    return;
                }
                errorMsg += ` (Falhou após ${maxRetries} tentativas)`;
            }
            
            $('#devicesGrid').html('<div class="col-12"><div class="alert alert-warning text-center">' + errorMsg + '</div></div>');
        }
    });
}

function updateStatistics(devices) {
    const connected = devices.filter(d => d.status === 'open').length;
    const connecting = devices.filter(d => d.status === 'connecting').length;
    const disconnected = devices.length - connected - connecting;
    
    $('#connectedCount').text(connected);
    $('#connectingCount').text(connecting);
    $('#disconnectedCount').text(disconnected);
}

function renderDeviceCards(devices) {
    let html = '';
    
    if (devices.length === 0) {
        html = '<div class="col-12"><div class="alert alert-info">Nenhum dispositivo encontrado</div></div>';
    } else {
        devices.forEach(device => {
            let statusClass = 'status-offline';
            let statusIcon = 'fa-wifi text-danger';
            
            if (device.status === 'open') {
                statusClass = 'status-online';
                statusIcon = 'fa-wifi text-success';
            } else if (device.status === 'connecting') {
                statusClass = 'status-connecting';
                statusIcon = 'fa-spinner fa-pulse text-warning';
            }
            
            const statusText = device.display_status;
            
            html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card status-card ${statusClass} shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col">
                                    <h6 class="card-title mb-2">
                                        <i class="fas ${statusIcon}"></i>
                                        ${device.name || 'Sem nome'}
                                    </h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <strong>Status:</strong> ${statusText}<br>
                                            <strong>Última Recarga:</strong> ${device.data_ultima_recarga}<br>
                                            <strong>JID:</strong> ${device.jid || 'N/A'}<br>
                                            <strong>Session:</strong> <code>${device.session}</code>
                                        </small>
                                    </p>
                                </div>
                            </div>
                            <div class="row no-gutters">
                                <div class="col">
                                    <div class="last-check mb-2">
                                        <i class="fas fa-clock"></i> 
                                        Atualizado: ${device.last_check || 'há pouco'}
                                    </div>
                                </div>
                            </div>
                            <div class="row no-gutters">
                                <div class="col-12">
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="editDevice(${device.id})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="reconectarDevice(${device.id}, '${device.name}')" title="Reconectar">
                                            <i class="fas fa-wifi"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="atualizarRecarga(${device.id})" title="Atualizar Recarga">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    $('#devicesGrid').html(html);
}

function forceStatusCheck() {
    const $btn = $('#btnForceCheck');
    const $icon = $('#syncIcon');
    
    $btn.prop('disabled', true);
    $icon.addClass('auto-refresh');
    
    $.ajax({
        url: '/dispositivo/force-status-check',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                
                // Recarregar dados após 3 segundos
                setTimeout(() => {
                    loadDevices();
                }, 3000);
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao executar verificação'
            });
        },
        complete: function() {
            $btn.prop('disabled', false);
            $icon.removeClass('auto-refresh');
        }
    });
}

function toggleAutoRefresh() {
    const $btn = $('#btnAutoRefresh');
    const $icon = $btn.find('i');
    
    if (isAutoRefresh) {
        // Parar auto refresh
        clearInterval(autoRefreshInterval);
        isAutoRefresh = false;
        $btn.removeClass('btn-warning').addClass('btn-info');
        $icon.removeClass('fa-stop').addClass('fa-play');
        $btn.html('<i class="fas fa-play"></i> Auto Refresh');
    } else {
        // Iniciar auto refresh
        autoRefreshInterval = setInterval(() => {
            loadDevices();
        }, 30000); // A cada 30 segundos
        
        isAutoRefresh = true;
        $btn.removeClass('btn-info').addClass('btn-warning');
        $icon.removeClass('fa-play').addClass('fa-stop');
        $btn.html('<i class="fas fa-stop"></i> Parar Auto Refresh');
    }
}

function updateLastCheck() {
    const now = new Date();
    $('#lastCheck').text(now.toLocaleString('pt-BR'));
}

// Funções para os botões dos cards
function editDevice(id) {
    // Abrir o modal de edição diretamente no monitor
    loadDeviceForEdit(id);
}

function loadDeviceForEdit(id) {
    $.ajax({
        url: "/dispositivo/" + id + "/get",
        type: "GET",
        success: function(data) {
            // Criar modal dinamicamente se não existir
            if (!$('#modalEditDevice').length) {
                createEditModal();
            }
            
            $('#edit_device_id_monitor').val(data.id);
            $('#edit_device_name_monitor').val(data.name);
            
            // Converter a data formatada de volta para o formato datetime-local
            if (data.data_ultima_recarga) {
                const date = new Date(data.data_ultima_recarga);
                date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                $('#edit_data_ultima_recarga_monitor').val(date.toISOString().slice(0, 16));
            }
            
            $('#modalEditDevice').modal('show');
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao carregar dados do dispositivo'
            });
        }
    });
}

function createEditModal() {
    const modalHtml = `
        <div class="modal fade" id="modalEditDevice" tabindex="-1" role="dialog" aria-labelledby="modalEditDeviceLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditDeviceLabel">Editar Dispositivo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_device_id_monitor">
                        <div class="form-group">
                            <label for="edit_device_name_monitor">Nome do dispositivo:</label>
                            <input type="text" id="edit_device_name_monitor" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Última Recarga:</label>
                            <div class="input-group">
                                <input type="datetime-local" id="edit_data_ultima_recarga_monitor" class="form-control">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info btn-sm" onclick="atualizarRecargaModal()">
                                        <i class="fas fa-sync-alt"></i> Agora
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="salvarEdicaoMonitor()">Salvar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('body').append(modalHtml);
}

function atualizarRecargaModal() {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('#edit_data_ultima_recarga_monitor').val(now.toISOString().slice(0, 16));
    
    Swal.fire({
        icon: 'success',
        title: 'Sucesso',
        text: 'Data atualizada para agora! Clique em "Salvar" para confirmar.',
        timer: 3000,
        showConfirmButton: false
    });
}

function salvarEdicaoMonitor() {
    const data = {
        id: $('#edit_device_id_monitor').val(),
        name: $('#edit_device_name_monitor').val(),
        data_ultima_recarga: $('#edit_data_ultima_recarga_monitor').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: "/dispositivo/update",
        type: "POST",
        data: data,
        success: function(response) {
            $('#modalEditDevice').modal('hide');
            loadDevices(); // Recarregar dispositivos
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: 'Dispositivo atualizado com sucesso'
            });
        },
        error: function(response) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: response.responseJSON.message || 'Erro ao atualizar dispositivo'
            });
        }
    });
}

function reconectarDevice(deviceId, deviceName) {
    $('#reconectar-device-name').text(deviceName);
    $('#modalReconectar').modal('show');
    
    // Reset do modal
    $('#reconectar-loading').show();
    $('#reconectar-qr').hide();
    $('#reconectar-erro').hide();
    
    $.ajax({
        url: "/dispositivo/reconectar",
        type: "POST",
        data: {
            id: deviceId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#reconectar-loading').hide();
                // Verificar se o QR code já vem com o prefixo data:image/png;base64,
                let qrCodeSrc = response.qrcode;
                if (!qrCodeSrc.startsWith('data:image/png;base64,')) {
                    qrCodeSrc = 'data:image/png;base64,' + response.qrcode;
                }
                $('#qr-reconectar-img').attr('src', qrCodeSrc);
                $('#reconectar-qr').show();
                
                // Iniciar countdown
                let seconds = 60;
                reconectarCountdown = setInterval(function() {
                    $('#reconectar-countdown').text(seconds);
                    seconds--;
                    
                    if (seconds < 0) {
                        clearInterval(reconectarCountdown);
                        $('#reconectar-timer').html('<span class="text-danger">QR Code expirado</span>');
                        // Parar verificação de status quando expirar
                        if (statusCheckInterval) {
                            clearInterval(statusCheckInterval);
                        }
                    }
                }, 1000);
                
                // Iniciar verificação de status a cada 3 segundos
                startStatusCheck(deviceId, deviceName);
            } else {
                $('#reconectar-loading').hide();
                $('#reconectar-erro-msg').text(response.message);
                $('#reconectar-erro').show();
            }
        },
        error: function(response) {
            $('#reconectar-loading').hide();
            $('#reconectar-erro-msg').text('Erro inesperado durante a reconexão');
            $('#reconectar-erro').show();
        }
    });
}

function atualizarRecarga(deviceId) {
    Swal.fire({
        title: 'Confirmar Atualização',
        text: 'Deseja atualizar a última recarga para agora?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, atualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/dispositivo/update-recarga",
                type: "POST", 
                data: {
                    id: deviceId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: 'Última recarga atualizada!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadDevices(); // Recarregar dispositivos
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao atualizar recarga'
                    });
                }
            });
        }
    });
}

function startStatusCheck(deviceId, deviceName) {
    // Primeiro buscar a session do dispositivo
    $.ajax({
        url: "/dispositivo/" + deviceId + "/get",
        type: "GET",
        success: function(deviceData) {
            const session = deviceData.session;
            
            statusCheckInterval = setInterval(function() {
                // Verificar status diretamente na Evolution API
                $.ajax({
                    url: "/dispositivo/check-evolution-status",
                    type: "POST",
                    data: {
                        session: session,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success && response.connected) {
                            // Parar todos os intervals
                            if (statusCheckInterval) {
                                clearInterval(statusCheckInterval);
                            }
                            if (reconectarCountdown) {
                                clearInterval(reconectarCountdown);
                            }
                            
                            // Atualizar status no banco de dados
                            updateDeviceStatusInDatabase(deviceId, 'open');
                            
                            // Fechar modal
                            $('#modalReconectar').modal('hide');
                            
                            // Mostrar sucesso
                            Swal.fire({
                                icon: 'success',
                                title: 'Conectado!',
                                text: `${deviceName} foi conectado com sucesso!`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                            
                            // Recarregar dispositivos
                            loadDevices();
                        }
                    },
                    error: function() {
                        console.log('Erro ao verificar status na Evolution API');
                    }
                });
            }, 3000); // Verificar a cada 3 segundos
        },
        error: function() {
            console.log('Erro ao obter dados do dispositivo');
        }
    });
}

function updateDeviceStatusInDatabase(deviceId, status) {
    $.ajax({
        url: "/dispositivo/updateStatus",
        type: "POST",
        data: {
            id: deviceId,
            status: status,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Status atualizado no banco de dados');
        },
        error: function() {
            console.log('Erro ao atualizar status no banco de dados');
        }
    });
}

// Limpar countdown quando modal for fechado
$('#modalReconectar').on('hidden.bs.modal', function () {
    if (reconectarCountdown) {
        clearInterval(reconectarCountdown);
    }
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
});

// Limpar interval ao sair da página
$(window).on('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endsection
