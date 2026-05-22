<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESORTIC – @yield('title', 'Gestión Hotelera')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; }

        /* Navbar */
        .navbar-brand { font-weight: 700; letter-spacing: 1px; font-size: 1.3rem; }

        /* Sidebar */
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #fff;
            border-right: 1px solid #e3e6ea;
            width: 220px;
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #495057;
            padding: 10px 20px;
            font-size: 0.875rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sidebar .nav-link:hover  { background: #f0f4ff; color: #0d6efd; }
        .sidebar .nav-link.active { background: #e8f0fe; color: #0d6efd; font-weight: 500; border-left: 3px solid #0d6efd; }
        .sidebar .nav-section { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #adb5bd; padding: 16px 20px 4px; }

        /* Content */
        .main-content { flex: 1; padding: 24px; overflow-x: auto; }

        /* Cards */
        .stat-card { border: none; border-radius: 10px; }
        .stat-card .card-body { padding: 1.25rem; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

        /* Table */
        .table thead th { background: #f8f9fa; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: 1px solid #dee2e6; }
        .table tbody tr:hover { background: #f8f9fa; }

        /* Page header */
        .page-header { margin-bottom: 24px; }
        .page-header h4 { font-weight: 600; color: #212529; margin-bottom: 4px; }
        .page-header p  { color: #6c757d; font-size: 0.875rem; margin: 0; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid px-3">
        <a class="navbar-brand" href="{{ route('accounts.index') }}">
            <i class="bi bi-building me-2"></i>RESORTIC
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-white-50 small d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i>{{ session('user_name') }}
                <span class="badge bg-white text-primary ms-1">{{ ucfirst(session('user_role')) }}</span>
            </span>
            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Salir
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Layout principal -->
<div class="d-flex">

    <!-- Sidebar -->
    <div class="sidebar d-none d-md-block">
        <div class="py-2">
            <div class="nav-section">Principal</div>
            <a href="{{ route('accounts.index') }}" class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Cuentas
            </a>
            <div class="nav-section">Próximamente</div>
            <a href="#" class="nav-link text-muted">
                <i class="bi bi-door-open"></i> Habitaciones
            </a>
            <a href="#" class="nav-link text-muted">
                <i class="bi bi-calendar-check"></i> Reservas
            </a>
            <a href="#" class="nav-link text-muted">
                <i class="bi bi-credit-card"></i> Pagos
            </a>
            <a href="#" class="nav-link text-muted">
                <i class="bi bi-graph-up"></i> Contabilidad
            </a>
        </div>
    </div>

    <!-- Contenido -->
    <div class="main-content">
        {{-- Alertas globales --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
