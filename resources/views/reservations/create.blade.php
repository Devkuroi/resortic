@extends('layouts.app')
@section('title', 'Nueva Reserva')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-calendar-plus me-2"></i>Nueva Reserva</h4>
    <p>Realiza una reserva de habitación (RF8).</p>
</div>

<div class="row justify-content-center">
<div class="col-12 col-lg-8">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

@if($errors->any())
<div class="alert alert-danger">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Corrige los errores:</strong>
    <ul class="mb-0 mt-1 ps-3">
        @foreach($errors->all() as $e)<li class="small">{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form action="{{ route('reservations.store') }}" method="POST" novalidate id="resForm">
@csrf

{{-- Selección de habitación --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Habitación <span class="text-danger">*</span></label>
    <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror"
            onchange="updatePrice()">
        <option value="">Selecciona una habitación...</option>
        @foreach($rooms as $r)
            <option value="{{ $r->id }}"
                    data-price="{{ $r->price_per_night }}"
                    data-capacity="{{ $r->capacity }}"
                    {{ old('room_id', $selectedRoom?->id) == $r->id ? 'selected' : '' }}>
                [{{ $r->hotel->name ?? '—' }}] Hab. {{ $r->number }} – {{ $r->type_label }} – ${{ number_format($r->price_per_night,0,',','.') }}/noche ({{ $r->capacity }} pers.)
            </option>
        @endforeach
    </select>
    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Cliente (solo admin y hotel lo seleccionan; cliente es fijo) --}}
@if(in_array(session('user_role'), ['admin','hotel']))
<div class="mb-3">
    <label class="form-label fw-semibold small">Cliente <span class="text-danger">*</span></label>
    <select name="client_id" class="form-select @error('client_id') is-invalid @enderror">
        <option value="">Selecciona un cliente...</option>
        @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected':'' }}>{{ $c->name }} ({{ $c->email }})</option>
        @endforeach
    </select>
    @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@else
<div class="mb-3">
    <label class="form-label fw-semibold small">Cliente</label>
    <input type="text" class="form-control bg-light" value="{{ session('user_name') }}" disabled>
    <input type="hidden" name="client_id" value="{{ session('user_id') }}">
</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Fecha de entrada <span class="text-danger">*</span></label>
        <input type="date" name="check_in" id="check_in"
               class="form-control @error('check_in') is-invalid @enderror"
               value="{{ old('check_in', $checkIn) }}"
               min="{{ date('Y-m-d') }}"
               onchange="updatePrice()">
        @error('check_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Fecha de salida <span class="text-danger">*</span></label>
        <input type="date" name="check_out" id="check_out"
               class="form-control @error('check_out') is-invalid @enderror"
               value="{{ old('check_out', $checkOut) }}"
               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
               onchange="updatePrice()">
        @error('check_out')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Número de huéspedes <span class="text-danger">*</span></label>
        <input type="number" name="guests" id="guests" min="1"
               class="form-control @error('guests') is-invalid @enderror"
               value="{{ old('guests', 1) }}">
        @error('guests')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold small">Notas adicionales</label>
    <textarea name="notes" rows="2" class="form-control" placeholder="Peticiones especiales, alergias, hora de llegada...">{{ old('notes') }}</textarea>
</div>

{{-- Resumen de precio --}}
<div class="card bg-light border-0 mb-4" id="price-summary" style="{{ ($checkIn && $checkOut) ? '' : 'display:none' }}">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Resumen del costo</span>
        </div>
        <hr class="my-2">
        <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Precio por noche</span>
            <span id="price-night">—</span>
        </div>
        <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Número de noches</span>
            <span id="price-nights">—</span>
        </div>
        <div class="d-flex justify-content-between fw-bold text-primary mt-2">
            <span>Total estimado</span>
            <span id="price-total">—</span>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('reservations.availability') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-calendar-check me-1"></i>Confirmar reserva
    </button>
</div>
</form>

</div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function updatePrice() {
    const select  = document.getElementById('room_id');
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    const summary = document.getElementById('price-summary');

    if (!select.value || !checkIn || !checkOut) { summary.style.display = 'none'; return; }

    const opt      = select.options[select.selectedIndex];
    const price    = parseFloat(opt.dataset.price);
    const d1       = new Date(checkIn);
    const d2       = new Date(checkOut);
    const nights   = Math.round((d2 - d1) / 86400000);

    if (nights <= 0) { summary.style.display = 'none'; return; }

    const total = price * nights;
    document.getElementById('price-night').textContent  = '$' + price.toLocaleString('es-CO');
    document.getElementById('price-nights').textContent = nights;
    document.getElementById('price-total').textContent  = '$' + total.toLocaleString('es-CO');
    summary.style.display = 'block';
}

// Validar fecha de salida siempre posterior a entrada
document.getElementById('check_in').addEventListener('change', function() {
    const co = document.getElementById('check_out');
    const next = new Date(this.value);
    next.setDate(next.getDate() + 1);
    const nextStr = next.toISOString().split('T')[0];
    co.min = nextStr;
    if (co.value <= this.value) co.value = nextStr;
    updatePrice();
});

// Inicializar resumen si hay valores precargados
updatePrice();
</script>
@endpush
