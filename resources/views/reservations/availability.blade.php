@extends('layouts.app')
@section('title', 'Consultar Disponibilidad')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-search me-2"></i>Consultar Disponibilidad</h4>
    <p>Busca habitaciones disponibles por fechas, hotel y preferencias (RF7).</p>
</div>

{{-- Formulario de búsqueda --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('reservations.availability') }}" novalidate>
            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold small">Entrada <span class="text-danger">*</span></label>
                    <input type="date" name="check_in" class="form-control"
                           value="{{ $checkIn }}" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold small">Salida <span class="text-danger">*</span></label>
                    <input type="date" name="check_out" class="form-control"
                           value="{{ $checkOut }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label fw-semibold small">Personas</label>
                    <input type="number" name="capacity" min="1" class="form-control"
                           value="{{ request('capacity') }}" placeholder="1">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold small">Tipo</label>
                    <select name="type" class="form-select">
                        <option value="">Cualquiera</option>
                        @foreach(['single'=>'Sencilla','double'=>'Doble','suite'=>'Suite','family'=>'Familiar','deluxe'=>'Deluxe'] as $v=>$l)
                            <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold small">Hotel</label>
                    <select name="hotel_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($hotels as $h)
                            <option value="{{ $h->id }}" {{ request('hotel_id')==$h->id?'selected':'' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label fw-semibold small">Precio máx.</label>
                    <input type="number" name="max_price" class="form-control"
                           value="{{ request('max_price') }}" placeholder="$">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-search me-1"></i>Buscar disponibilidad
                    </button>
                    <a href="{{ route('reservations.availability') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Resultados --}}
@if($checkIn && $checkOut)
    @if($rooms->isEmpty())
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle fs-5"></i>
            <span>No hay habitaciones disponibles para las fechas seleccionadas con los filtros aplicados.</span>
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-muted">
                <i class="bi bi-check-circle text-success me-1"></i>
                {{ $rooms->count() }} habitación(es) disponible(s) del
                {{ \Carbon\Carbon::parse($checkIn)->format('d/m/Y') }} al
                {{ \Carbon\Carbon::parse($checkOut)->format('d/m/Y') }}
                ({{ \Carbon\Carbon::parse($checkIn)->diffInDays($checkOut) }} noches)
            </h6>
        </div>

        <div class="row g-3">
        @foreach($rooms as $room)
            @php
                $nights     = \Carbon\Carbon::parse($checkIn)->diffInDays($checkOut);
                $totalPrice = $nights * $room->price_per_night;
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold">Hab. {{ $room->number }}</h6>
                                <div class="text-muted small">{{ $room->hotel->name ?? '—' }}</div>
                            </div>
                            <span class="badge bg-success">Disponible</span>
                        </div>

                        <div class="mb-2">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-1">
                                {{ $room->type_label }}
                            </span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                <i class="bi bi-person me-1"></i>Hasta {{ $room->capacity }} personas
                            </span>
                        </div>

                        @if($room->description)
                        <p class="small text-muted mb-2">{{ Str::limit($room->description, 80) }}</p>
                        @endif

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted">Precio total ({{ $nights }} noches)</div>
                                <div class="fw-bold text-primary fs-5">${{ number_format($totalPrice, 0, ',', '.') }}</div>
                                <div class="small text-muted">${{ number_format($room->price_per_night, 0, ',', '.') }} / noche</div>
                            </div>
                            <a href="{{ route('reservations.create', ['room_id'=>$room->id, 'check_in'=>$checkIn, 'check_out'=>$checkOut]) }}"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-calendar-plus me-1"></i>Reservar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    @endif
@else
    <div class="text-center text-muted py-5">
        <i class="bi bi-calendar-range fs-1 d-block mb-3"></i>
        <p>Ingresa las fechas de entrada y salida para ver la disponibilidad.</p>
    </div>
@endif

@endsection

@push('scripts')
<script>
    // Asegurar que la fecha de salida sea siempre posterior a la de entrada
    document.querySelector('[name="check_in"]').addEventListener('change', function() {
        const checkOut = document.querySelector('[name="check_out"]');
        if (checkOut.value && checkOut.value <= this.value) {
            const next = new Date(this.value);
            next.setDate(next.getDate() + 1);
            checkOut.value = next.toISOString().split('T')[0];
        }
        checkOut.min = new Date(new Date(this.value).getTime() + 86400000).toISOString().split('T')[0];
    });
</script>
@endpush
