<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Criar permissões
        $permissions = [
            'ver clientes',
            'criar clientes',
            'editar clientes',
            'excluir clientes',
            'gerenciar clientes',
            'listar clientes',
            'ver usuários',
            'criar usuários',
            'editar usuários',
            'excluir usuários',
            'gerenciar usuários',
            'listar usuários',
            'gerenciar dispositivos',
            'gerenciar mensagens',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Criar funções e atribuir permissões
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $atendenteRole = Role::firstOrCreate(['name' => 'atendente']);

        // Admin tem todas as permissões
        $adminRole->syncPermissions($permissions);

        // Atendente tem apenas algumas permissões
        $atendenteRole->syncPermissions(['ver clientes', 'criar clientes']);

        // Atribuir função Admin ao usuário padrão
        $admin = User::where('email', 'artica@artica.com')->first();
        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}
