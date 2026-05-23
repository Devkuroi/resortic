@extends('layouts.app')
@section('title', 'Detalle de Reserva')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h4><i class="bi bi-calendar-event me-2"></i>Reserva #{{ $reservation->id }}</h4>
        <p>Detalle completo de la reserva.</p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array(session('user_role'), ['admin','hotel']) || $reservation->status === 'pending')
        <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        @endif
        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Info principal --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-info-circle me-2 text-primary"></i>Información de la reserva
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Estado</div>
                        <span class="badge bg-{{ $reservation->status_badge }} bg-opacity-10 text-{{ $reservation->status_badge }} border border-{{ $reservation->status_badge }} border-opacity-25 fs-6 px-3 py-2">
                            {{ $reservation->status_label }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Fecha de creación</div>
                        <div>{{ $reservation->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Check-in</div>
                        <div class="fw-semibold"><i class="bi bi-box-arrow-in-right text-success me-1"></i>{{ $reservation->check_in->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Check-out</div>
                        <div class="fw-semibold"><i class="bi bi-box-arrow-right text-danger me-1"></i>{{ $reservation->check_out->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Noches</div>
                        <div class="fw-semibold">{{ $reservation->nights }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Huéspedes</div>
                        <div class="fw-semibold"><i class="bi bi-people me-1"></i>{{ $reservation->guests }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Total pagado</div>
                        <div class="fw-bold text-primary fs-5">${{ number_format($reservation->total_price, 0, ',', '.') }}</div>
                    </div>
                    @if($reservation->notes)
                    <div class="col-12">
                        <div class="small text-muted mb-1">Notas</div>
                        <div class="bg-light rounded p-3 small">{{ $reservation->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Info habitación y cliente --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-door-open me-2 text-primary"></i>Habitación
            </div>
            <div class="card-body">
                <div class="small text-muted mb-1">Hotel</div>
                <div class="fw-semibold mb-2">{{ $reservation->room->hotel->name ?? '—' }}</div>
                <div class="small text-muted mb-1">Número</div>
                <div class="fw-semibold mb-2">Hab. {{ $reservation->room->number }}</div>
                <div class="small text-muted mb-1">Tipo</div>
                <div class="mb-2">{{ $reservation->room->type_label }}</div>
                <div class="small text-muted mb-1">Precio por noche</div>
                <div>${{ number_format($reservation->room->price_per_night, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-person me-2 text-primary"></i>Cliente
            </div>
            <div class="card-body">
                <div class="small text-muted mb-1">Nombre</div>
                <div class="fw-semibold mb-2">{{ $reservation->client->name ?? '—' }}</div>
                <div class="small text-muted mb-1">Correo</div>
                <div class="small">{{ $reservation->client->email ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
