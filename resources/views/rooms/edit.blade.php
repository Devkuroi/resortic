@extends('layouts.app')
@section('title', 'Editar Habitación')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-pencil-square me-2"></i>Editar Habitación</h4>
    <p>Modifica los datos de la habitación <strong>{{ $room->number }}</strong> — {{ $room->hotel->name ?? '' }} (RF5).</p>
</div>

<div class="row justify-content-center">
<div class="col-12 col-lg-7">
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

<form action="{{ route('rooms.update', $room) }}" method="POST" novalidate>
@csrf @method('PUT')

{{-- Info del hotel (solo lectura) --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">Hotel</label>
    <input type="text" class="form-control bg-light" value="{{ $room->hotel->name ?? '—' }}" disabled>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold small">N° Habitación <span class="text-danger">*</span></label>
        <input type="text" name="number" class="form-control @error('number') is-invalid @enderror"
               value="{{ old('number', $room->number) }}">
        @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror">
            @foreach(['single'=>'Sencilla','double'=>'Doble','suite'=>'Suite','family'=>'Familiar','deluxe'=>'Deluxe'] as $v=>$l)
                <option value="{{ $v }}" {{ old('type',$room->type)===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Capacidad <span class="text-danger">*</span></label>
        <input type="number" name="capacity" min="1" max="20"
               class="form-control @error('capacity') is-invalid @enderror"
               value="{{ old('capacity', $room->capacity) }}">
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Precio por noche ($) <span class="text-danger">*</span></label>
        <input type="number" name="price_per_night" min="1" step="1000"
               class="form-control @error('price_per_night') is-invalid @enderror"
               value="{{ old('price_per_night', $room->price_per_night) }}">
        @error('price_per_night')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Estado <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="available"   {{ old('status',$room->status)==='available'?'selected':'' }}>Disponible</option>
            <option value="occupied"    {{ old('status',$room->status)==='occupied'?'selected':'' }}>Ocupada</option>
            <option value="maintenance" {{ old('status',$room->status)==='maintenance'?'selected':'' }}>Mantenimiento</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-semibold small">Descripción</label>
    <textarea name="description" rows="3" class="form-control">{{ old('description', $room->description) }}</textarea>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancelar</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar cambios</button>
</div>
</form>

</div>
</div>
</div>
</div>
@endsection
