<?php

namespace App\Http\Controllers;

use App\Helpers\Base62Helper;
use App\Http\Controllers\Utils;
use App\Models\Avaliacao;
use App\Models\Chat;
use App\Models\Colaborador;
use App\Models\Config;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Messagen;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrdersItens;
use App\Models\Route;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EventsController extends Controller
{



    public function index()
    {
        $reponseJson = file_get_contents('php://input');

        // file_put_contents(Utils::createCode()."-audio.txt",$reponseJson);
        $reponseArray = json_decode($reponseJson, true);
        $session = Device::where('session', $reponseArray['data']['sessionId'])->first();
        $config = Config::firstOrFail();
        if ($reponseArray['data']['event'] == "DISCONNECTED") {
            $session->status = "DISCONNECTED";
            $session->update();
            exit;
        }

        // Configurar o Carbon para usar o fuso horário de São Paulo
        $now = Carbon::now('America/Sao_Paulo');

        $daysOfWeek = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terça',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sábado',
        ];

        $dayOfWeek =  $daysOfWeek[$now->dayOfWeek];
        // Obter a hora e minutos atuais
        $currentTime = $now->format('H:i:s');

        // Verifique se existe um slot disponível com os parâmetros fornecidos
        $exists = DB::table('available_slots_config')
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->exists();



        $jid = $reponseArray['data']['message']['from'];
        // Remover o texto antes do '@'
        $numero_sem_arroba = substr($jid, 0, strpos($jid, '@'));
        // Extrair apenas os últimos 9 dígitos (número de celular)
        $jid = $numero_sem_arroba;

        // Se não houver slot disponível, enviar mensagem fora do horário
        if ($exists) {
            $this->verifyService($reponseArray, $session);
        } else {
            // Montar a lista de horários de funcionamento
            $operatingHours = [];
            foreach ($daysOfWeek as $index => $day) {
                $slots = DB::table('available_slots_config')
                    ->where('day_of_week', $day)
                    ->select('start_time', 'end_time')
                    ->get();

                if ($slots->isEmpty()) {
                    $operatingHours[$day] = 'Fechado';
                } else {
                    $hours = [];
                    foreach ($slots as $slot) {
                        $hours[] = $slot->start_time . ' às ' . $slot->end_time;
                    }
                    $operatingHours[$day] = implode(', ', $hours);
                }
            }

            // Construir a mensagem com os horários de funcionamento
            $message = 'Desculpe, estamos fora do horário de atendimento. Os nossos horários de funcionamento são:\n\n';
            foreach ($operatingHours as $day => $hours) {
                $message .= ucfirst($day) . ': ' . $hours . '\n';
            }

            $this->sendMessagem($session->session, $jid, $message);
            exit;
        }
    }

    public function verifyService($reponseArray, $session)
    {
        // Verifica se a mensagem é de mim (fromMe) para marcar como "eu_iniciei"
        $isFromMe = isset($reponseArray['data']['message']['fromMe']) && $reponseArray['data']['message']['fromMe'];
        
        if (!$isFromMe && !$reponseArray['data']['message']['fromGroup']) {
            $jid = $reponseArray['data']['message']['from'];

            // Remover o texto antes do '@'
            $numero_sem_arroba = substr($jid, 0, strpos($jid, '@'));
            $jid = $numero_sem_arroba;

            $service = Chat::where('session_id',  $session->id)
                ->where('jid', $jid)
                ->where('active', 1)
                ->first();

            if (!$service) {
                $service = new Chat();
                $service->jid = $jid;
                $service->session_id = $session->id;
                $service->service_id = Utils::createCode();
                $service->await_answer = "init_chat";
                $service->save();
            }

            // Verificar se é mensagem de áudio e direcionar para atendimento humano
            if ($reponseArray['data']['message']['type'] == "audio") {
                $service->await_answer = "await_human";
                $service->update();
                exit;
            }

            // Fluxo de atendimento
            if ($service->await_answer == "init_chat") {
                $text = 'Bom dia! 🌅 Para agilizar o atendimento, escolha uma das opções abaixo:';
                $options = [
                    "Geladeira",
                    "Microondas", 
                    "Freezer",
                    "Máquina de Lavar"
                ];
                $this->sendMessagewithOption($session->session, $jid, $text, $options);
                $service->await_answer = "menu_aparelhos";
                $service->update();
                exit;
            }

            if ($service->await_answer == "menu_aparelhos") {
                $response = $reponseArray['data']['message']['text'];
                
                switch ($response) {
                    case "1": // Geladeira
                        $service->flow_stage = "geladeira";
                        $service->await_answer = "menu2";
                        $service->update();
                        $text = "Você selecionou: Geladeira ❄️\n\nQual o problema com sua geladeira?";
                        $options = [
                            "Não está gelando",
                            "Fazendo muito barulho",
                            "Vazando água",
                            "Não liga"
                        ];
                        $this->sendMessagewithOption($session->session, $jid, $text, $options);
                        break;

                    case "2": // Microondas
                        $service->flow_stage = "microondas";
                        $service->await_answer = "menu2";
                        $service->update();
                        $text = "Você selecionou: Microondas 📡\n\nQual o problema com seu microondas?";
                        $options = [
                            "Não aquece",
                            "Prato não gira",
                            "Fazendo faísca",
                            "Não liga"
                        ];
                        $this->sendMessagewithOption($session->session, $jid, $text, $options);
                        break;

                    case "3": // Freezer
                        $service->flow_stage = "freezer";
                        $service->await_answer = "menu2";
                        $service->update();
                        $text = "Você selecionou: Freezer 🧊\n\nQual o problema com seu freezer?";
                        $options = [
                            "Não está congelando",
                            "Fazendo muito gelo",
                            "Fazendo barulho",
                            "Não liga"
                        ];
                        $this->sendMessagewithOption($session->session, $jid, $text, $options);
                        break;

                    case "4": // Máquina de Lavar
                        $service->flow_stage = "maquina_lavar";
                        $service->await_answer = "menu2";
                        $service->update();
                        $text = "Você selecionou: Máquina de Lavar 👕\n\nQual o problema com sua máquina?";
                        $options = [
                            "Não está lavando",
                            "Não centrifuga",
                            "Vazando água",
                            "Não liga"
                        ];
                        $this->sendMessagewithOption($session->session, $jid, $text, $options);
                        break;

                    default:
                        $service->erro = $service->erro + 1;
                        $service->update();
                        $text = "Opção inválida! Por favor, escolha uma das opções disponíveis.";
                        $this->sendMessagem($session->session, $jid, $text);
                        
                        if ($service->erro > 2) {
                            $text = "Por favor aguarde, em instantes você será atendido(a) por um técnico.";
                            $this->sendMessagem($session->session, $jid, $text);
                            $service->await_answer = "await_human";
                            $service->update();
                        }
                        break;
                }
                exit;
            }

            if ($service->await_answer == "menu2") {
                $response = $reponseArray['data']['message']['text'];
                $aparelho = $service->flow_stage;
                
                // Aqui você pode continuar o fluxo específico para cada problema
                $text = "Entendi o problema! 🔧\n\nPara continuar com o atendimento, preciso de algumas informações:\n\n";
                $text .= "1️⃣ Qual a marca do aparelho?\n";
                $text .= "2️⃣ Há quanto tempo apresenta esse problema?\n";
                $text .= "3️⃣ Qual seu endereço para visita técnica?\n\n";
                $text .= "Por favor, aguarde que em breve um técnico especializado entrará em contato! 👨‍🔧";
                
                $this->sendMessagem($session->session, $jid, $text);
                
                $service->await_answer = "await_human";
                $service->update();
                exit;
            }
            
            // Para outras situações, direcionar para atendimento humano
            if ($service->await_answer == "await_human" || $service->await_answer == "in_service") {
                // Não fazer nada, aguardar atendimento humano
                exit;
            }

        } elseif ($isFromMe) {
            // Se a mensagem foi enviada por mim, marcar como "eu_iniciei"
            $jid = $reponseArray['data']['message']['to'] ?? $reponseArray['data']['message']['from'];
            $numero_sem_arroba = substr($jid, 0, strpos($jid, '@'));
            $jid = $numero_sem_arroba;

            $service = Chat::where('session_id',  $session->id)
                ->where('jid', $jid)
                ->where('active', 1)
                ->first();

            if (!$service) {
                $service = new Chat();
                $service->jid = $jid;
                $service->session_id = $session->id;
                $service->service_id = Utils::createCode();
                $service->await_answer = "eu_iniciei";
                $service->flow_stage = "eu_iniciei";
                $service->save();
            }
            return; // Não processar mais se foi enviado por mim
        }
    }

    public function cronEventHandler()
    {
        // Pegar dados da Evolution
        $reponseJson = file_get_contents('php://input');
        
        // LOG SIMPLES - Salvar tudo que vem da Evolution
        $logFile = storage_path('logs/evolution_recebido.log');
        $timestamp = date('Y-m-d H:i:s');
        $logContent = "[$timestamp] DADOS RECEBIDOS DA EVOLUTION:\n";
        $logContent .= $reponseJson . "\n";
        $logContent .= "==========================================\n\n";
        
        file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
        
        if (empty($reponseJson)) {
            return response()->json(['status' => 'No data received']);
        }
        
        $evolutionData = json_decode($reponseJson, true);
        
        // A Evolution envia uma estrutura diferente, vamos adaptar
        if (!isset($evolutionData['instance'])) {
            return response()->json(['status' => 'Invalid data format - no instance']);
        }
        
        // Buscar sessão pelo instanceId
        $session = Device::where('session', $evolutionData['instance'])->first();
        
        if (!$session) {
            // Log para debug
            file_put_contents($logFile, "[$timestamp] SESSÃO NÃO ENCONTRADA: {$evolutionData['instance']}\n", FILE_APPEND | LOCK_EX);
            return response()->json(['status' => 'Session not found', 'instance' => $evolutionData['instance']]);
        }
        
        // Adaptar os dados para o formato esperado pelo verifyService
        $reponseArray = [
            'data' => [
                'sessionId' => $evolutionData['instance'],
                'event' => $evolutionData['event'] ?? 'MESSAGE',
                'message' => [
                    'from' => $evolutionData['data']['key']['remoteJid'] ?? null,
                    'fromMe' => $evolutionData['data']['key']['fromMe'] ?? false,
                    'fromGroup' => false, // Assumindo que não é grupo por enquanto
                    'type' => $evolutionData['data']['messageType'] ?? 'text',
                    'text' => $evolutionData['data']['message']['conversation'] ?? '',
                    'to' => $evolutionData['sender'] ?? null
                ]
            ]
        ];
        
        // Log da conversão
        file_put_contents($logFile, "[$timestamp] DADOS CONVERTIDOS:\n" . json_encode($reponseArray, JSON_PRETTY_PRINT) . "\n==========================================\n\n", FILE_APPEND | LOCK_EX);
        
        // Processar o evento com os dados adaptados
        $this->verifyService($reponseArray, $session);
        
        return response()->json(['status' => 'Event processed successfully']);
    }

    public function teste()
    {
        $texto = file_get_contents('php://input');
        $reponseJson = file_get_contents('teste.txt');

        $reponseArray = json_decode($reponseJson, true);
        $session = Device::where('session', $reponseArray['data']['sessionId'])->first();

        //  dd($reponseArray['data']['sessionId']);


        // verifica se o serviço está em andamento
        $this->verifyService($reponseArray, $session);
    }
    public function mensagemEmMassa()
    {
        $devices = Device::get(); // IDs dos dispositivos
        // Configurar o Carbon para usar o fuso horário de São Paulo
        $now = Carbon::now('America/Sao_Paulo');


        $daysOfWeek = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terça',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sábado',
        ];

        $dayOfWeek =  $daysOfWeek[$now->dayOfWeek];
        // Obter a hora e minutos atuais
        $currentTime = $now->format('H:i:s');

        // Verifique se existe um slot disponível com os parâmetros fornecidos
        $exists = DB::table('available_slots')
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->exists();

        // Use dd() para depuração
        if (!$exists) {
            print_r('Fora de Data de Agendamento' . $currentTime);
            exit;
        }

        foreach ($devices as $device) {
            $mensagen = Messagen::where('device_id', null)->whereNot('number', "")->where('number', 'like', '55119%')->limit(1)->get();
            // Obtém o número de mensagens enviadas nas últimas horas
            $messageCount = $device->message_count_last_hour;


            // Verifica se o número de mensagens enviadas nas últimas horas é menor ou igual a 39
            if ($messageCount <= 39 && isset($mensagen)) {

                foreach ($mensagen as $mensage) {

                    $imagen = asset($mensage->imagem->caminho);
                    $mensage->device_id = $device->id;
                    $mensage->update();

                    $this->sendImage($device->session, $mensage->number, $imagen, $mensage->messagem);

                    echo 'enviado : ' . $mensage->number . ' <br>';
                }
            }
        }
    }
    public function storeAvaliacao(Request $request)
    {
        //    dd($request->all());


        // Crie uma nova instância de Avaliacao
        $avaliacao = new Avaliacao();

        // Preencha os campos com os dados do formulário
        $avaliacao->nota = $request->input('rate');
        $avaliacao->comentario = $request->input('comentario');
        $avaliacao->telefone = $request->input('telefone');
        $avaliacao->ip_device = $request->input('ip_device');
        $avaliacao->colaborador_id = $request->input('colaborador_id');
        $avaliacao->nota = $request->input('nota');


        // Salve a avaliação no banco de dados
        $avaliacao->save();

        // Você pode retornar uma resposta ou redirecionar o usuário após salvar a avaliação
        return view("front.avaliacao.obrigado");
    }
    public function sendImage($session, $phone, $nomeImagen, $detalhes)
    {
        $curl = curl_init();

        $send = array(
            "number" => $phone,
            "message" => array(
                "image" => array(
                    "url" => $nomeImagen // public_path('uploads/' . $nomeImagen)
                ),
                "caption" => $detalhes
            ),
            "delay" => 3
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_URL_ZAP') . '/' . $session . '/messages/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($send),
            CURLOPT_HTTPHEADER => array(
                'secret: $2a$12$VruN7Mf0FsXW2mR8WV0gTO134CQ54AmeCR.ml3wgc9guPSyKtHMgC',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        //  file_put_contents(Utils::createCode() . ".txt", $response);

        curl_close($curl);
    }
    public function avaliacao(Request $request)
    {


        if ($request->name_rota) {

            // Buscar colaborador com base no colaborador_od associado à rota
            $rota = Route::where("name",  urldecode($request->name_rota))->first();




            if (!isset($rota->colaborador_id)) {
                echo json_encode(array("Mensagem" => "Sem Colaborador Vinculado"));
                exit;
            } else {
                $colaborador = Colaborador::find($rota->colaborador_id);
                return view("front.avaliacao.index", compact('colaborador'));
            }
        }

        $colaborador = Colaborador::find($request->colaborador);

        if (!$colaborador) {
            echo json_encode(array("Mensagem" => "Sem Colaborador Vinculado"));
            exit;
        } else {
            return view("front.avaliacao.index", compact('colaborador'));
        }
    }
    public function sendMessagem($session, $phone, $texto)
    {
        // Em ambiente de teste, apenas logar
        if (app()->environment(['testing']) || $session === 'BOT_TESTE') {
            error_log("TESTE - Enviando mensagem para {$phone}: {$texto}");
            echo "MENSAGEM: {$texto}\n";
            return json_encode(['status' => 'test_mode', 'message' => $texto]);
        }

        // Limpar número
        $numero = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($numero, '55')) {
            $numero = substr($numero, 2);
        }

        $client = new \GuzzleHttp\Client();
        $url = "http://147.79.111.119:8080/message/sendText/{$session}";

        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => env('TOKEN_EVOLUTION'),
        ];

        $body = json_encode([
            'number' => '55' . $numero,
            'text' => $texto,
        ]);

        try {
            $request = new \GuzzleHttp\Psr7\Request('POST', $url, $headers, $body);
            $response = $client->sendAsync($request)->wait();
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            error_log("Erro ao enviar mensagem: " . $e->getMessage());
            return false;
        }
    }
    public function sendMessagewithOption($session, $phone, $text, $options)
    {
        // Em ambiente de teste, apenas logar
        if (app()->environment(['testing']) || $session === 'BOT_TESTE') {
            error_log("TESTE - Enviando opções para {$phone}: {$text}");
            echo "MENSAGEM COM OPÇÕES: {$text}\n";
            foreach ($options as $index => $option) {
                echo ($index + 1) . ". {$option}\n";
            }
            return json_encode(['status' => 'test_mode', 'message' => $text, 'options' => $options]);
        }

        // Limpar número
        $numero = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($numero, '55')) {
            $numero = substr($numero, 2);
        }

        // Montar mensagem com opções numeradas
        $mensagemCompleta = $text . "\n\n";
        foreach ($options as $index => $option) {
            $mensagemCompleta .= ($index + 1) . ". " . $option . "\n";
        }

        $client = new \GuzzleHttp\Client();
        $url = "http://147.79.111.119:8080/message/sendText/{$session}";

        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => env('TOKEN_EVOLUTION'),
        ];

        $body = json_encode([
            'number' => '55' . $numero,
            'text' => $mensagemCompleta,
        ]);

        try {
            $request = new \GuzzleHttp\Psr7\Request('POST', $url, $headers, $body);
            $response = $client->sendAsync($request)->wait();
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            error_log("Erro ao enviar mensagem com opções: " . $e->getMessage());
            return false;
        }
    }
    public function sendAudio($session, $phone)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('APP_URL_ZAP') . '/' . $session . '/messages/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "number": "' . $phone . '",
            "message": {
                "audio": {
                    "url" : "http://localhost:3333/static/audio/2F49EE65082AB66116EBFC03DC26C44D.ogg?sessionId=JOSE_1&messageId=2F49EE65082AB66116EBFC03DC26C44D"
                }
            },
            "delay": 0
        }',
            CURLOPT_HTTPHEADER => array(
                'secret: $2a$12$VruN7Mf0FsXW2mR8WV0gTO134CQ54AmeCR.ml3wgc9guPSyKtHMgC',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }
}
