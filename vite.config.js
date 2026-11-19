import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js', 
                'resources/css/historia-clinica.css',
                'resources/css/admin/usuarios.css',
                'resources/css/navbar/editar-perfil.css',
                'resources/css/navbar/p1_home.css',
                'resources/css/navbar/contactos.css',
                'resources/css/navbar/servicios.css',
                'resources/css/components/citas/create.css',
                'resources/css/components/pacientes/index.css',
                'resources/css/components/pacientes/create.css',
                'resources/css/login/login.css',
                'resources/css/login/register.css',
                'resources/css/login/resetpassword.css',
                'resources/css/login/unlock.css',
                'resources/css/login/two-factor.css',
                'resources/css/usuario/home.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
