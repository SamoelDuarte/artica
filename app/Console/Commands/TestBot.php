<?php

namespace App\Console\Commands;

use App\Http\Controllers\EventsController;
use App\Models\Device;
use Illuminate\Console\Command;

class TestBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:test {phone} {message} {session?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o bot de assistência técnica simulando uma mensagem';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message');
        $sessionName = $this->argument('session') ?? 'teste';

        // Busca ou cria um dispositivo de teste
        $session = Device::where('session', $sessionName)->first();
        
        if (!$session) {
            $session = new Device();
            $session->session = $sessionName;
            $session->name = 'Bot Teste';
            $session->status = 'open';
            $session->save();
            $this->info("Dispositivo de teste criado com session: {$sessionName}");
        }

        // Simula o array de resposta da Evolution API
        $responseArray = [
            'data' => [
                'sessionId' => $sessionName,
                'event' => 'MESSAGE_CREATED',
                'message' => [
                    'from' => $phone . '@s.whatsapp.net',
                    'to' => 'bot@s.whatsapp.net',
                    'fromMe' => false,
                    'fromGroup' => false,
                    'text' => $message,
                    'type' => 'text'
                ]
            ]
        ];

        $this->info("Simulando mensagem do {$phone}: '{$message}'");
        
        $eventsController = new EventsController();
        $eventsController->verifyService($responseArray, $session);
        
        $this->info("Teste do bot executado com sucesso!");
    }
}
