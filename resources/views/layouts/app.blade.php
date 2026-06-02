<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESORTIC – @yield('title', 'Gestión Hotelera')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            /* Espacio inferior en móvil para la barra de navegación */
            padding-bottom: 0;
        }

        @media (max-width: 767.98px) {
            body {
                padding-bottom: 70px;
            }
        }

        /* ── Navbar ── */
        .navbar-brand { font-weight: 700; letter-spacing: 1px; font-size: 1.3rem; }

        /* ── Sidebar (escritorio) ── */
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #fff;
            border-right: 1px solid #e3e6ea;
            width: 230px;
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #495057;
            padding: 9px 20px;
            font-size: 0.875rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sidebar .nav-link:hover  { background: #f0f4ff; color: #0d6efd; }
        .sidebar .nav-link.active {
            background: #e8f0fe;
            color: #0d6efd;
            font-weight: 500;
            border-left: 3px solid #0d6efd;
        }
        .sidebar .nav-section {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #adb5bd;
            padding: 16px 20px 4px;
        }

        /* ── Offcanvas (menú lateral móvil) ── */
        .offcanvas-nav .nav-link {
            color: #495057;
            padding: 11px 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 8px;
            margin: 1px 8px;
        }
        .offcanvas-nav .nav-link:hover  { background: #f0f4ff; color: #0d6efd; }
        .offcanvas-nav .nav-link.active {
            background: #e8f0fe;
            color: #0d6efd;
            font-weight: 600;
        }
        .offcanvas-nav .nav-section {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #adb5bd;
            padding: 14px 20px 4px;
        }
        .offcanvas-header { border-bottom: 1px solid #e3e6ea; }
        .offcanvas-user-badge {
            background: #e8f0fe;
            border-radius: 10px;
            padding: 10px 16px;
            margin: 12px;
        }

        /* ── Bottom nav (móvil) ── */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #fff;
            border-top: 1px solid #dee2e6;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
        }

        @media (max-width: 767.98px) {
            .bottom-nav { display: flex; }
        }

        .bottom-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 4px 6px;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.65rem;
            font-weight: 500;
            gap: 2px;
            border: none;
            background: none;
            transition: color 0.15s;
            cursor: pointer;
        }
        .bottom-nav-item i { font-size: 1.25rem; line-height: 1; }
        .bottom-nav-item.active { color: #0d6efd; }
        .bottom-nav-item:hover  { color: #0d6efd; }

        /* ── Contenido principal ── */
        .main-content { flex: 1; padding: 24px; overflow-x: auto; min-width: 0; }

        @media (max-width: 575.98px) {
            .main-content { padding: 16px 12px; }
        }

        /* ── Componentes comunes ── */
        .stat-card { border: none; border-radius: 10px; }
        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .table thead th {
            background: #f8f9fa;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 1px solid #dee2e6;
        }
        .table tbody tr:hover { background: #f8f9fa; }
        .page-header { margin-bottom: 24px; }
        .page-header h4 { font-weight: 600; color: #212529; margin-bottom: 4px; }
        .page-header p  { color: #6c757d; font-size: 0.875rem; margin: 0; }
    </style>
    @stack('styles')
</head>
<body>

{{-- ═══════════════════════════════════════════════
     NAVBAR SUPERIOR
════════════════════════════════════════════════ --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid px-3">

        {{-- Botón hamburguesa (solo móvil) --}}
        <button class="btn btn-outline-light btn-sm me-2 d-md-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileMenu"
                aria-controls="mobileMenu"
                aria-label="Abrir menú">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand" href="{{ route('reservations.availability') }}">
            <i class="bi bi-building me-2"></i>RESORTIC
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-white-50 small d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i>{{ session('user_name') }}
                <span class="badge bg-white text-primary ms-1">
                    {{ session('user_role') === 'admin' ? 'Admin' : (session('user_role') === 'hotel' ? 'Hotel' : 'Cliente') }}
                </span>
            </span>
            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    <span class="d-none d-sm-inline">Salir</span>
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ═══════════════════════════════════════════════
     OFFCANVAS – MENÚ LATERAL MÓVIL
════════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <div>
            <h6 class="mb-0 fw-bold text-primary" id="mobileMenuLabel">
                <i class="bi bi-building me-2"></i>RESORTIC
            </h6>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    {{-- Info del usuario --}}
    <div class="offcanvas-user-badge">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                 style="width:36px;height:36px;font-size:0.8rem;font-weight:700;flex-shrink:0;">
                {{ strtoupper(substr(session('user_name', 'U'), 0, 2)) }}
            </div>
            <div>
                <div class="fw-semibold small">{{ session('user_name') }}</div>
                <span class="badge bg-primary" style="font-size:0.65rem;">
                    {{ session('user_role') === 'admin' ? 'Admin' : (session('user_role') === 'hotel' ? 'Hotel' : 'Cliente') }}
                </span>
            </div>
        </div>
    </div>

    <div class="offcanvas-body p-0 offcanvas-nav">

        {{-- Búsqueda: todos --}}
        <div class="nav-section">Búsqueda</div>
        <a href="{{ route('reservations.availability') }}"
           class="nav-link {{ request()->routeIs('reservations.availability') ? 'active' : '' }}"
           data-bs-dismiss="offcanvas">
            <i class="bi bi-search"></i> Disponibilidad
        </a>

        {{-- Reservas --}}
        <div class="nav-section">Reservas</div>
        <a href="{{ route('reservations.index') }}"
           class="nav-link {{ request()->routeIs('reservations.index','reservations.show','reservations.edit') ? 'active' : '' }}"
           data-bs-dismiss="offcanvas">
            <i class="bi bi-calendar-check"></i> Reservas
        </a>
        @if(in_array(session('user_role'), ['admin','client']))
        <a href="{{ route('reservations.create') }}"
           class="nav-link {{ request()->routeIs('reservations.create') ? 'active' : '' }}"
           data-bs-dismiss="offcanvas">
            <i class="bi bi-plus-circle"></i> Nueva reserva
        </a>
        @endif

        {{-- Habitaciones --}}
        @if(in_array(session('user_role'), ['admin','hotel']))
        <div class="nav-section">Habitaciones</div>
        <a href="{{ route('rooms.index') }}"
           class="nav-link {{ request()->routeIs('rooms.index','rooms.edit') ? 'active' : '' }}"
           data-bs-dismiss="offcanvas">
            <i class="bi bi-door-open"></i> Habitaciones
        </a>
        <a href="{{ route('rooms.create') }}"
           class="nav-link {{ request()->routeIs('rooms.create') ? 'active' : '' }}"
           data-bs-dismiss="offcanvas">
            <i class="bi bi-plus-circle"></i> Nueva habitación
        </a>
        @endif

        {{-- Administración --}}
        @if(session('user_role') === 'admin')
        <div class="nav-section">Administración</div>
        <a href="{{ route('accounts.index') }}"
           class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}"
           data-bs-dismiss="offcanvas">
            <i class="bi bi-people"></i> Cuentas
        </a>
        @endif

        {{-- Cerrar sesión --}}
        <div class="nav-section">Sesión</div>
        <form action="{{ route('logout') }}" method="POST" class="mx-2 mb-3">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 btn-sm mt-1">
                <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
            </button>
        </form>

    </div>
</div>

{{-- ═══════════════════════════════════════════════
     LAYOUT PRINCIPAL
════════════════════════════════════════════════ --}}
<div class="d-flex">

    {{-- Sidebar escritorio (oculto en móvil) --}}
    <div class="sidebar d-none d-md-block">
        <div class="py-2">

            <div class="nav-section">Búsqueda</div>
            <a href="{{ route('reservations.availability') }}"
               class="nav-link {{ request()->routeIs('reservations.availability') ? 'active' : '' }}">
                <i class="bi bi-search"></i> Disponibilidad
            </a>

            <div class="nav-section">Reservas</div>
            <a href="{{ route('reservations.index') }}"
               class="nav-link {{ request()->routeIs('reservations.index','reservations.show','reservations.edit') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Reservas
            </a>
            @if(in_array(session('user_role'), ['admin','client']))
            <a href="{{ route('reservations.create') }}"
               class="nav-link {{ request()->routeIs('reservations.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Nueva reserva
            </a>
            @endif

            @if(in_array(session('user_role'), ['admin','hotel']))
            <div class="nav-section">Habitaciones</div>
            <a href="{{ route('rooms.index') }}"
               class="nav-link {{ request()->routeIs('rooms.index','rooms.edit') ? 'active' : '' }}">
                <i class="bi bi-door-open"></i> Habitaciones
            </a>
            <a href="{{ route('rooms.create') }}"
               class="nav-link {{ request()->routeIs('rooms.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Nueva habitación
            </a>
            @endif

            @if(session('user_role') === 'admin')
            <div class="nav-section">Administración</div>
            <a href="{{ route('accounts.index') }}"
               class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Cuentas
            </a>
            @endif
        </div>
    </div>

    {{-- Contenido --}}
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     BARRA DE NAVEGACIÓN INFERIOR (solo móvil)
════════════════════════════════════════════════ --}}
<nav class="bottom-nav">

    {{-- Disponibilidad --}}
    <a href="{{ route('reservations.availability') }}"
       class="bottom-nav-item {{ request()->routeIs('reservations.availability') ? 'active' : '' }}">
        <i class="bi bi-search"></i>
        <span>Buscar</span>
    </a>

    {{-- Reservas --}}
    <a href="{{ route('reservations.index') }}"
       class="bottom-nav-item {{ request()->routeIs('reservations.index','reservations.show') ? 'active' : '' }}">
        <i class="bi bi-calendar-check"></i>
        <span>Reservas</span>
    </a>

    {{-- Nueva reserva (admin/client) o Habitaciones (hotel) --}}
    @if(in_array(session('user_role'), ['admin','client']))
    <a href="{{ route('reservations.create') }}"
       class="bottom-nav-item {{ request()->routeIs('reservations.create') ? 'active' : '' }}">
        <i class="bi bi-plus-circle"></i>
        <span>Reservar</span>
    </a>
    @endif

    @if(in_array(session('user_role'), ['admin','hotel']))
    <a href="{{ route('rooms.index') }}"
       class="bottom-nav-item {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
        <i class="bi bi-door-open"></i>
        <span>Habitaciones</span>
    </a>
    @endif

    {{-- Cuentas (admin) --}}
    @if(session('user_role') === 'admin')
    <a href="{{ route('accounts.index') }}"
       class="bottom-nav-item {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Cuentas</span>
    </a>
    @endif

    {{-- Menú completo (siempre visible) --}}
    <button class="bottom-nav-item"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobileMenu">
        <i class="bi bi-grid"></i>
        <span>Menú</span>
    </button>

</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>