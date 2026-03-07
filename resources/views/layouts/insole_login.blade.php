<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','Login Clinica')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
    @vite('resources/css/login/login.css')

    
</head>

<body>

    <div class="background-container">
        <div class="wave-decoration">
            <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="#ffffff" d="
                    M0,160
                    C240,160 480,240 720,240
                    C960,240 1200,160 1440,160
                    L1440,320 L0,320 Z
                ">
                </path>
            </svg>
        </div>
    </div>

    @yield('content')

</body>

</html>