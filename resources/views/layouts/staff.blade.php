<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — Gestión LocalMarket' : 'Panel de Gestión — LocalMarket' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="staff-body">
    <aside id="staff-sidebar" class="sidebar">
        <a href="{{ route('staff.dashboard') }}" class="logo">
            <div class="logo-mark">🌿</div>
            <span>Local<b>Market</b></span>
        </a>

        <div class="side-section">
            <div class="side-title">Operaciones</div>
            <a href="{{ route('staff.dashboard') }}" class="side-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <span class="ico">📊</span> Resumen
            </a>

            @if(auth()->user()->hasRole('admin', 'warehouse', 'cashier'))
                <a href="{{ route('staff.orders.index') }}" class="side-link {{ request()->routeIs('staff.orders.*') ? 'active' : '' }}">
                    <span class="ico">📦</span> Pedidos
                </a>
            @endif

            @if(auth()->user()->hasRole('admin', 'warehouse'))
                <a href="{{ route('staff.inventory.index') }}" class="side-link {{ request()->routeIs('staff.inventory.*') ? 'active' : '' }}">
                    <span class="ico">🏬</span> Inventario
                </a>
                <a href="{{ route('staff.batches.index') }}" class="side-link {{ request()->routeIs('staff.batches.*') ? 'active' : '' }}">
                    <span class="ico">🏷️</span> Lotes y Caducidades
                </a>
            @endif

            @if(auth()->user()->hasRole('admin', 'cashier'))
                <a href="{{ route('staff.pos.create') }}" class="side-link {{ request()->routeIs('staff.pos.*') ? 'active' : '' }}">
                    <span class="ico">💳</span> Venta en Tienda <small class="badge badge-amber" style="margin-left:auto;">Scope</small>
                </a>
            @endif
        </div>

        @if(auth()->user()->hasRole('admin'))
            <div class="side-section">
                <div class="side-title">Administración</div>
                <a href="{{ route('staff.products.index') }}" class="side-link {{ request()->routeIs('staff.products.*') ? 'active' : '' }}">
                    <span class="ico">🍎</span> Productos
                </a>
                <a href="{{ route('staff.categories.index') }}" class="side-link {{ request()->routeIs('staff.categories.*') ? 'active' : '' }}">
                    <span class="ico">📁</span> Categorías
                </a>
                <a href="{{ route('staff.coupons.index') }}" class="side-link {{ request()->routeIs('staff.coupons.*') ? 'active' : '' }}">
                    <span class="ico">🎟️</span> Cupones
                </a>
                <a href="{{ route('staff.users.index') }}" class="side-link {{ request()->routeIs('staff.users.*') ? 'active' : '' }}">
                    <span class="ico">👥</span> Usuarios
                </a>
                <a href="{{ route('staff.audit.index') }}" class="side-link {{ request()->routeIs('staff.audit.*') ? 'active' : '' }}">
                    <span class="ico">📜</span> Auditoría
                </a>
            </div>
        @endif

        <div class="side-user">
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <span class="badge badge-green">{{ auth()->user()->roleLabel() }}</span>
            </div>
            <div style="margin-top: .6rem; display: flex; gap: .4rem;">
                <a href="{{ route('home') }}" class="btn btn-sm btn-ghost" style="color: #cfe6da; border: 1px solid rgba(255,255,255,0.2);">Ver tienda</a>
                <form action="{{ route('logout') }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="staff-main">
        <header class="staff-top">
            <div class="flex">
                <button type="button" class="btn btn-sm btn-outline staff-menu-toggle" data-toggle="#staff-sidebar" aria-label="Abrir menú lateral">☰ Menú</button>
                <h1>@yield('title', 'Panel de gestión')</h1>
            </div>
            <div>
                <span class="muted" style="font-size: .85rem;">{{ date('d/m/Y') }} · Barcelona</span>
            </div>
        </header>

        <div class="staff-content">
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
                        <strong>Corrige los siguientes errores:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
