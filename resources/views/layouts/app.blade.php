<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — LocalMarket' : 'LocalMarket — Supermercado de proximidad en Barcelona' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <div class="topbar">
        <div class="container">
            <span>📍 Entrega en Barcelona ciudad (08001–08042) · Recogida gratuita en tienda</span>
            <span>📞 93 123 45 67 · Lun a Sáb 8:30–20:30</span>
        </div>
    </div>

    <header class="site-header">
        <div class="container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-mark">🌿</div>
                <span>Local<b>Market</b></span>
            </a>

            <form action="{{ route('catalog.index') }}" method="GET" class="header-search" role="search">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar frutas, lácteos, panadería…" aria-label="Buscar productos">
            </form>

            <button type="button" class="menu-toggle" data-toggle="#main-nav" aria-label="Abrir menú" aria-expanded="false">☰</button>

            <nav id="main-nav" class="nav">
                <a href="{{ route('catalog.index') }}">Catálogo</a>

                @auth
                    @if(auth()->user()->isStaff())
                        <a href="{{ route('staff.dashboard') }}" class="badge badge-green">Panel de gestión</a>
                    @else
                        <a href="{{ route('account.orders') }}">Mis pedidos</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="link">Salir ({{ auth()->user()->name }})</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-outline">Crear cuenta</a>
                @endauth

                <a href="{{ route('cart.index') }}" class="cart-link btn btn-sm btn-primary">
                    🛒 Cesta
                    @php
                        $cartCount = session('cart') ? count(session('cart')) : 0;
                    @endphp
                    @if($cartCount > 0)
                        <span class="cart-count">{{ $cartCount }}</span>
                    @endif
                </a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success" data-autohide role="alert">
                    <span>✓</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error" role="alert">
                    <span>⚠️</span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <span>⚠️</span>
                    <div>
                        <strong>Por favor, corrige los siguientes errores:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="logo" style="color: #fff; margin-bottom: .8rem;">
                        <div class="logo-mark">🌿</div>
                        <span>Local<b>Market</b></span>
                    </div>
                    <p>Tu supermercado de barrio en Barcelona. Frescura diaria, productores locales y compra fácil con entrega a domicilio o recogida rápida en tienda.</p>
                </div>
                <div>
                    <h4>Atención al cliente</h4>
                    <p>Carrer del Mercat, 12<br>08001 Barcelona<br>pedidos@localmarket.test</p>
                </div>
                <div>
                    <h4>Acceso rápido</h4>
                    <p><a href="{{ route('catalog.index') }}">Catálogo online</a><br>
                    <a href="{{ route('cart.index') }}">Mi cesta</a><br>
                    <a href="{{ route('login') }}">Área de clientes y empleados</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; {{ date('Y') }} LocalMarket Barcelona. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
