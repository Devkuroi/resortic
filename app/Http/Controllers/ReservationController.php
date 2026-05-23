<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    private function checkSession(): void
    {
        if (!session()->has('user_id')) {
            abort(redirect()->route('login'));
        }
    }

    // RF8: Listar reservas según rol
    public function index(Request $request)
    {
        $this->checkSession();

        $role   = session('user_role');
        $userId = session('user_id');

        $query = Reservation::with(['room.hotel', 'client']);

        if ($role === 'client') {
            // El cliente solo ve sus propias reservas
            $query->where('client_id', $userId);
        } elseif ($role === 'hotel') {
            // El hotel ve reservas de sus habitaciones
            $query->whereHas('room', fn($q) => $q->where('hotel_id', $userId));
        }
        // admin ve todo

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id') && $role === 'admin') {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('hotel_id') && in_array($role, ['admin'])) {
            $query->whereHas('room', fn($q) => $q->where('hotel_id', $request->hotel_id));
        }
        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $hotels  = User::where('role', 'hotel')->where('status', 'active')->get();

        // Estadísticas contextuales
        $baseQuery = Reservation::query();
        if ($role === 'client') {
            $baseQuery->where('client_id', $userId);
        } elseif ($role === 'hotel') {
            $baseQuery->whereHas('room', fn($q) => $q->where('hotel_id', $userId));
        }

        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'pending'   => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        return view('reservations.index', compact('reservations', 'clients', 'hotels', 'stats'));
    }

    // RF8: Formulario para nueva reserva
    public function create(Request $request)
    {
        $this->checkSession();

        // Si viene con room_id pre-seleccionado (desde disponibilidad)
        $selectedRoom = $request->filled('room_id') ? Room::with('hotel')->find($request->room_id) : null;
        $checkIn      = $request->check_in  ?? '';
        $checkOut     = $request->check_out ?? '';

        $rooms   = Room::with('hotel')->where('status', 'available')->get();
        $clients = User::where('role', 'client')->where('status', 'active')->get();

        return view('reservations.create', compact('rooms', 'clients', 'selectedRoom', 'checkIn', 'checkOut'));
    }

    // RF8: Guardar reserva
    public function store(Request $request)
    {
        $this->checkSession();

        $role    = session('user_role');
        $userId  = session('user_id');

        // Si es cliente, el client_id siempre es él mismo
        if ($role === 'client') {
            $request->merge(['client_id' => $userId]);
        }

        $request->validate([
            'room_id'   => 'required|exists:rooms,id',
            'client_id' => 'required|exists:users,id',
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests'    => 'required|integer|min:1',
            'notes'     => 'nullable|string|max:500',
        ], [
            'room_id.required'   => 'Debes seleccionar una habitación.',
            'client_id.required' => 'Debes seleccionar un cliente.',
            'check_in.required'  => 'La fecha de entrada es obligatoria.',
            'check_in.after_or_equal' => 'La fecha de entrada no puede ser anterior a hoy.',
            'check_out.required' => 'La fecha de salida es obligatoria.',
            'check_out.after'    => 'La fecha de salida debe ser posterior a la de entrada.',
            'guests.required'    => 'El número de huéspedes es obligatorio.',
        ]);

        $room = Room::findOrFail($request->room_id);

        // Verificar capacidad
        if ($request->guests > $room->capacity) {
            return back()->withErrors(['guests' => "La habitación tiene capacidad máxima de {$room->capacity} personas."])->withInput();
        }

        // RF7: Verificar disponibilidad en fechas solicitadas
        if (!$room->isAvailableForDates($request->check_in, $request->check_out)) {
            return back()->withErrors(['check_in' => 'La habitación no está disponible en las fechas seleccionadas.'])->withInput();
        }

        // Calcular precio total
        $nights     = \Carbon\Carbon::parse($request->check_in)->diffInDays($request->check_out);
        $totalPrice = $nights * $room->price_per_night;

        Reservation::create([
            'room_id'     => $request->room_id,
            'client_id'   => $request->client_id,
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'guests'      => $request->guests,
            'total_price' => $totalPrice,
            'status'      => 'pending',
            'notes'       => $request->notes,
        ]);

        // Si el cliente reserva, actualizar habitación como ocupada
        // (solo si el hotel/admin confirma se marcaría occupied; aquí se deja en pending)

        return redirect()->route('reservations.index')->with('success', 'Reserva creada exitosamente. Pendiente de confirmación.');
    }

    // Ver detalle de reserva
    public function show(Reservation $reservation)
    {
        $this->checkSession();
        $this->authorizeReservation($reservation);
        $reservation->load(['room.hotel', 'client']);
        return view('reservations.show', compact('reservation'));
    }

    // Formulario de edición (solo admin y hotel)
    public function edit(Reservation $reservation)
    {
        $this->checkSession();
        $this->authorizeReservation($reservation);

        $rooms   = Room::with('hotel')->get();
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $reservation->load(['room.hotel', 'client']);

        return view('reservations.edit', compact('reservation', 'rooms', 'clients'));
    }

    // Actualizar reserva
    public function update(Request $request, Reservation $reservation)
    {
        $this->checkSession();
        $this->authorizeReservation($reservation);

        $request->validate([
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests'    => 'required|integer|min:1',
            'status'    => 'required|in:pending,confirmed,cancelled,completed',
            'notes'     => 'nullable|string|max:500',
        ], [
            'check_in.required'  => 'La fecha de entrada es obligatoria.',
            'check_out.required' => 'La fecha de salida es obligatoria.',
            'check_out.after'    => 'La salida debe ser posterior a la entrada.',
            'guests.required'    => 'El número de huéspedes es obligatorio.',
            'status.required'    => 'El estado es obligatorio.',
        ]);

        // Verificar disponibilidad si cambiaron las fechas
        if ($reservation->check_in->format('Y-m-d') !== $request->check_in ||
            $reservation->check_out->format('Y-m-d') !== $request->check_out) {
            if (!$reservation->room->isAvailableForDates($request->check_in, $request->check_out, $reservation->id)) {
                return back()->withErrors(['check_in' => 'La habitación no está disponible en las nuevas fechas.'])->withInput();
            }
        }

        $nights     = \Carbon\Carbon::parse($request->check_in)->diffInDays($request->check_out);
        $totalPrice = $nights * $reservation->room->price_per_night;

        $reservation->update([
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'guests'      => $request->guests,
            'total_price' => $totalPrice,
            'status'      => $request->status,
            'notes'       => $request->notes,
        ]);

        // Sincronizar estado de habitación
        if ($request->status === 'confirmed') {
            $reservation->room->update(['status' => 'occupied']);
        } elseif (in_array($request->status, ['cancelled', 'completed'])) {
            $reservation->room->update(['status' => 'available']);
        }

        return redirect()->route('reservations.index')->with('success', 'Reserva actualizada correctamente.');
    }

    // Cancelar reserva
    public function destroy(Reservation $reservation)
    {
        $this->checkSession();
        $this->authorizeReservation($reservation);

        if (in_array($reservation->status, ['completed'])) {
            return redirect()->route('reservations.index')
                ->with('error', 'No se puede cancelar una reserva ya completada.');
        }

        $reservation->update(['status' => 'cancelled']);
        $reservation->room->update(['status' => 'available']);

        return redirect()->route('reservations.index')->with('success', 'Reserva cancelada correctamente.');
    }

    // RF7: Consultar disponibilidad pública
    public function availability(Request $request)
    {
        $this->checkSession();

        $rooms   = collect();
        $hotels  = User::where('role', 'hotel')->where('status', 'active')->get();
        $checkIn  = $request->check_in  ?? '';
        $checkOut = $request->check_out ?? '';

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $request->validate([
                'check_in'  => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
            ], [
                'check_in.after_or_equal' => 'La fecha de entrada no puede ser en el pasado.',
                'check_out.after'         => 'La salida debe ser posterior a la entrada.',
            ]);

            $query = Room::with('hotel')->where('status', '!=', 'maintenance');

            if ($request->filled('hotel_id')) {
                $query->where('hotel_id', $request->hotel_id);
            }
            if ($request->filled('capacity')) {
                $query->where('capacity', '>=', $request->capacity);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('max_price')) {
                $query->where('price_per_night', '<=', $request->max_price);
            }

            $allRooms = $query->get();

            // Filtrar por disponibilidad real en las fechas
            $rooms = $allRooms->filter(
                fn($room) => $room->isAvailableForDates($request->check_in, $request->check_out)
            );
        }

        return view('reservations.availability', compact('rooms', 'hotels', 'checkIn', 'checkOut'));
    }

    private function authorizeReservation(Reservation $reservation): void
    {
        $role   = session('user_role');
        $userId = session('user_id');

        if ($role === 'client' && $reservation->client_id !== $userId) {
            abort(403, 'No tienes permiso para ver esta reserva.');
        }

        if ($role === 'hotel') {
            $reservation->load('room');
            if ($reservation->room->hotel_id !== $userId) {
                abort(403, 'No tienes permiso para gestionar esta reserva.');
            }
        }
    }
}
