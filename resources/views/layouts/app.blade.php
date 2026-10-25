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
            <span style="display: flex; align-items: center; gap: 0.4rem;">
                <x-icon name="map-pin" size="14" />
                <span>Entrega en Barcelona ciudad (08001–08042) · Recogida gratuita en tienda</span>
            </span>
            <span style="display: flex; align-items: center; gap: 0.4rem;">
                <x-icon name="phone" size="14" />
                <span>93 123 45 67 · Lun a Sáb 8:30–20:30</span>
            </span>
        </div>
    </div>

    <header class="site-header">
        <div class="container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-mark">
                    <x-icon name="shopping-bag" size="20" stroke="2.2" />
                </div>
                <span>Local<b>Market</b></span>
            </a>

            <form action="{{ route('catalog.index') }}" method="GET" class="header-search" role="search">
                <div class="search-ico">
                    <x-icon name="search" size="16" />
                </div>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar frutas, lácteos, panadería…" aria-label="Buscar productos">
            </form>

            <button type="button" class="menu-toggle" data-toggle="#main-nav" aria-label="Abrir menú" aria-expanded="false">
                <x-icon name="menu" size="22" />
            </button>

            <nav id="main-nav" class="nav">
                <a href="{{ route('catalog.index') }}">
                    <x-icon name="shopping-bag" size="16" />
                    <span>Catálogo</span>
                </a>

                @auth
                    @if(auth()->user()->isStaff())
                        <a href="{{ route('staff.dashboard') }}" class="badge badge-green" style="padding: 0.35rem 0.75rem;">
                            <x-icon name="layout-dashboard" size="14" />
                            <span>Gestión</span>
                        </a>
                    @else
                        <a href="{{ route('account.orders') }}">
                            <x-icon name="package" size="16" />
                            <span>Mis pedidos</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="link" title="Cerrar sesión">
                            <x-icon name="log-out" size="15" />
                            <span>Salir</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}">
                        <x-icon name="user" size="16" />
                        <span>Entrar</span>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-outline">Crear cuenta</a>
                @endauth

                <a href="{{ route('cart.index') }}" class="cart-link btn btn-sm btn-primary">
                    <x-icon name="shopping-cart" size="16" stroke="2.2" />
                    <span>Cesta</span>
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
        @if (session('success') || session('error') || $errors->any())
        <div class="container" style="padding-top: 1.25rem;">
            @if (session('success'))
                <div class="alert alert-success" data-autohide role="alert">
                    <x-icon name="check" size="20" stroke="2.5" />
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error" role="alert">
                    <x-icon name="alert-triangle" size="20" stroke="2.5" />
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <x-icon name="alert-circle" size="20" stroke="2.5" />
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
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="logo" style="color: #fff; margin-bottom: 0.8rem;">
                        <div class="logo-mark" style="background: linear-gradient(135deg, var(--emerald-400), var(--emerald-600));">
                            <x-icon name="shopping-bag" size="20" />
                        </div>
                        <span>Local<b>Market</b></span>
                    </div>
                    <p style="line-height: 1.6; max-width: 360px;">Tu supermercado de barrio en Barcelona. Frescura diaria, productores locales y compra fácil con entrega a domicilio o recogida rápida en tienda.</p>
                </div>
                <div>
                    <h4>Atención al cliente</h4>
                    <p style="line-height: 1.6;">Carrer del Mercat, 12<br>08001 Barcelona<br>pedidos@localmarket.test</p>
                </div>
                <div>
                    <h4>Acceso rápido</h4>
                    <p style="line-height: 1.8;">
                        <a href="{{ route('catalog.index') }}">Catálogo online</a><br>
                        <a href="{{ route('cart.index') }}">Mi cesta</a><br>
                        <a href="{{ route('login') }}">Área de clientes y empleados</a>
                    </p>
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
