<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Reservation::class);

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $query = Reservation::with(['room.hotel', 'client']);

        // Filtro por rol
        match ($user->role) {
            'hotel'  => $query->whereHas('room', fn($q) => $q->where('hotel_id', $user->id)),
            default  => null,
        };

        // Filtros opcionales
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id') && $user->isAdmin()) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('hotel_id') && $user->isAdmin()) {
            $query->whereHas('room', fn($q) => $q->where('hotel_id', $request->hotel_id));
        }
        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        $reservations = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $hotels  = User::where('role', 'hotel')->where('status', 'active')->get();
        $stats   = $this->buildStats($user);

        return view('reservations.index', compact('reservations', 'clients', 'hotels', 'stats'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Reservation::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $selectedRoom = $request->filled('room_id')
            ? Room::with('hotel')->find($request->room_id)
            : null;

        // Hotel solo ve sus propias habitaciones; admin y cliente ven todas las disponibles
        $roomsQuery = Room::with('hotel')->available();

        if ($user->isHotel()) {
            $roomsQuery->forHotel($user->id);
        }

        $rooms   = $roomsQuery->get();
        $clients = User::where('role', 'client')->where('status', 'active')->get();

        return view(
            'reservations.create',
            array_merge(
                compact('rooms', 'clients', 'selectedRoom'),
                [
                    'checkIn'  => $request->check_in  ?? '',
                    'checkOut' => $request->check_out ?? '',
                ]
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Reservation::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // El cliente siempre reserva para sí mismo
        if ($user->isClient()) {
            $request->merge(['client_id' => $user->id]);
        }

        $request->validate([
            'room_id'   => 'required|exists:rooms,id',
            'client_id' => 'required|exists:users,id',
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests'    => 'required|integer|min:1',
            'notes'     => 'nullable|string|max:500',
        ], $this->reservationMessages());

        // Seguridad extra: el hotel solo puede reservar habitaciones que le pertenecen
        if ($user->isHotel()) {
            $room = Room::findOrFail($request->room_id);
            if ($room->hotel_id !== $user->id) {
                abort(403, 'No puedes reservar habitaciones de otro hotel.');
            }
        }

        $client = User::findOrFail($request->client_id);

        $this->service->create($request->only(
            'room_id', 'check_in', 'check_out', 'guests', 'notes'
        ), $client);

        return redirect()->route('reservations.index')
            ->with('success', 'Reserva creada exitosamente. Pendiente de confirmación.');
    }

    public function show(Reservation $reservation): View
    {
        $this->authorize('view', $reservation);

        $reservation->load(['room.hotel', 'client']);

        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation): View
    {
        $this->authorize('update', $reservation);

        $rooms   = Room::with('hotel')->get();
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $reservation->load(['room.hotel', 'client']);

        return view('reservations.edit', compact('reservation', 'rooms', 'clients'));
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $request->validate([
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests'    => 'required|integer|min:1',
            'status'    => 'required|in:pending,confirmed,cancelled,completed',
            'notes'     => 'nullable|string|max:500',
        ], $this->reservationMessages());

        $this->service->update($reservation, $request->only(
            'check_in', 'check_out', 'guests', 'status', 'notes'
        ));

        return redirect()->route('reservations.index')
            ->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $this->authorize('delete', $reservation);

        $this->service->cancel($reservation);

        return redirect()->route('reservations.index')
            ->with('success', 'Reserva cancelada correctamente.');
    }

    public function availability(Request $request): View
    {
        $rooms  = collect();
        $hotels = User::where('role', 'hotel')->where('status', 'active')->get();

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

            if ($request->filled('hotel_id'))  $query->where('hotel_id', $request->hotel_id);
            if ($request->filled('capacity'))  $query->where('capacity', '>=', $request->capacity);
            if ($request->filled('type'))      $query->where('type', $request->type);
            if ($request->filled('max_price')) $query->where('price_per_night', '<=', $request->max_price);

            $rooms = $query->get()->filter(
                fn(Room $room) => $room->isAvailableForDates($checkIn, $checkOut)
            );
        }

        return view('reservations.availability', compact('rooms', 'hotels', 'checkIn', 'checkOut'));
    }

    /* ── Helpers privados ───────────────────────── */

    private function buildStats(User $user): array
    {
        $base = Reservation::query();

        match ($user->role) {
            'client' => $base->where('client_id', $user->id),
            'hotel'  => $base->whereHas('room', fn($q) => $q->where('hotel_id', $user->id)),
            default  => null,
        };

        return [
            'total'     => (clone $base)->count(),
            'pending'   => (clone $base)->where('status', 'pending')->count(),
            'confirmed' => (clone $base)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
        ];
    }

    private function reservationMessages(): array
    {
        return [
            'room_id.required'        => 'Debes seleccionar una habitación.',
            'client_id.required'      => 'Debes seleccionar un cliente.',
            'check_in.required'       => 'La fecha de entrada es obligatoria.',
            'check_in.after_or_equal' => 'La fecha de entrada no puede ser anterior a hoy.',
            'check_out.required'      => 'La fecha de salida es obligatoria.',
            'check_out.after'         => 'La fecha de salida debe ser posterior a la de entrada.',
            'guests.required'         => 'El número de huéspedes es obligatorio.',
            'status.required'         => 'El estado es obligatorio.',
        ];
    }
}
