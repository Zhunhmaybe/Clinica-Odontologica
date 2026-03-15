<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('styles')
    @vite('resources/css/usuario/home.css')
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="logo">
                <img src="/images/logo-danny.png" alt="Logo Danny">
            </div>                


            <div class="user">
                <strong>{{ Auth::user()->nombre }}</strong><br>
                <small>{{Auth::user()->nombre_rol}}</small>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button class="btn btn-sm btn-light w-100">Cerrar Sesión</button>
                </form>
            </div>
        </aside>
        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>