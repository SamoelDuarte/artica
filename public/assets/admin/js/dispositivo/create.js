const inputNome = document.getElementById('device_name');
const btnGerar = document.getElementById('createDeviceBtn');
const qrCard = document.getElementById('qrCard');
const qrExpired = document.getElementById("qr-expired");

let session = '';
let id_device = '';
let conectado = false;

// Definir data e hora atual como padrão para o campo data_ultima_recarga
document.addEventListener('DOMContentLoaded', function() {
    const dataUltimaRecargaInput = document.getElementById('data_ultima_recarga');
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    dataUltimaRecargaInput.value = now.toISOString().slice(0, 16);
});

// Função para validar os intervalos de tempo
function validateTimeIntervals() {
    const startMinutes = parseInt(document.getElementById('start_minutes').value) || 0;
    const startSeconds = parseInt(document.getElementById('start_seconds').value) || 0;
    const endMinutes = parseInt(document.getElementById('end_minutes').value) || 0;
    const endSeconds = parseInt(document.getElementById('end_seconds').value) || 0;

    const startTotal = (startMinutes * 60) + startSeconds;
    const endTotal = (endMinutes * 60) + endSeconds;

    return {
        isValid: startTotal < endTotal,
        startTotal,
        endTotal
    };
}

// Validar todos os campos quando houver mudança
['device_name', 'data_ultima_recarga', 'start_minutes', 'start_seconds', 'end_minutes', 'end_seconds'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        const nome = inputNome.value.trim();
        const dataUltimaRecarga = document.getElementById('data_ultima_recarga').value;
        const timeValidation = validateTimeIntervals();
        
        if (!timeValidation.isValid) {
            document.getElementById('end_minutes').setCustomValidity('O intervalo final deve ser maior que o inicial');
            document.getElementById('end_seconds').setCustomValidity('O intervalo final deve ser maior que o inicial');
        } else {
            document.getElementById('end_minutes').setCustomValidity('');
            document.getElementById('end_seconds').setCustomValidity('');
        }

        btnGerar.disabled = nome.length === 0 || 
                           nome.toLowerCase() === 'zaxio' || 
                           dataUltimaRecarga === '' ||
                           !timeValidation.isValid;
    });
});

btnGerar.addEventListener('click', function () {
    const nome = inputNome.value.trim();
    qrExpired.style.display = "none";
    if (nome === '' || nome.toLowerCase() === 'zaxio') return;

    // Desabilita campo e esconde botão
    inputNome.disabled = true;
    btnGerar.style.display = 'none';

    // Requisição AJAX
    $.ajax({
        url: "/dispositivo/criar",
        type: "POST",
        data: {
            nome: nome,
            data_ultima_recarga: document.getElementById('data_ultima_recarga').value,
            start_minutes: document.getElementById('start_minutes').value,
            start_seconds: document.getElementById('start_seconds').value,
            end_minutes: document.getElementById('end_minutes').value,
            end_seconds: document.getElementById('end_seconds').value,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {
            session = data.session;
            id_device = data.id;
            $('#session_device').val(session);
            $('#id_device').val(id_device);

            const qrImg = document.getElementById('qrcode-img');
            qrImg.src = data.qrcode;
            qrImg.style.display = 'block';

            document.getElementById('qr-timer').style.display = 'block';
            qrCard.style.display = 'block';

            startCountdown();
            startVerificacao();
        }
    });
});

function startCountdown() {
    let countdown = 10;
    const countdownSpan = document.getElementById("countdown");
    const qrTimer = document.getElementById("qr-timer");
   
    const qrcodeImg = document.getElementById("qrcode-img");

    // Garante que o texto comece em 10
    countdownSpan.innerText = countdown;

    const interval = setInterval(() => {
        if (conectado) return clearInterval(interval);
        countdown--;
        countdownSpan.innerText = countdown;

        if (countdown <= 0) {
            clearInterval(interval);
            qrTimer.style.display = "none";
            qrcodeImg.style.display = "none";
            qrExpired.style.display = "block";
        
            if (!conectado) {
                inputNome.disabled = false;
                btnGerar.style.display = 'inline-block';
            }
        }
        
    }, 1000);
}


// Verificação status
function startVerificacao() {
    const footerQrCode = document.getElementById("footer-qr-code");

    const intervalId = setInterval(() => {
        if (conectado) return clearInterval(intervalId);

      $.ajax({
                url: "/dispositivo/getStatus",
        type: "GET",
        data: { sessionId: session },
        success: function (response) {
            let res = response.data; // Corrigido: acessar a chave 'data'
            
            if (res.instance && res.instance.state === 'open') {
                conectado = true;
                $('#qrcode-img').hide();
                $('#qr-timer').hide();
                $('#footer-qr-code').show();

                $.ajax({
                    url: "updateStatus",
                    method: "POST",
                    data: JSON.stringify({
                        id: id_device,
                        status: res.instance.state,
                        jid: res.instance.instanceName,
                        picture: null, // Sem picture na resposta atual
                        nome: inputNome.value
                    }),
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "Conectado com sucesso.",
                    showConfirmButton: false,
                    timer: 5000,
                        });
                    }
                }
            });


    }, 1000);
}

// var newSession = {
//     "url": "http://localhost:3333/sessions/add",
//     "method": "POST",
//     "timeout": 0,
//     "headers": {
//         "secret": "$2a$12$VruN7Mf0FsXW2mR8WV0gTO134CQ54AmeCR.ml3wgc9guPSyKtHMgC",
//         "Content-Type": "application/json"
//     },
//     "data": JSON.stringify({
//         "sessionId": session
//     }),
// };

// $.ajax(newSession).done(function (response) {

//     qrcodeImg.src = response['qr'];
// });



// Função de verificação que será executada a cada 5 segundos
// function verificarCondicaos() {


//     var settings = {
//         "url": "http://localhost:3333/sessions/" + session + "/status",
//         "method": "GET",
//         "timeout": 0,
//         "headers": {
//             "secret": "$2a$12$VruN7Mf0FsXW2mR8WV0gTO134CQ54AmeCR.ml3wgc9guPSyKtHMgC"
//         },
//     };

//     $.ajax(settings).done(function (response) {
        
//     });
// }

// Definir o intervalo de verificação a cada 5 segundos se foi authicado


// function updateName() {
//     var name_device = $("#name_device").val();
//     var updateName = {
//         "url": "updateName",
//         "method": "POST",
//         "timeout": 0,
//         "data": JSON.stringify({
//             "name": name_device,
//             "id": id_device,

//         }),
//         "headers": {
//             "Content-Type": "application/json",
//             "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
//         }
//     };

//     $.ajax(updateName).done(function (response) {

//         const Toast = Swal.mixin({
//             toast: true,
//             position: 'top-end',
//             showConfirmButton: false,
//             timer: 5000,
//             timerProgressBar: true,
//             didOpen: (toast) => {
//                 toast.addEventListener('mouseenter', Swal.stopTimer)
//                 toast.addEventListener('mouseleave', Swal.resumeTimer)
//             }
//         })

//         Toast.fire({
//             icon: 'success',
//             title: "Nome Atualizado  com sucesso.",
//         })

//     });



// }