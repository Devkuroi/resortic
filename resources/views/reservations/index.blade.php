@extends('layouts.app')
@section('title', 'Reservas')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4><i class="bi bi-calendar-check me-2"></i>Reservas</h4>
        <p>Gestiona las reservas del sistema (RF8).</p>
    </div>
    @if(in_array(session('user_role'), ['admin','client']))
    <a href="{{ route('reservations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nueva reserva
    </a>
    @endif
</div>

{{-- Estadísticas --}}
<div class="row g-3 mb-4">
    @php
    $statItems = [
        ['label'=>'Total',      'value'=>$stats['total'],     'icon'=>'bi-calendar',       'color'=>'primary'],
        ['label'=>'Pendientes', 'value'=>$stats['pending'],   'icon'=>'bi-clock',          'color'=>'warning'],
        ['label'=>'Confirmadas','value'=>$stats['confirmed'], 'icon'=>'bi-check-circle',   'color'=>'success'],
        ['label'=>'Canceladas', 'value'=>$stats['cancelled'], 'icon'=>'bi-x-circle',       'color'=>'secondary'],
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
        <form method="GET" action="{{ route('reservations.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pending"   {{ request('status')==='pending'?'selected':'' }}>Pendiente</option>
                    <option value="confirmed" {{ request('status')==='confirmed'?'selected':'' }}>Confirmada</option>
                    <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Cancelada</option>
                    <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Completada</option>
                </select>
            </div>
            @if(session('user_role') === 'admin')
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Hotel</label>
                <select name="hotel_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id')==$h->id?'selected':'' }}>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Cliente</label>
                <select name="client_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($reservations->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <p>No se encontraron reservas.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Hotel / Hab.</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Noches</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($reservations as $res)
                    <tr>
                        <td class="text-muted small">{{ $res->id }}</td>
                        <td class="small">{{ $res->client->name ?? '—' }}</td>
                        <td class="small">
                            <div>{{ $res->room->hotel->name ?? '—' }}</div>
                            <div class="text-muted">Hab. {{ $res->room->number ?? '—' }}</div>
                        </td>
                        <td class="small">{{ $res->check_in->format('d/m/Y') }}</td>
                        <td class="small">{{ $res->check_out->format('d/m/Y') }}</td>
                        <td class="small text-center">{{ $res->nights }}</td>
                        <td class="small fw-semibold">${{ number_format($res->total_price, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $res->status_badge }} bg-opacity-10 text-{{ $res->status_badge }} border border-{{ $res->status_badge }} border-opacity-25">
                                {{ $res->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('reservations.show', $res) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(in_array(session('user_role'), ['admin','hotel']) || ($res->status === 'pending'))
                                <a href="{{ route('reservations.edit', $res) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                                @if(!in_array($res->status, ['cancelled','completed']))
                                <form action="{{ route('reservations.destroy', $res) }}" method="POST"
                                      onsubmit="return confirm('¿Cancelar la reserva #{{ $res->id }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Cancelar"><i class="bi bi-x-circle"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($reservations->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted small">Mostrando {{ $reservations->firstItem() }}–{{ $reservations->lastItem() }} de {{ $reservations->total() }}</div>
            {{ $reservations->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="px-3 py-2 border-top text-muted small">{{ $reservations->total() }} reservas</div>
        @endif
        @endif
    </div>
</div>
@endsection
