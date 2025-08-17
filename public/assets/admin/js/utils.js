function buscarEnderecoPorCep(cepSelector, campos) {
    const cepInput = document.querySelector(cepSelector);

    if (!cepInput) return;

    // Impede digitar qualquer coisa que não seja número e limita a 8 caracteres
    cepInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });

    cepInput.addEventListener('blur', function () {
        const cep = cepInput.value;

        if (cep.length !== 8) {
            limparCampos(campos);
            return;
        }

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    preencherCampos(campos, data);
                } else {
                    liberarCampos(campos);
                    limparCampos(campos);
                }
            })
            .catch(() => {
                liberarCampos(campos);
                limparCampos(campos);
            });
    });

    function preencherCampos(campos, data) {
        if (campos.logradouro) document.querySelector(campos.logradouro).value = data.logradouro || '';
        if (campos.bairro) document.querySelector(campos.bairro).value = data.bairro || '';
        if (campos.cidade) {
            document.querySelector(campos.cidade).value = data.localidade || '';
            document.querySelector(campos.cidade).readOnly = true;
        }
        if (campos.estado) {
            document.querySelector(campos.estado).value = data.uf || '';
            document.querySelector(campos.estado).readOnly = true;
        }
    }

    function limparCampos(campos) {
        if (campos.logradouro) document.querySelector(campos.logradouro).value = '';
        if (campos.bairro) document.querySelector(campos.bairro).value = '';
        if (campos.cidade) document.querySelector(campos.cidade).value = '';
        if (campos.estado) document.querySelector(campos.estado).value = '';
    }

    function liberarCampos(campos) {
        if (campos.cidade) document.querySelector(campos.cidade).readOnly = false;
        if (campos.estado) document.querySelector(campos.estado).readOnly = false;
    }
}

function initGoogleAutocomplete(inputSelector, campos) {
    const input = document.querySelector(inputSelector);
    if (!input) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['address'],
        componentRestrictions: { country: 'br' },
    });

    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();
        const components = place.address_components;

        let logradouro = '';
        let bairro = '';
        let cidade = '';
        let cep = '';
        let numero = '';

        components.forEach(component => {
            const types = component.types;

            if (types.includes('street_number')) {
                numero = component.long_name;
            }

            if (types.includes('route')) {
                logradouro = component.long_name;
            }

            if (types.includes('sublocality') || types.includes('sublocality_level_1')) {
                bairro = component.long_name;
            }

            if (types.includes('administrative_area_level_2')) {
                cidade = component.long_name;
            }

            if (types.includes('postal_code')) {
                cep = component.long_name.replace(/\D/g, ''); // ✅ remove hífens e letras
            }
        });

        // Se o número não veio de street_number, tenta extrair do endereço completo
        if (!numero && place.formatted_address) {
            const match = place.formatted_address.match(/(\d{1,5})/);
            if (match) {
                numero = match[1];
            }
        }

        // Preenche os campos
        if (campos.logradouro) document.querySelector(campos.logradouro).value = logradouro;
        if (campos.bairro) document.querySelector(campos.bairro).value = bairro;
        if (campos.cidade) document.querySelector(campos.cidade).value = cidade;
        if (campos.cep) document.querySelector(campos.cep).value = cep;
        if (campos.numero) document.querySelector(campos.numero).value = numero;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const telefoneInput = document.querySelector('#telefone');

    telefoneInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');

        // Se não tem nada, não aplica máscara
        if (value.length === 0) {
            e.target.value = '';
            return;
        }

        if (value.length > 11) value = value.slice(0, 11);

        if (value.length > 10) {
            // Celular com 9 dígitos: (11) 91234-5678
            value = value.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 6) {
            // Fixo com 8 dígitos: (11) 1234-5678
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            // Só DDD + prefixo parcial: (11) 1234
            value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        } else {
            // Apenas DDD parcial
            value = value.replace(/^(\d{0,2})/, '($1');
        }

        e.target.value = value;
    });
});


