<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViewDeviceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:logs {--lines=50 : Número de linhas para exibir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Visualiza os logs de verificação de status dos dispositivos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = (int) $this->option('lines');
        
        if (!File::exists($logFile)) {
            $this->error('❌ Arquivo de log não encontrado.');
            return Command::FAILURE;
        }
        
        $this->info("📋 Últimas {$lines} linhas dos logs de dispositivos:");
        $this->newLine();
        
        // Ler as últimas linhas do arquivo de log
        $command = "tail -n {$lines} \"{$logFile}\" | grep -i \"device\"";
        
        // No Windows, usar PowerShell
        if (PHP_OS_FAMILY === 'Windows') {
            $command = "powershell -Command \"Get-Content '{$logFile}' | Select-Object -Last {$lines} | Where-Object {\$_ -match 'device'}\"";
        }
        
        $output = shell_exec($command);
        
        if ($output) {
            $this->line($output);
        } else {
            $this->warn('⚠️ Nenhum log relacionado a dispositivos encontrado nas últimas ' . $lines . ' linhas.');
        }
        
        return Command::SUCCESS;
    }
}
