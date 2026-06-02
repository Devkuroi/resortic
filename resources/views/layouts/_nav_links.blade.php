{{-- Disponibilidad: todos la ven --}}
<div class="nav-section">Búsqueda</div>
<a href="{{ route('reservations.availability') }}"
   class="nav-link {{ request()->routeIs('reservations.availability') ? 'active' : '' }}"
   data-bs-dismiss="offcanvas">
    <i class="bi bi-search"></i> Disponibilidad
</a>

{{-- Reservas: todos los roles pueden consultar --}}
<div class="nav-section">Reservas</div>
<a href="{{ route('reservations.index') }}"
   class="nav-link {{ request()->routeIs('reservations.index','reservations.show') ? 'active' : '' }}"
   data-bs-dismiss="offcanvas">
    <i class="bi bi-calendar-check"></i> Reservas
</a>

{{-- Nueva reserva: solo admin y hotel --}}
@if(Auth::user()->isAdmin() || Auth::user()->isHotel())
<a href="{{ route('reservations.create') }}"
   class="nav-link {{ request()->routeIs('reservations.create','reservations.edit') ? 'active' : '' }}"
   data-bs-dismiss="offcanvas">
    <i class="bi bi-plus-circle"></i> Nueva reserva
</a>
@endif

{{-- Habitaciones: admin y hotel --}}
@if(Auth::user()->isAdmin() || Auth::user()->isHotel())
<div class="nav-section">Habitaciones</div>
<a href="{{ route('rooms.index') }}"
   class="nav-link {{ request()->routeIs('rooms.index','rooms.edit') ? 'active' : '' }}"
   data-bs-dismiss="offcanvas">
    <i class="bi bi-door-open"></i> Habitaciones
</a>
<a href="{{ route('rooms.create') }}"
   class="nav-link {{ request()->routeIs('rooms.create') ? 'active' : '' }}"
   data-bs-dismiss="offcanvas">
    <i class="bi bi-plus-circle"></i> Nueva habitación
</a>
@endif

{{-- Cuentas: solo admin --}}
@if(Auth::user()->isAdmin())
<div class="nav-section">Administración</div>
<a href="{{ route('accounts.index') }}"
   class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}"
   data-bs-dismiss="offcanvas">
    <i class="bi bi-people"></i> Cuentas
</a>
@endif
