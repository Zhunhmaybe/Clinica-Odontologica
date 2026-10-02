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
                'resources/css/components/pacientes/index.css',
                'resources/css/components/pacientes/create.css',
                'resources/css/login/login.css',
                'resources/css/login/two-factor.css'
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
