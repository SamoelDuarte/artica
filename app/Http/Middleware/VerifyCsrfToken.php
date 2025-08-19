<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'git-webhook',
        '/git-webhook',
        'webhook',
        'api/*',  
        'cron/*',      // Ignorar CSRF para todas as rotas que começam com 'api/'
        'mobile/login-mobile',     // Se suas rotas mobile estão em /mobile/ também pode adicionar aqui
    ];
}
