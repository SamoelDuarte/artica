<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GitWebhookController extends Controller
{
   public function handle(Request $request)
   {
       $output = [];
       $returnVar = null;
       $projectPath = base_path();

       // Comando para resetar e limpar mudanças locais antes do pull
       $cmd = "cd {$projectPath} && whoami && /usr/bin/git reset --hard && /usr/bin/git clean -fd && /usr/bin/git pull origin main 2>&1";

       exec($cmd, $output, $returnVar);

       return response()->json([
           'executed_as' => exec('whoami'),
           'executed_command' => $cmd,
           'output' => $output,
           'return_var' => $returnVar
       ]);
   }
}
