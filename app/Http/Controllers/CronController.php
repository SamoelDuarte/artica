<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\Messagen;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CronController extends Controller
{
    public function enviarPendentes()
    {
        $mensagens = Messagen::where('enviado', false)
            ->where('direcao', 'enviado')
            ->whereHas('pedido', function ($query) {
                $query->where('status_pedido_id', 8);
            })
            ->with(['pedido.cliente', 'device', 'entregador'])
            ->limit(20)
            ->get();


        foreach ($mensagens as $mensagem) {
            // Validação mínima
            if (
                !$mensagem->pedido ||
                !$mensagem->pedido->cliente ||
                !$mensagem->device ||
                !$mensagem->entregador
            ) {
                Log::warning("Mensagem {$mensagem->id} ignorada por falta de dados relacionados.");
                continue;
            }

            $enviado = $this->enviarMensagem($mensagem);

            if ($enviado) {
                $mensagem->enviado = true;
                $mensagem->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function enviarMensagem(Messagen $mensagem)
    {
        $client = new Client();

        $numero = $mensagem->pedido->cliente->telefone;
        $nomeEntregador = $mensagem->entregador->nome;
        $textoOriginal = $mensagem->messagem;

        // Monta a mensagem formatada com quebras de linha
        $mensagemFormatada = "Mensagem Entregador ({$nomeEntregador})\n{$textoOriginal}";

        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => env('TOKEN_EVOLUTION'),
        ];

        $body = json_encode([
            'number' => '55' . $numero,
            'text' => $mensagemFormatada,
        ]);

        $url = "http://147.79.111.119:8080/message/sendText/{$mensagem->device->session}";

        $request = new Request('POST', $url, $headers, $body);

        try {
            $response = $client->sendAsync($request)->wait();
            $statusCode = $response->getStatusCode();
            $bodyResponse = $response->getBody()->getContents();

            Log::info("Mensagem ID {$mensagem->id} enviada com status {$statusCode}: {$bodyResponse}");

            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao enviar mensagem ID {$mensagem->id}: " . $e->getMessage());
            return false;
        }
    }

    public function mensagemEmMassa()
    {
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
        $dayOfWeek = $daysOfWeek[$now->dayOfWeek];
        $currentTime = $now->format('H:i:s');

        // Verifica horário disponível
        $exists = DB::table('available_slots')
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->exists();

        if (!$exists) {
            echo 'Fora de Data de Agendamento: ' . $currentTime;
            return;
        }

        // Pega campanhas ativas com contatos não enviados
        $campaigns = Campaign::where('status', 'play')
            ->with([
                'contactList' => function ($query) {
                    $query->wherePivot('send', false)->limit(1);
                },
                'devices' => function ($query) {
                    $query->where('status', 'open');
                }
            ])
            ->get();

        foreach ($campaigns as $campaign) {
            // Pega todos os contatos não enviados desta campanha
            $contactList = $campaign->contactList()
                ->wherePivot('send', false)
                ->orderBy('contact_list.id', 'asc')
                ->get();

            if ($contactList->isEmpty()) {
                continue;
            }

            // Filtra apenas contatos com telefone
            $contactList = $contactList->filter(function($contact) {
                return !empty($contact->phone);
            });

            if ($contactList->isEmpty()) {
                continue;
            }

            // Pega todos os devices disponíveis da campanha que estão no horário
            $availableDevices = [];

            if ($campaign->devices->count() > 0) {
                foreach ($campaign->devices as $device) {
                    if ($device->status !== 'open') {
                        continue;
                    }

                    if ($device->message_count_last_hour > 39) {
                        echo "Device {$device->id} atingiu o limite de mensagens por hora.<br>";
                        continue;
                    }

                    // Converte os intervalos para segundos
                    $startInterval = ($device->start_minutes * 60) + $device->start_seconds;
                    $endInterval = ($device->end_minutes * 60) + $device->end_seconds;

                    // Gera um intervalo aleatório entre start e end
                    $randomInterval = rand($startInterval, $endInterval);
                    
                    // Verifica quanto tempo passou desde o último envio
                    $lastUpdate = Carbon::parse($device->updated_at);
                    $now = Carbon::now();
                    $diffInSeconds = $now->diffInSeconds($lastUpdate);

                    // Se já passou o tempo do intervalo aleatório, adiciona ao array
                    if ($diffInSeconds >= $randomInterval) {
                        $availableDevices[] = $device;
                        echo "Device {$device->id} disponível para envio (última atualização: {$diffInSeconds}s, intervalo sorteado: {$randomInterval}s entre {$startInterval}s e {$endInterval}s).<br>";
                    } else {
                        echo "Device {$device->id} ainda não atingiu o intervalo mínimo (última atualização: {$diffInSeconds}s, precisa esperar: {$randomInterval}s, entre {$startInterval}s e {$endInterval}s).<br>";
                    }
                }
            }

            // Se não encontrou nenhum device disponível, pula para próxima campanha
            if (empty($availableDevices)) {
                echo "Nenhum device disponível dentro do intervalo de tempo para a campanha {$campaign->id}.<br>";
                continue;
            }

            // Para cada device disponível, pega um contato diferente e envia
            foreach ($availableDevices as $index => $device) {
                // Verifica se ainda tem contatos disponíveis
                if ($index >= $contactList->count()) {
                    break; // Sai do loop se não houver mais contatos
                }

                // Pega o próximo contato da lista
                $contact = $contactList[$index];

                // Preparar os dados para envio
                $imagem = asset($campaign->imagem->caminho);
                $texto = $campaign->texto ?? '';

                $this->sendImage($device->session, $contact->phone, $imagem, $texto);

                // Atualiza o updated_at do device
                $device->touch();

                // Marca este contato específico como enviado
                $contact->pivot->send = true;
                $contact->pivot->save();

                echo "Enviado para {$contact->phone} via device {$device->id} <br>";
            }

            // Já processou este contato, vai para a próxima campanha
            continue;
        }

        echo "Nenhum contato para enviar agora.<br>";
    }

    public function sendImage($session, $phone, $urlImagem, $descricao = '')
    {
        $numero = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($numero, '55')) {
            $numero = substr($numero, 2);
        }

        $client = new \GuzzleHttp\Client();
        $url = "http://147.79.111.119:8080/message/sendMedia/{$session}";

        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => env('TOKEN_EVOLUTION'),
        ];

        $body = json_encode([
            'number' => '55' . $numero,
            'mediatype' => 'image',
            'mimetype' => 'image/png',
            'caption' => $descricao,
            'media' => $urlImagem,
            // 'media' => 'https://th.bing.com/th/id/R.106357017f8bd35d565974dde8072dbb?rik=IjfuQUTQ8pkXFg&pid=ImgRaw&r=0',
            'fileName' => 'imagem.png',
        ]);

        try {
            $request = new \GuzzleHttp\Psr7\Request('POST', $url, $headers, $body);
            $response = $client->sendAsync($request)->wait();

            Log::info("Imagem enviada para 55{$numero}");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar imagem: " . $e->getMessage());
            return false;
        }
    }
}
