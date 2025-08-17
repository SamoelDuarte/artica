<?php

return [
    'required' => 'O :attribute é obrigatório.',
    'string' => 'O :attribute deve ser um texto.',
    'max' => [
        'string' => 'O :attribute não pode ter mais que :max caracteres.',
    ],
    'email' => 'O :attribute deve ser um endereço de e-mail válido.',
    'unique' => 'Este :attribute já está em uso.',
    'min' => [
        'string' => 'O :attribute deve ter pelo menos :min caracteres.',
    ],
    'image' => 'O :attribute deve ser uma imagem.',
    'mimes' => 'O :attribute deve ser um arquivo do tipo: :values.',
    'date' => 'O :attribute deve ser uma data válida.',
    'boolean' => 'O :attribute deve ser verdadeiro ou falso.',
    'nullable' => 'O :attribute pode ser nulo.',
    'confirmed' => 'A confirmação de :attribute não coincide.',
    'custom' => [
        'name' => [
            'required' => 'O nome é obrigatório.',
            'string' => 'O nome deve ser um texto.',
            'max' => 'O nome não pode ter mais que 255 caracteres.',
        ],
        'email' => [
            'required' => 'O e-mail é obrigatório.',
            'email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'unique' => 'Este e-mail já está em uso.',
        ],
        'password' => [
            'required' => 'A senha é obrigatória.',
            'string' => 'A senha deve ser um texto.',
            'min' => 'A senha deve ter pelo menos 6 caracteres.',
        ],
    ],
    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'telefone' => 'telefone',
        'foto' => 'foto',
        'data_admissao' => 'data de admissão',
        'data_demissao' => 'data de demissão',
    ],
];
