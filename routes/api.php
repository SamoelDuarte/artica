<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileClienteController;
use App\Http\Controllers\Api\MobileMensagemController;
use App\Http\Controllers\Api\MobilePedidoController;
use App\Http\Controllers\Api\MobileProdutoController;
use App\Http\Controllers\Api\MobileUsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('mobile')->group(function () {

    // rota de login, que NÃO precisa do token fixo da app
    Route::post('/login-mobile', [MobileAuthController::class, 'login']);

    // rotas protegidas pelo token fixo da app
    Route::middleware(['check.api.token'])->group(function () {

        Route::prefix('pedidos')->controller(MobilePedidoController::class)->group(function () {
            Route::get('/{usuario_id}', 'listarPedidos');
            Route::put('/{id}/status', 'atualizarStatus');
            Route::post('/salva', 'store');
        });

        Route::prefix('produtos')->controller(MobileProdutoController::class)->group(function () {
            Route::get('/', 'listar');
        });

        Route::prefix('clientes')->controller(MobileClienteController::class)->group(function () {
            Route::post('/', 'getCliente'); // agora espera o telefone via POST
            Route::post('/salvar', 'store'); // agora espera o telefone via POST
        });

        Route::prefix('mensagens')->controller(MobileMensagemController::class)->group(function () {
        // Listar mensagens de um pedido
        Route::get('/{pedido_id}', 'listarMensagens');

        // Enviar nova mensagem (POST)
        Route::post('/enviar', 'enviarMensagem');
    });


        Route::get('/usuario/{usuario_id}', [MobileUsuarioController::class, 'verificaUsuario']);
    });
});
