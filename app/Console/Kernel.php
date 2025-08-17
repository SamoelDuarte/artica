<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Verificar status dos dispositivos a cada 5 minutos
        $schedule->command('device:check-status')
            ->everyFiveMinutes()
            ->withoutOverlapping() // Evita execuções simultâneas
            ->runInBackground(); // Executa em background
            
        // Alternativa: verificar a cada minuto (mais frequente)
        // $schedule->command('device:check-status')->everyMinute()->withoutOverlapping();
        
        // Alternativa: verificar a cada hora (menos frequente)
        // $schedule->command('device:check-status')->hourly()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
