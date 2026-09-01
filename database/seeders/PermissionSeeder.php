<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de Spatie (Muy importante para evitar bugs)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //PERFILES
        Permission::firstOrCreate(["name" => "ver-Perfil", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "editar-Perfil", "guard_name" => "web"]);

        //2FA
        Permission::firstOrCreate(["name" => "2FA", "guard_name" => "web"]);
        //citas
        Permission::firstOrCreate(["name" => "ver-citas", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "crear-citas", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "editar-citas", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "eliminar-citas", "guard_name" => "web"]);
        //pacientes
        Permission::firstOrCreate(["name" => "ver-pacientes", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "crear-pacientes", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "editar-pacientes", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "eliminar-pacientes", "guard_name" => "web"]);

        //----------------------------------------------------
        //Asignar permisos a roles
        //----------------------------------------------------
        //Crear la variable para obtener el rol de usuario
        /** @var \Spatie\Permission\Models\Role $rolUsuario */
        $rolUsuario = Role::where('name', 'usuario')->first();
        if ($rolUsuario) {
            $rolUsuario->givePermissionTo([
                'ver-Perfil',
            ]);
        }

        /** @var \Spatie\Permission\Models\Role $rolrecepcionista */
        $rolrecepcionista= Role::where('name','recepcionista')->first();
        if($rolrecepcionista){
            $rolrecepcionista->givePermissionTo([
                'ver-Perfil',
                'editar-Perfil',
                '2FA',
                'ver-citas',
                'ver-pacientes'
            ]);
        }

        /** @var \Spatie\Permission\Models\Role $rolauditor */
        $rolauditor = Role::where('name','auditor')->first();
        if($rolauditor){
            $rolauditor->givePermissionTo([
                'ver-Perfil',
                'editar-Perfil',
                '2FA',
                'ver-citas',
                'ver-pacientes'
            ]);
        }

        /** @var \Spatie\Permission\Models\Role $roladmin */
        $roladmin = Role::where('name','admin')->first();
        if($roladmin){
            $roladmin->givePermissionTo([
                'ver-Perfil',
                'editar-Perfil',
                '2FA',
                'ver-citas',
                'ver-pacientes'
            ]);
        }

        /** @var \Spatie\Permission\Models\Role $roldoctor */
        $roldoctor = Role::where('name','doctor')->first();
        if($roldoctor){
            $roldoctor->givePermissionTo([
                'ver-Perfil',
                'editar-Perfil',
                '2FA',
                'ver-citas',
                'ver-pacientes'
            ]);
        }

    }
}
