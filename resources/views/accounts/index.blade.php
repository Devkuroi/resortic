@extends('layouts.app')
@section('title', 'Gestión de cuentas')

@section('content')

<!-- Encabezado -->
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4><i class="bi bi-people me-2"></i>Gestión de cuentas</h4>
        <p>Administra los usuarios, hoteles y clientes registrados en el sistema.</p>
    </div>
    <a href="{{ route('accounts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nueva cuenta
    </a>
</div>

<!-- Estadísticas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
                    <div class="text-muted small">Total cuentas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['active'] }}</div>
                    <div class="text-muted small">Activas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-building-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['hotels'] }}</div>
                    <div class="text-muted small">Hoteles</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['clients'] }}</div>
                    <div class="text-muted small">Clientes</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('accounts.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nombre o correo..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Rol</label>
                <select name="role" class="form-select">
                    <option value="">Todos</option>
                    <option value="admin"  {{ request('role') == 'admin'  ? 'selected' : '' }}>Administrador</option>
                    <option value="hotel"  {{ request('role') == 'hotel'  ? 'selected' : '' }}>Hotel</option>
                    <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Cliente</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($accounts->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <p class="mb-0">No se encontraron cuentas con los filtros aplicados.</p>
                <a href="{{ route('accounts.index') }}" class="btn btn-link btn-sm mt-2">Limpiar filtros</a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registrado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                    <tr>
                        <td class="text-muted small">{{ $account->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                                     style="width:34px;height:34px;font-size:0.75rem;font-weight:600;flex-shrink:0;">
                                    {{ strtoupper(substr($account->name, 0, 2)) }}
                                </div>
                                <span class="fw-semibold small">{{ $account->name }}</span>
                            </div>
                        </td>
                        <td class="text-muted small">{{ $account->email }}</td>
                        <td>
                            <span class="badge bg-{{ $account->role_badge }} bg-opacity-10 text-{{ $account->role_badge }} border border-{{ $account->role_badge }} border-opacity-25">
                                {{ $account->role_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $account->status_badge }} bg-opacity-10 text-{{ $account->status_badge }} border border-{{ $account->status_badge }} border-opacity-25">
                                <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;vertical-align:middle;"></i>
                                {{ $account->status_label }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $account->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('accounts.edit', $account) }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('accounts.destroy', $account) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar la cuenta de {{ addslashes($account->name) }}? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($accounts->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted small">
                Mostrando {{ $accounts->firstItem() }}–{{ $accounts->lastItem() }} de {{ $accounts->total() }} cuentas
            </div>
            {{ $accounts->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="px-3 py-2 border-top text-muted small">
            {{ $accounts->total() }} {{ $accounts->total() === 1 ? 'cuenta' : 'cuentas' }} en total
        </div>
        @endif
        @endif
    </div>
</div>

@endsection
