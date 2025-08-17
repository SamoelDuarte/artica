var url = window.location.origin;
$('#table-device').DataTable({
    processing: true,
    serverSide: true,
    "ajax": {
        "url": url + "/dispositivo/getDevices",
        "type": "GET"
    },
    "columns": [
        {
            "data": "name"
        },
        {
            "data": "display_status"
        },
        {
            "data": "data_ultima_recarga_formatada"
        },
        {
            "data": null,
            "render": function(data, type, row) {
                if (row.start_minutes !== null && row.start_seconds !== null) {
                    return row.start_minutes + 'min ' + row.start_seconds + 'seg';
                }
                return '-';
            }
        },
        {
            "data": null,
            "render": function(data, type, row) {
                if (row.end_minutes !== null && row.end_seconds !== null) {
                    return row.end_minutes + 'min ' + row.end_seconds + 'seg';
                }
                return '-';
            }
        },
        {
            "data": "actions",
            "orderable": false,
            "searchable": false
        }
    ],
    'columnDefs': [
        {
            targets: [2, 3, 4, 5],
            className: 'dt-body-center'
        }
    ],
    'rowCallback': function (row, data, index) {
        let btn = 'success';
        let statusText = 'Desconectado';
        
        if (data && data['display_status']) {
            statusText = data['display_status'];
            if (data['display_status'] == "Conectado") {
                btn = "success";
            } else if (data['display_status'] == "Conectando") {
                btn = "warning";
            } else {
                btn = "danger";
            }
        } else {
            btn = "danger";
        }
        
        $('td:eq(1)', row).html('<button class="btn btn-' + btn + '">' + statusText + '</button>');
    },
});

function configModalDelete(id) {
    $('#id_device').val(id);
}

function editDevice(id) {
    // Buscar dados do dispositivo
    $.ajax({
        url: "/dispositivo/" + id + "/get",
        type: "GET",
        success: function(data) {
            $('#edit_device_id').val(data.id);
            $('#edit_device_name').val(data.name);
            
            // Converter a data formatada de volta para o formato datetime-local
            if (data.data_ultima_recarga) {
                const date = new Date(data.data_ultima_recarga);
                date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                $('#edit_data_ultima_recarga_input').val(date.toISOString().slice(0, 16));
            }
            
            $('#edit_start_minutes').val(data.start_minutes);
            $('#edit_start_seconds').val(data.start_seconds);
            $('#edit_end_minutes').val(data.end_minutes);
            $('#edit_end_seconds').val(data.end_seconds);
            $('#modalEdit').modal('show');
        }
    });
}

// Função para validar os intervalos de tempo na edição
function validateEditTimeIntervals() {
    const startMinutes = parseInt($('#edit_start_minutes').val()) || 0;
    const startSeconds = parseInt($('#edit_start_seconds').val()) || 0;
    const endMinutes = parseInt($('#edit_end_minutes').val()) || 0;
    const endSeconds = parseInt($('#edit_end_seconds').val()) || 0;

    const startTotal = (startMinutes * 60) + startSeconds;
    const endTotal = (endMinutes * 60) + endSeconds;

    return {
        isValid: startTotal < endTotal,
        startTotal,
        endTotal
    };
}

// Validar campos do modal de edição
$('#modalEdit input').on('input', function() {
    const timeValidation = validateEditTimeIntervals();
    if (!timeValidation.isValid) {
        $('#edit_end_minutes').get(0).setCustomValidity('O intervalo final deve ser maior que o inicial');
        $('#edit_end_seconds').get(0).setCustomValidity('O intervalo final deve ser maior que o inicial');
    } else {
        $('#edit_end_minutes').get(0).setCustomValidity('');
        $('#edit_end_seconds').get(0).setCustomValidity('');
    }
});

// Salvar edição
$('#btnSaveEdit').click(function() {
    const timeValidation = validateEditTimeIntervals();
    if (!timeValidation.isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'O intervalo final deve ser maior que o inicial'
        });
        return;
    }

    const data = {
        id: $('#edit_device_id').val(),
        name: $('#edit_device_name').val(),
        data_ultima_recarga: $('#edit_data_ultima_recarga_input').val(),
        start_minutes: $('#edit_start_minutes').val(),
        start_seconds: $('#edit_start_seconds').val(),
        end_minutes: $('#edit_end_minutes').val(),
        end_seconds: $('#edit_end_seconds').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: "/dispositivo/update",
        type: "POST",
        data: data,
        success: function(response) {
            $('#modalEdit').modal('hide');
            $('#table-device').DataTable().ajax.reload();
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
});

// Atualizar última recarga
$('#btnAtualizarRecarga').click(function() {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('#edit_data_ultima_recarga_input').val(now.toISOString().slice(0, 16));
    
    Swal.fire({
        icon: 'success',
        title: 'Sucesso',
        text: 'Data atualizada para agora! Clique em "Salvar" para confirmar.',
        timer: 3000,
        showConfirmButton: false
    });
});

// Reconectar dispositivo
let reconectarCountdown;
let statusCheckInterval;
$('#btnReconectar').click(function() {
    const deviceId = $('#edit_device_id').val();
    
    // Fechar modal de edição e abrir modal de reconexão
    $('#modalEdit').modal('hide');
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
                const deviceName = $('#edit_device_name').val();
                startStatusCheckIndex(deviceId, deviceName);
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
});

function startStatusCheckIndex(deviceId, deviceName) {
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
                            updateDeviceStatusInDatabaseIndex(deviceId, 'open');
                            
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
                            
                            // Recarregar tabela
                            $('#table-device').DataTable().ajax.reload();
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

// Função para atualizar status do dispositivo no banco de dados
function updateDeviceStatusInDatabaseIndex(deviceId, status) {
    $.ajax({
        url: "/dispositivo/" + deviceId + "/update-status",
        type: "POST",
        data: {
            status: status,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Status do dispositivo atualizado no banco de dados');
        },
        error: function() {
            console.log('Erro ao atualizar status do dispositivo no banco de dados');
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