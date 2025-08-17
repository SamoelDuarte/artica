<?php

namespace App\Console\Commands;

use App\Models\Device;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDeviceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:check-status {--force : Força a verificação mesmo se não houver devices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica o status de todos os dispositivos na Evolution API e atualiza no banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando verificação de status dos dispositivos via Evolution API...');
        
        try {
            // Buscar todas as instâncias da Evolution API
            $evolutionInstances = $this->fetchAllInstancesFromEvolution();
            
            if (empty($evolutionInstances)) {
                $this->warn('⚠️ Nenhuma instância encontrada na Evolution API.');
                return Command::SUCCESS;
            }
            
            $this->info("📡 Encontradas " . count($evolutionInstances) . " instâncias na Evolution API.");
            
            // Buscar todos os devices do banco que possuem session
            $devices = Device::whereNotNull('session')->get();
            $this->info("📱 Encontrados {$devices->count()} dispositivos no banco de dados.");
            
            $updated = 0;
            $notFoundInEvolution = 0;
            $errors = 0;
            
            // Criar um mapa das instâncias da Evolution por nome (session)
            $evolutionMap = [];
            foreach ($evolutionInstances as $instance) {
                $evolutionMap[$instance['name']] = $instance;
            }
            
            foreach ($devices as $device) {
                try {
                    $this->line("🔍 Verificando dispositivo: {$device->name} (Session: {$device->session})");
                    
                    if (isset($evolutionMap[$device->session])) {
                        $evolutionInstance = $evolutionMap[$device->session];
                        
                        $oldStatus = $device->status;
                        $newStatus = $this->mapEvolutionStatus($evolutionInstance['connectionStatus']);
                        
                        // Atualizar dados no banco
                        $device->status = $newStatus;
                        
                        // Atualizar picture se disponível
                        if (!empty($evolutionInstance['profilePicUrl'])) {
                            $device->picture = $evolutionInstance['profilePicUrl'];
                        }
                        
                        // Atualizar JID se disponível
                        if (!empty($evolutionInstance['ownerJid'])) {
                            $device->jid = $evolutionInstance['ownerJid'];
                        }
                        
                        $device->save();
                        
                        if ($oldStatus !== $newStatus) {
                            $this->info("✅ Status atualizado: {$device->name} -> {$newStatus} (Evolution: {$evolutionInstance['connectionStatus']})");
                            $updated++;
                        } else {
                            $this->line("▶️ Status inalterado: {$device->name} -> {$newStatus}");
                        }
                        
                        // Log da verificação
                        Log::info('Device status updated from Evolution API', [
                            'device_id' => $device->id,
                            'device_name' => $device->name,
                            'session' => $device->session,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'evolution_status' => $evolutionInstance['connectionStatus'],
                            'owner_jid' => $evolutionInstance['ownerJid'] ?? null,
                            'profile_name' => $evolutionInstance['profileName'] ?? null
                        ]);
                        
                    } else {
                        // Device não encontrado na Evolution - marcar como desconectado
                        $oldStatus = $device->status;
                        if ($oldStatus !== 'DISCONNECTED') {
                            $device->status = 'DISCONNECTED';
                            $device->save();
                            $this->warn("⚠️ Dispositivo não encontrado na Evolution: {$device->name} -> DESCONECTADO");
                            $updated++;
                            
                            Log::warning('Device not found in Evolution API', [
                                'device_id' => $device->id,
                                'device_name' => $device->name,
                                'session' => $device->session,
                                'old_status' => $oldStatus
                            ]);
                        } else {
                            $this->line("▶️ Dispositivo já estava desconectado: {$device->name}");
                        }
                        
                        $notFoundInEvolution++;
                    }
                    
                } catch (\Exception $e) {
                    $this->error("💥 Erro no dispositivo {$device->name}: {$e->getMessage()}");
                    $errors++;
                    
                    Log::error('Device status check failed', [
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'session' => $device->session,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $this->newLine();
            $this->info("🎯 Verificação concluída!");
            $this->info("📊 Estatísticas:");
            $this->info("   • Dispositivos no banco: {$devices->count()}");
            $this->info("   • Instâncias na Evolution: " . count($evolutionInstances));
            $this->info("   • Status atualizados: {$updated}");
            $this->info("   • Não encontrados na Evolution: {$notFoundInEvolution}");
            $this->info("   • Erros encontrados: {$errors}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("💥 Erro geral na verificação: {$e->getMessage()}");
            Log::error('General device check error', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    /**
     * Busca todas as instâncias da Evolution API
     */
    private function fetchAllInstancesFromEvolution(): array
    {
        try {
            $client = new Client();
            
            $this->line("🌐 Consultando Evolution API: " . env('APP_URL_ZAP') . "/instance/fetchInstances");
            
            $response = $client->request('GET', env('APP_URL_ZAP') . "/instance/fetchInstances", [
                'headers' => [
                    'apikey' => env('TOKEN_EVOLUTION')
                ],
                'timeout' => 30 // Timeout maior para buscar todas as instâncias
            ]);

            $instances = json_decode($response->getBody(), true);
            
            if (!is_array($instances)) {
                throw new \Exception('Resposta inválida da Evolution API');
            }
            
            $this->line("✅ Sucesso na consulta da Evolution API");
            
            return $instances;
            
        } catch (\Exception $e) {
            $this->error("❌ Erro ao consultar Evolution API: {$e->getMessage()}");
            Log::error("Evolution API fetchInstances error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mapeia o status da Evolution para o status do sistema
     */
    private function mapEvolutionStatus(string $evolutionStatus): string
    {
        switch ($evolutionStatus) {
            case 'open':
                return 'open';
            case 'connecting':
                return 'connecting';  
            case 'close':
            case 'closed':
                return 'disconnected';
            default:
                return 'disconnected';
        }
    }
}
