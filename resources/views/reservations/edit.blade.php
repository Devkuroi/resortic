@extends('layouts.app')
@section('title', 'Editar Reserva')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil-square me-2"></i>Editar Reserva #{{ $reservation->id }}</h4>
    <p>Modifica los datos de la reserva.</p>
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

<form action="{{ route('reservations.update', $reservation) }}" method="POST" novalidate>
@csrf @method('PUT')

{{-- Info de habitación (solo lectura) --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Hotel</label>
        <input type="text" class="form-control bg-light" value="{{ $reservation->room->hotel->name ?? '—' }}" disabled>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Habitación</label>
        <input type="text" class="form-control bg-light"
               value="Hab. {{ $reservation->room->number }} – {{ $reservation->room->type_label }} – ${{ number_format($reservation->room->price_per_night,0,',','.') }}/noche"
               disabled>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Fecha de entrada <span class="text-danger">*</span></label>
        <input type="date" name="check_in" id="check_in"
               class="form-control @error('check_in') is-invalid @enderror"
               value="{{ old('check_in', $reservation->check_in->format('Y-m-d')) }}"
               onchange="updatePrice()">
        @error('check_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Fecha de salida <span class="text-danger">*</span></label>
        <input type="date" name="check_out" id="check_out"
               class="form-control @error('check_out') is-invalid @enderror"
               value="{{ old('check_out', $reservation->check_out->format('Y-m-d')) }}"
               onchange="updatePrice()">
        @error('check_out')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Huéspedes <span class="text-danger">*</span></label>
        <input type="number" name="guests" min="1"
               class="form-control @error('guests') is-invalid @enderror"
               value="{{ old('guests', $reservation->guests) }}">
        @error('guests')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@if(in_array(session('user_role'), ['admin','hotel']))
<div class="mb-3">
    <label class="form-label fw-semibold small">Estado <span class="text-danger">*</span></label>
    <select name="status" class="form-select @error('status') is-invalid @enderror">
        <option value="pending"   {{ old('status',$reservation->status)==='pending'?'selected':'' }}>Pendiente</option>
        <option value="confirmed" {{ old('status',$reservation->status)==='confirmed'?'selected':'' }}>Confirmada</option>
        <option value="cancelled" {{ old('status',$reservation->status)==='cancelled'?'selected':'' }}>Cancelada</option>
        <option value="completed" {{ old('status',$reservation->status)==='completed'?'selected':'' }}>Completada</option>
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@else
<input type="hidden" name="status" value="{{ $reservation->status }}">
@endif

<div class="mb-3">
    <label class="form-label fw-semibold small">Notas</label>
    <textarea name="notes" rows="2" class="form-control">{{ old('notes', $reservation->notes) }}</textarea>
</div>

{{-- Resumen de precio --}}
<div class="card bg-light border-0 mb-4" id="price-summary">
    <div class="card-body py-3">
        <div class="fw-semibold mb-2">Resumen del costo</div>
        <hr class="my-2">
        <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Precio por noche</span>
            <span>${{ number_format($reservation->room->price_per_night, 0, ',', '.') }}</span>
        </div>
        <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Número de noches</span>
            <span id="price-nights">{{ $reservation->nights }}</span>
        </div>
        <div class="d-flex justify-content-between fw-bold text-primary mt-2">
            <span>Total estimado</span>
            <span id="price-total">${{ number_format($reservation->total_price, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i>Guardar cambios
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
const pricePerNight = {{ $reservation->room->price_per_night }};

function updatePrice() {
    const ci = document.getElementById('check_in').value;
    const co = document.getElementById('check_out').value;
    if (!ci || !co) return;
    const nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
    if (nights <= 0) return;
    document.getElementById('price-nights').textContent = nights;
    document.getElementById('price-total').textContent  = '$' + (pricePerNight * nights).toLocaleString('es-CO');
}

document.getElementById('check_in').addEventListener('change', function() {
    const co = document.getElementById('check_out');
    const next = new Date(this.value);
    next.setDate(next.getDate() + 1);
    co.min = next.toISOString().split('T')[0];
    if (co.value <= this.value) co.value = next.toISOString().split('T')[0];
    updatePrice();
});
document.getElementById('check_out').addEventListener('change', updatePrice);
</script>
@endpush
