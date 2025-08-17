<?php

namespace App\Http\Controllers;

use App\Models\Device;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\Facades\DataTables;

class DeviceController extends Controller
{
    public function dash()
    {
        return view('dashboard.index');
    }

    public function index()
    {
        return view('sistema.dispositivo.index');
    }

    public function create()
    {
        Device::whereNull('status')->delete();

        $device = new Device();
        $device->session = Utils::createCode();
        $device->save();

        return view('sistema.dispositivo.create', compact('device'));
    }

    public function store(Request $request)
    {
        Device::whereNull('status')->delete();

        $request->validate([
            'nome' => 'required|string|max:255',
            'data_ultima_recarga' => 'required|date',
            'start_minutes' => 'required|integer|min:0',
            'start_seconds' => 'required|integer|min:0|max:59',
            'end_minutes' => 'required|integer|min:0',
            'end_seconds' => 'required|integer|min:0|max:59'
        ]);

        // Validação do intervalo de tempo
        $startTotal = ($request->start_minutes * 60) + $request->start_seconds;
        $endTotal = ($request->end_minutes * 60) + $request->end_seconds;

        if ($startTotal >= $endTotal) {
            return response()->json([
                'errors' => [
                    'time_interval' => ['O intervalo final deve ser maior que o inicial']
                ]
            ], 422);
        }

        $device = new Device();
        $device->session = Utils::createCode();
        $device->name = $request->nome;
        $device->start_minutes = $request->start_minutes;
        $device->start_seconds = $request->start_seconds;
        $device->end_minutes = $request->end_minutes;
        $device->end_seconds = $request->end_seconds;
        $device->data_ultima_recarga = $request->data_ultima_recarga;
        $device->save();

        $qrcode = $this->getQrCode($device->session);

        return response()->json([
            'session' => $device->session,
            'id' => $device->id,
            'qrcode' => $qrcode
        ]);
    }

    public function getDevices()
    {
        // Comentando temporariamente para debug - você pode descomentar depois
        // Device::deleteDevicesWithNullJid(); 
        
        $devices = Device::orderBy('id');
        return DataTables::of($devices)
            ->addColumn('actions', function($device) {
                return '
                    <div class="btn-group">
                        <a href="#" class="btn btn-sm btn-info edit" onclick="editDevice(' . $device->id . ')">
                            <i class="fas fa-edit"></i>
                        </a> 
                        <a href="#" class="btn btn-sm btn-danger delete" onclick="configModalDelete(' . $device->id . ')">
                            <i class="far fa-trash-alt"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $device = Device::find($request->id);

        $device->status = $request->status;
        $device->picture = $request->picture;
        $device->jid = $request->jid;
        $device->update();

        return response()->json(['status' => '1']);
    }

    public function updateName(Request $request)
    {
        $device = Device::find($request->id);
        $device->name = $request->name;
        $device->update();

        return response()->json(['status' => '1']);
    }

    public function getQrCode($session)
    {
        $url = env('APP_URL_ZAP') . '/instance/create';

        $data = [
            "instanceName" => $session,
            "qrcode"       => true,
            "integration"  => "WHATSAPP-BAILEYS"
        ];

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . env('TOKEN_EVOLUTION'),
                'Content-Type: application/json'
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $result = json_decode($response, true);

        return $result['qrcode']['base64'] ?? false;
    }

    public function gerarQr(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:devices,id',
            'nome' => 'required|string|max:255',
            'start_minutes' => 'required|integer|min:0',
            'start_seconds' => 'required|integer|min:0|max:59',
            'end_minutes' => 'required|integer|min:0',
            'end_seconds' => 'required|integer|min:0|max:59'
        ]);

        $device = Device::find($request->id);
        $device->name = $request->nome;
        $device->start_minutes = $request->start_minutes;
        $device->start_seconds = $request->start_seconds;
        $device->end_minutes = $request->end_minutes;
        $device->end_seconds = $request->end_seconds;
        $device->save();

        $qrCode = $this->getQrCode($device->session);

        if ($qrCode) {
            return response()->json(['qr_code' => $qrCode]);
        } else {
            return response()->json(['error' => 'Erro ao gerar QR Code.'], 500);
        }
    }

    public function getStatus(Request $request)
    {
        $client = new Client();

        try {
            $response = $client->request('GET', env('APP_URL_ZAP') . "/instance/connectionState/{$request->sessionId}", [
                'headers' => [
                    'apikey' => env('TOKEN_EVOLUTION')
                ]
            ]);

            $body = json_decode($response->getBody(), true);

            return response()->json([
                'status' => true,
                'data' => $body
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkEvolutionStatus(Request $request)
    {
        $request->validate([
            'session' => 'required|string'
        ]);

        $client = new Client();

        try {
            $response = $client->request('GET', env('APP_URL_ZAP') . "/instance/connectionState/{$request->session}", [
                'headers' => [
                    'apikey' => env('TOKEN_EVOLUTION')
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            // Verificar se está conectado (open)
            $isConnected = isset($body['instance']['state']) && $body['instance']['state'] === 'open';
            
            return response()->json([
                'success' => true,
                'connected' => $isConnected,
                'status' => $body['instance']['state'] ?? 'unknown',
                'data' => $body
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $device = Device::where('id', $request->id_device)->first();

        if ($device) {
            $device->delete();
            return back()->with('success', 'Deletado Com Sucesso.');
        }

        return back()->with('error', 'Dispositivo não encontrado.');
    }

    public function getDevice($id)
    {
        $device = Device::find($id);
        if (!$device) {
            return response()->json(['message' => 'Dispositivo não encontrado'], 404);
        }
        return response()->json($device);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:devices,id',
            'name' => 'required|string|max:255',
            'data_ultima_recarga' => 'required|date',
            'start_minutes' => 'required|integer|min:0',
            'start_seconds' => 'required|integer|min:0|max:59',
            'end_minutes' => 'required|integer|min:0',
            'end_seconds' => 'required|integer|min:0|max:59'
        ]);

        // Validação do intervalo de tempo
        $startTotal = ($request->start_minutes * 60) + $request->start_seconds;
        $endTotal = ($request->end_minutes * 60) + $request->end_seconds;

        if ($startTotal >= $endTotal) {
            return response()->json([
                'message' => 'O intervalo final deve ser maior que o inicial'
            ], 422);
        }

        $device = Device::find($request->id);
        $device->name = $request->name;
        $device->data_ultima_recarga = $request->data_ultima_recarga;
        $device->start_minutes = $request->start_minutes;
        $device->start_seconds = $request->start_seconds;
        $device->end_minutes = $request->end_minutes;
        $device->end_seconds = $request->end_seconds;
        $device->save();

        return response()->json(['message' => 'Dispositivo atualizado com sucesso']);
    }

    public function monitorStatus()
    {
        return view('sistema.dispositivo.monitor');
    }

    public function getStatusAll()
    {
        $devices = Device::whereNotNull('session')
            ->select('id', 'name', 'session', 'status', 'jid', 'updated_at', 'data_ultima_recarga')
            ->get()
            ->map(function ($device) {
                $statusColor = 'danger'; // default para offline
                if ($device->status === 'open') {
                    $statusColor = 'success';
                } elseif ($device->status === 'connecting') {
                    $statusColor = 'warning';
                } elseif ($device->status === 'DISCONNECTED' || $device->status === 'disconnected') {
                    $statusColor = 'danger';
                }
                
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'session' => $device->session,
                    'status' => $device->status,
                    'display_status' => $device->display_status,
                    'jid' => $device->jid,
                    'updated_at' => $device->updated_at?->format('d/m/Y H:i:s'),
                    'data_ultima_recarga' => $device->data_ultima_recarga_formatada,
                    'status_color' => $statusColor,
                    'last_check' => $device->updated_at?->diffForHumans()
                ];
            });

        return response()->json($devices);
    }

    public function forceStatusCheck()
    {
        try {
            Artisan::call('device:check-status', ['--force' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Verificação de status iniciada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar verificação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function atualizarUltimaRecarga(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:devices,id'
        ]);

        $device = Device::find($request->id);
        $device->data_ultima_recarga = now();
        $device->save();

        return response()->json([
            'message' => 'Data da última recarga atualizada com sucesso',
            'data_ultima_recarga' => $device->data_ultima_recarga_formatada
        ]);
    }

    public function reconectar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:devices,id'
        ]);

        $device = Device::find($request->id);
        
        try {
            // Primeiro, desconectar a sessão atual se existir
            $this->desconectarSessao($device->session);
            
            // Gerar nova sessão
            $device->session = Utils::createCode();
            $device->status = null;
            $device->jid = null;
            $device->picture = null;
            $device->data_ultima_recarga = now();
            $device->save();

            // Gerar novo QR code
            $qrcode = $this->getQrCode($device->session);

            if ($qrcode) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reconexão iniciada com sucesso',
                    'qrcode' => $qrcode,
                    'session' => $device->session
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao gerar QR Code para reconexão'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro durante reconexão: ' . $e->getMessage()
            ], 500);
        }
    }

    private function desconectarSessao($session)
    {
        try {
            $client = new Client();
            $response = $client->request('DELETE', env('APP_URL_ZAP') . "/instance/logout/{$session}", [
                'headers' => [
                    'apikey' => env('TOKEN_EVOLUTION')
                ]
            ]);
            return true;
        } catch (\Exception $e) {
            // Log do erro se necessário, mas não interrompe o processo
            return false;
        }
    }

    public function updateRecarga(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:devices,id'
        ]);

        try {
            $device = Device::find($request->id);
            $device->data_ultima_recarga = now();
            $device->save();

            return response()->json([
                'success' => true,
                'message' => 'Última recarga atualizada com sucesso',
                'data_ultima_recarga' => $device->data_ultima_recarga_formatada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar última recarga: ' . $e->getMessage()
            ], 500);
        }
    }
}
