<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EntregadorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\SorteioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GitWebhookController;
use App\Http\Controllers\MenssageController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});






Route::post('/git-webhook', [GitWebhookController::class, 'handle']);
Route::get('/whoami', function () {
    return response()->json(['user' => exec('whoami')]);
});

Route::post('/webhook', [WebhookController::class, 'evento'])->withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class,
    \App\Http\Middleware\Authenticate::class,
]);

// Webhook específico para o bot de assistência técnica
Route::post('/webhook/bot-assistencia', [WebhookController::class, 'botAssistencia'])->withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class,
    \App\Http\Middleware\Authenticate::class,
]);

// Rota adicional para eventos diretos do bot
Route::post('/events/bot', [\App\Http\Controllers\EventsController::class, 'index'])->withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class,
    \App\Http\Middleware\Authenticate::class,
]);


Route::prefix('/cron')->controller(CronController::class)->group(function () {
    Route::get('/enviarMensagem', 'enviarPendentes');
    Route::get('/mensagemEmMas', 'mensagemEmMassa');
});

// Rota para cron job do bot de assistência técnica
Route::prefix('/cron/bot')->controller(\App\Http\Controllers\EventsController::class)->group(function () {
    Route::post('/eventos', 'cronEventHandler');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Página de listagem de usuários
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuario.index');
    Route::get('/usuarios/create', [UsuarioController::class, 'create'])->name('usuario.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuario.store');
    Route::get('/usuarios/{user}/edit', [UsuarioController::class, 'edit'])->name('usuario.edit');
    Route::put('/usuarios/{user}', [UsuarioController::class, 'update'])->name('usuario.update');
    Route::delete('/usuarios/{user}', [UsuarioController::class, 'destroy'])->name('usuario.destroy');

    Route::prefix('/clientes')->controller(ClienteController::class)->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('cliente.index');
        Route::get('/novo', [ClienteController::class, 'create'])->name('cliente.create');
        Route::post('/store', [ClienteController::class, 'store'])->name('cliente.store');
        Route::get('/edita/{cliente}', [ClienteController::class, 'edit'])->name('cliente.edit');
        Route::delete('/delete/{cliente}', [ClienteController::class, 'destroy'])->name('cliente.destroy');
        Route::put('/atualiza/{cliente}', [ClienteController::class, 'update'])->name('cliente.update');
        Route::get('/buscar-por-telefone', 'buscarPorTelefone')->name('cliente.buscar.telefone');
    });

    Route::prefix('/dispositivo')->controller(DeviceController::class)->group(function () {
        Route::post('/criar', 'store');
        Route::post('/gerarQr', 'gerarQr')->name('dispositivo.gerarQr');
        Route::get('/', 'index')->name('dispositivo.index');
        Route::get('/novo', 'create')->name('dispositivo.create');
        Route::get('/monitor', 'monitorStatus')->name('dispositivo.monitor');
        Route::post('/delete', 'delete')->name('dispositivo.delete');
        Route::get('/getDevices', 'getDevices');
        Route::get('/getStatusAll', 'getStatusAll');
        Route::post('/force-status-check', 'forceStatusCheck');
        Route::post('/updateStatus', 'updateStatus');
        Route::post('/updateName', 'updateName');
        Route::get('/getStatus', 'getStatus');
        Route::post('/check-evolution-status', 'checkEvolutionStatus')->name('dispositivo.checkEvolutionStatus');
        Route::get('/{id}/get', 'getDevice');
        Route::post('/update', 'update');
        Route::post('/atualizar-ultima-recarga', 'atualizarUltimaRecarga')->name('dispositivo.atualizarUltimaRecarga');
        Route::post('/update-recarga', 'updateRecarga')->name('dispositivo.updateRecarga');
        Route::post('/reconectar', 'reconectar')->name('dispositivo.reconectar');
    });

    Route::prefix('/mensagem')->controller(MenssageController::class)->group(function () {
        Route::get('/', 'create')->name('message.create');
        Route::get('/agendamentos', 'indexAgendamentos')->name('message.agendamento');
        Route::get('/getAgendamentos', 'getAgendamentos')->name('message.getAgendamento');
        Route::post('/upload', 'upload')->name('upload.imagem');
        Route::post('/countContact', 'countContact');
        Route::get('/novo', 'index')->name('message.index');;
        Route::get('/getMessage', 'getMessage');
        Route::post('/bulk', 'bulkMessage')->name('message.bulk');
    });

     Route::prefix('/agenda')->controller(ScheduleController::class)->group(function () {
            Route::get('/', 'index')->name('schedule.index');
            Route::post('/atualiza', 'update')->name('schedule.update');
        });




    // Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
    Route::get('/', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

});






Route::middleware(['cors'])->post('/obterparcelamento', [CronController::class, 'obterParcelamento']);


require __DIR__ . '/auth.php';
