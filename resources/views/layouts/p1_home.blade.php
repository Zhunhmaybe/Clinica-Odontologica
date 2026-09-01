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
    <nav class="navbar navbar-expand-lg navbar-dark bg-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="/images/logo-danny.png" alt="Logo Danny" style="max-height: 50px;" class="img-fluid">
                <span class="ms-2 fw-bold text-white">Consultorio Danny</span>
            </a>
            
            <!-- Botón Hamburguesa para Móviles -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto text-center mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('login') }}">Ingresar</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('servicios')}}">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('contacto')}}">Contactanos</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="text-center mt-auto py-3">
        <div class="container">
            <p class="mb-0 text-white">
                © 2025 Consultorio Odontológico Danny | Cuidando sonrisas
            </p>
        </div>
    </footer>

    <!-- Scripts necesarios para Bootstrap Responsive Navbar -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>