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
            <div class="logo-mark">
                <x-icon name="shopping-bag" size="20" stroke="2.2" />
            </div>
            <span>Local<b>Market</b></span>
        </a>

        <div class="side-section">
            <div class="side-title">Operaciones</div>
            <a href="{{ route('staff.dashboard') }}" class="side-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <x-icon name="layout-dashboard" size="18" />
                <span>Resumen</span>
            </a>

            @if(auth()->user()->hasRole('admin', 'warehouse', 'cashier'))
                <a href="{{ route('staff.orders.index') }}" class="side-link {{ request()->routeIs('staff.orders.*') ? 'active' : '' }}">
                    <x-icon name="package" size="18" />
                    <span>Pedidos</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('admin', 'warehouse'))
                <a href="{{ route('staff.inventory.index') }}" class="side-link {{ request()->routeIs('staff.inventory.*') ? 'active' : '' }}">
                    <x-icon name="warehouse" size="18" />
                    <span>Inventario</span>
                </a>
                <a href="{{ route('staff.batches.index') }}" class="side-link {{ request()->routeIs('staff.batches.*') ? 'active' : '' }}">
                    <x-icon name="tags" size="18" />
                    <span>Lotes y Caducidades</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('admin', 'cashier'))
                <a href="{{ route('staff.pos.create') }}" class="side-link {{ request()->routeIs('staff.pos.*') ? 'active' : '' }}">
                    <x-icon name="credit-card" size="18" />
                    <span>Venta en Tienda (TPV)</span>
                    <span class="badge badge-green" style="margin-left: auto; font-size: 0.65rem; padding: 0.1rem 0.45rem;">POS</span>
                </a>
            @endif
        </div>

        @if(auth()->user()->hasRole('admin'))
            <div class="side-section">
                <div class="side-title">Administración</div>
                <a href="{{ route('staff.products.index') }}" class="side-link {{ request()->routeIs('staff.products.*') ? 'active' : '' }}">
                    <x-icon name="shopping-bag" size="18" />
                    <span>Productos</span>
                </a>
                <a href="{{ route('staff.categories.index') }}" class="side-link {{ request()->routeIs('staff.categories.*') ? 'active' : '' }}">
                    <x-icon name="folder" size="18" />
                    <span>Categorías</span>
                </a>
                <a href="{{ route('staff.coupons.index') }}" class="side-link {{ request()->routeIs('staff.coupons.*') ? 'active' : '' }}">
                    <x-icon name="ticket" size="18" />
                    <span>Cupones</span>
                </a>
                <a href="{{ route('staff.users.index') }}" class="side-link {{ request()->routeIs('staff.users.*') ? 'active' : '' }}">
                    <x-icon name="users" size="18" />
                    <span>Usuarios</span>
                </a>
                <a href="{{ route('staff.audit.index') }}" class="side-link {{ request()->routeIs('staff.audit.*') ? 'active' : '' }}">
                    <x-icon name="shield-check" size="18" />
                    <span>Auditoría</span>
                </a>
            </div>
        @endif

        <div class="side-user">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="side-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <strong style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name }}</strong>
                    <span class="badge badge-green" style="font-size: 0.68rem; padding: 0.1rem 0.5rem; margin-top: 0.2rem;">
                        {{ auth()->user()->roleLabel() }}
                    </span>
                </div>
            </div>
            <div style="margin-top: 0.85rem; display: flex; gap: 0.4rem;">
                <a href="{{ route('home') }}" class="btn btn-sm btn-ghost" style="color: #cbd5e1; border: 1px solid rgba(255,255,255,0.15); flex: 1; font-size: 0.75rem; padding: 0.35rem 0.5rem;">
                    <x-icon name="external-link" size="13" />
                    <span>Tienda</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline-form" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-ghost" style="color: #fca5a5; border: 1px solid rgba(239,68,68,0.25); width: 100%; font-size: 0.75rem; padding: 0.35rem 0.5rem;">
                        <x-icon name="log-out" size="13" />
                        <span>Salir</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="staff-main">
        <header class="staff-top">
            <div class="flex" style="gap: 1rem;">
                <button type="button" class="btn btn-sm btn-secondary staff-menu-toggle" data-toggle="#staff-sidebar" aria-label="Abrir menú lateral">
                    <x-icon name="menu" size="18" />
                    <span>Menú</span>
                </button>
                <h1>@yield('title', 'Panel de gestión')</h1>
            </div>
            <div class="flex" style="gap: 0.75rem;">
                <span class="badge badge-green" style="font-size: 0.78rem; padding: 0.25rem 0.65rem;">
                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10b981; margin-right: 4px;"></span>
                    Sistema en línea · Barcelona
                </span>
                <span class="muted" style="font-size: 0.82rem; font-variant-numeric: tabular-nums;">
                    {{ date('d/m/Y') }}
                </span>
            </div>
        </header>

        <div class="staff-content">
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
