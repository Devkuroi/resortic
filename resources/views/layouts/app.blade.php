<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESORTIC – @yield('title', 'Gestión Hotelera')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; font-size: 1.3rem; }

        /* ── Sidebar desktop ── */
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #fff;
            border-right: 1px solid #e3e6ea;
            width: 230px;
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #495057; padding: 9px 20px; font-size: 0.875rem;
            border-radius: 0; display: flex; align-items: center; gap: 8px;
        }
        .sidebar .nav-link:hover  { background: #f0f4ff; color: #0d6efd; }
        .sidebar .nav-link.active { background: #e8f0fe; color: #0d6efd; font-weight: 500; border-left: 3px solid #0d6efd; }
        .sidebar .nav-section { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #adb5bd; padding: 16px 20px 4px; }

        /* ── Offcanvas sidebar (mobile) ── */
        .offcanvas-sidebar .nav-link {
            color: #495057; padding: 10px 20px; font-size: 0.9rem;
            display: flex; align-items: center; gap: 8px;
        }
        .offcanvas-sidebar .nav-link:hover  { background: #f0f4ff; color: #0d6efd; }
        .offcanvas-sidebar .nav-link.active { background: #e8f0fe; color: #0d6efd; font-weight: 500; border-left: 3px solid #0d6efd; }
        .offcanvas-sidebar .nav-section { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #adb5bd; padding: 16px 20px 4px; }

        .main-content { flex: 1; padding: 24px; overflow-x: auto; min-width: 0; }
        @media (max-width: 767.98px) {
            .main-content { padding: 16px; }
        }

        .stat-card { border: none; border-radius: 10px; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .table thead th { background: #f8f9fa; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: 1px solid #dee2e6; }
        .table tbody tr:hover { background: #f8f9fa; }
        .page-header { margin-bottom: 24px; }
        .page-header h4 { font-weight: 600; color: #212529; margin-bottom: 4px; }
        .page-header p  { color: #6c757d; font-size: 0.875rem; margin: 0; }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid px-3">

        {{-- Hamburger (mobile) --}}
        <button class="btn btn-outline-light btn-sm me-2 d-md-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas"
                aria-controls="sidebarOffcanvas">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand" href="{{ route('reservations.availability') }}">
            <i class="bi bi-building me-2"></i>RESORTIC
        </a>

        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="text-white-50 small d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                <span class="badge bg-white text-primary ms-1">{{ Auth::user()->role_label }}</span>
            </span>
            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Salir</span>
                </button>
            </form>
        </div>
    </div>
</nav>
<div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div>
            <h6 class="offcanvas-title fw-bold" id="sidebarOffcanvasLabel">
                <i class="bi bi-building me-2 text-primary"></i>RESORTIC
            </h6>
            <small class="text-muted">
                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                <span class="badge bg-primary ms-1">{{ Auth::user()->role_label }}</span>
            </small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="nav-section">Búsqueda</div>
        <a href="{{ route('reservations.availability') }}"
           class="nav-link {{ request()->routeIs('reservations.availability') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Disponibilidad
        </a>

        <div class="nav-section">Reservas</div>
        <a href="{{ route('reservations.index') }}"
           class="nav-link {{ request()->routeIs('reservations.index','reservations.show') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Reservas
        </a>

        @if(Auth::user()->isAdmin() || Auth::user()->isHotel())
            <a href="{{ route('reservations.create') }}"
               class="nav-link {{ request()->routeIs('reservations.create','reservations.edit') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Nueva reserva
            </a>
        @endif

        @if(Auth::user()->isAdmin() || Auth::user()->isHotel())
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

        @if(Auth::user()->isAdmin())
            <div class="nav-section">Administración</div>
            <a href="{{ route('accounts.index') }}"
               class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Cuentas
            </a>
        @endif
    </div>
</div>

<div class="d-flex">

    <div class="sidebar d-none d-md-block">
        <div class="py-2">
            <div class="nav-section">Búsqueda</div>
            <a href="{{ route('reservations.availability') }}"
               class="nav-link {{ request()->routeIs('reservations.availability') ? 'active' : '' }}">
                <i class="bi bi-search"></i> Disponibilidad
            </a>

            <div class="nav-section">Reservas</div>
            <a href="{{ route('reservations.index') }}"
               class="nav-link {{ request()->routeIs('reservations.index','reservations.show') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Reservas
            </a>

            @if(Auth::user()->isAdmin() || Auth::user()->isHotel())
                <a href="{{ route('reservations.create') }}"
                   class="nav-link {{ request()->routeIs('reservations.create','reservations.edit') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i> Nueva reserva
                </a>
            @endif

            @if(Auth::user()->isAdmin() || Auth::user()->isHotel())
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

            @if(Auth::user()->isAdmin())
                <div class="nav-section">Administración</div>
                <a href="{{ route('accounts.index') }}"
                   class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Cuentas
                </a>
            @endif
        </div>
    </div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
