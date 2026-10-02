<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de Spatie (Muy importante para evitar bugs)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();


        //1. Permisos

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
        
        //historias clínicas
        Permission::firstOrCreate(["name" => "ver-historia", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "editar-historia", "guard_name" => "web"]);

        // auditoria
        Permission::firstOrCreate(["name" => "ver-auditoria", "guard_name" => "web"]);

        // usuarios
        Permission::firstOrCreate(["name" => "ver-usuarios", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "crear-usuarios", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "editar-usuarios", "guard_name" => "web"]);
        Permission::firstOrCreate(["name" => "eliminar-usuarios", "guard_name" => "web"]);

        // ----------------------------------------------------
        // 2. CREACIÓN DE ROLES (name + guard_name)
        // ----------------------------------------------------
        // Usamos firstOrCreate para asegurar que existan en la BDD
        $rolUsuario       = Role::firstOrCreate(["name" => "usuario", "guard_name" => "web"]);
        $rolRecepcionista = Role::firstOrCreate(["name" => "recepcionista", "guard_name" => "web"]);
        $rolAuditor       = Role::firstOrCreate(["name" => "auditor", "guard_name" => "web"]);
        $rolDoctor        = Role::firstOrCreate(["name" => "doctor", "guard_name" => "web"]);
        $rolAdmin         = Role::firstOrCreate(["name" => "admin", "guard_name" => "web"]);
        $rolSuperAdmin    = Role::firstOrCreate(["name" => "Super_admin", "guard_name" => "web"]);
        // ----------------------------------------------------
        // 3. ASIGNAR PERMISOS A ROLES
        // ----------------------------------------------------
        // syncPermissions asegura que si corres el seeder varias veces
        // no duplique ni cause errores, dejando exactamente los permisos indicados.
        // Usuario
        $rolUsuario->syncPermissions([
            'ver-Perfil',
        ]);
        // Recepcionista
        $rolRecepcionista->syncPermissions([
            'ver-Perfil',
            'editar-Perfil',
            '2FA',
            'ver-citas',
            'crear-citas',
            'editar-citas',
            'ver-pacientes',
            'crear-pacientes',
            'editar-pacientes',
        ]);
        // Auditor
        $rolAuditor->syncPermissions([
            'ver-Perfil',
            'editar-Perfil',
            '2FA',
            'ver-citas',
            'ver-pacientes',
            'ver-auditoria',
        ]);
        // Doctor
        $rolDoctor->syncPermissions([
            "ver-Perfil",
            "editar-Perfil",
            "2FA",
            "ver-citas",
            "editar-citas",
            "ver-pacientes",
            "crear-pacientes",
            "editar-pacientes",
            "ver-historia",
            "editar-historia",
        ]);
        // Admin (Todos los permisos)
        $rolAdmin->syncPermissions(Permission::all());

        // Super_admin (Todos los permisos, más adelante puede tener accesos del sistema)
        $rolSuperAdmin->syncPermissions(Permission::all());
    }
}
