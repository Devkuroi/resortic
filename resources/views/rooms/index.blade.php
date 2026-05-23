@extends('layouts.app')
@section('title', 'Habitaciones')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4><i class="bi bi-door-open me-2"></i>Gestión de Habitaciones</h4>
        <p>Administra las habitaciones registradas en el sistema.</p>
    </div>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nueva habitación
    </a>
</div>

{{-- Estadísticas --}}
<div class="row g-3 mb-4">
    @php
    $statItems = [
        ['label'=>'Total',        'value'=>$stats['total'],       'icon'=>'bi-door-open',       'color'=>'primary'],
        ['label'=>'Disponibles',  'value'=>$stats['available'],   'icon'=>'bi-check-circle',    'color'=>'success'],
        ['label'=>'Ocupadas',     'value'=>$stats['occupied'],    'icon'=>'bi-x-circle',        'color'=>'danger'],
        ['label'=>'Mantenimiento','value'=>$stats['maintenance'], 'icon'=>'bi-tools',           'color'=>'warning'],
    ];
    @endphp
    @foreach($statItems as $s)
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $s['color'] }} bg-opacity-10 text-{{ $s['color'] }}">
                    <i class="bi {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $s['value'] }}</div>
                    <div class="text-muted small">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filtros --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('rooms.index') }}" class="row g-2 align-items-end">
            @if(session('user_role') === 'admin')
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold mb-1">Hotel</label>
                <select name="hotel_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected':'' }}>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Tipo</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach(['single'=>'Sencilla','double'=>'Doble','suite'=>'Suite','family'=>'Familiar','deluxe'=>'Deluxe'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="available"   {{ request('status')==='available'?'selected':'' }}>Disponible</option>
                    <option value="occupied"    {{ request('status')==='occupied'?'selected':'' }}>Ocupada</option>
                    <option value="maintenance" {{ request('status')==='maintenance'?'selected':'' }}>Mantenimiento</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Precio máx.</label>
                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Ej: 200000" value="{{ request('max_price') }}">
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label small fw-semibold mb-1">Personas</label>
                <input type="number" name="capacity" min="1" class="form-control form-control-sm" placeholder="1" value="{{ request('capacity') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($rooms->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <p>No se encontraron habitaciones.</p>
                <a href="{{ route('rooms.index') }}" class="btn btn-link btn-sm">Limpiar filtros</a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hotel</th>
                        <th>Habitación</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Precio/noche</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rooms as $room)
                    <tr>
                        <td class="text-muted small">{{ $room->id }}</td>
                        <td class="small">{{ $room->hotel->name ?? '—' }}</td>
                        <td class="fw-semibold">Hab. {{ $room->number }}</td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">{{ $room->type_label }}</span></td>
                        <td class="small"><i class="bi bi-person me-1"></i>{{ $room->capacity }}</td>
                        <td class="small fw-semibold">${{ number_format($room->price_per_night, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $room->status_badge }} bg-opacity-10 text-{{ $room->status_badge }} border border-{{ $room->status_badge }} border-opacity-25">
                                {{ $room->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                {{-- Cambio rápido de estado (RF6) --}}
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="Cambiar estado">
                                        <i class="bi bi-toggles"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach(['available'=>'Disponible','occupied'=>'Ocupada','maintenance'=>'Mantenimiento'] as $val=>$lbl)
                                        <li>
                                            <form action="{{ route('rooms.status', $room) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $val }}">
                                                <button class="dropdown-item {{ $room->status === $val ? 'active' : '' }}">{{ $lbl }}</button>
                                            </form>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('rooms.destroy', $room) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar la habitación {{ $room->number }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($rooms->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted small">Mostrando {{ $rooms->firstItem() }}–{{ $rooms->lastItem() }} de {{ $rooms->total() }}</div>
            {{ $rooms->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="px-3 py-2 border-top text-muted small">{{ $rooms->total() }} habitaciones</div>
        @endif
        @endif
    </div>
</div>
@endsection
