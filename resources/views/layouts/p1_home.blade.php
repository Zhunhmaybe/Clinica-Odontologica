<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title','DentlaSoft')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    @vite('resources/css/navbar/p1_home.css')
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg bg-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="/images/logo-danny.png" alt="Logo Danny">
                <span class="ms-2 fw-bold text-white">Consultorio Danny</span>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li><a class="nav-link text-white" href="{{ route('login') }}">Ingresar</a></li>
                    <li><a class="nav-link text-white" href="{{ route('servicios')}}">Servicios</a></li>
                    <li><a class="nav-link text-white" href="{{ route('contacto')}}">Contactanos</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="text-center mt-auto">
        <div class="container">
            <p class="mb-0">
                © 2025 Consultorio Odontológico Danny | Cuidando sonrisas
            </p>
        </div>
    </footer>
</body>

</html>