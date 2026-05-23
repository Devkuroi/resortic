<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private function checkSession(): void
    {
        if (!session()->has('user_id')) {
            abort(redirect()->route('login'));
        }
    }

    // RF4, RF7: Listar habitaciones con filtros y disponibilidad
    public function index(Request $request)
    {
        $this->checkSession();

        $role    = session('user_role');
        $userId  = session('user_id');

        $query = Room::with('hotel');

        // Hotel solo ve sus propias habitaciones
        if ($role === 'hotel') {
            $query->where('hotel_id', $userId);
        }

        // Filtros
        if ($request->filled('hotel_id') && $role === 'admin') {
            $query->where('hotel_id', $request->hotel_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }
        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        $rooms  = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $hotels = User::where('role', 'hotel')->where('status', 'active')->get();

        $stats = [
            'total'       => ($role === 'hotel') ? Room::where('hotel_id', $userId)->count() : Room::count(),
            'available'   => ($role === 'hotel') ? Room::where('hotel_id', $userId)->where('status', 'available')->count()   : Room::where('status', 'available')->count(),
            'occupied'    => ($role === 'hotel') ? Room::where('hotel_id', $userId)->where('status', 'occupied')->count()    : Room::where('status', 'occupied')->count(),
            'maintenance' => ($role === 'hotel') ? Room::where('hotel_id', $userId)->where('status', 'maintenance')->count() : Room::where('status', 'maintenance')->count(),
        ];

        return view('rooms.index', compact('rooms', 'hotels', 'stats'));
    }

    // RF4: Formulario de registro de habitación
    public function create()
    {
        $this->checkSession();
        $role   = session('user_role');
        $hotels = ($role === 'admin') ? User::where('role', 'hotel')->where('status', 'active')->get() : collect();
        return view('rooms.create', compact('hotels'));
    }

    // RF4: Registrar habitación
    public function store(Request $request)
    {
        $this->checkSession();

        $hotelId = (session('user_role') === 'hotel')
            ? session('user_id')
            : $request->hotel_id;

        $request->merge(['hotel_id' => $hotelId]);

        $request->validate([
            'hotel_id'        => 'required|exists:users,id',
            'number'          => 'required|string|max:20',
            'type'            => 'required|in:single,double,suite,family,deluxe',
            'description'     => 'nullable|string|max:500',
            'price_per_night' => 'required|numeric|min:1',
            'capacity'        => 'required|integer|min:1|max:20',
            'status'          => 'required|in:available,occupied,maintenance',
        ], $this->messages());

        // Verificar número único por hotel
        $exists = Room::where('hotel_id', $hotelId)->where('number', $request->number)->exists();
        if ($exists) {
            return back()->withErrors(['number' => 'Ya existe una habitación con ese número en este hotel.'])->withInput();
        }

        Room::create([
            'hotel_id'        => $hotelId,
            'number'          => $request->number,
            'type'            => $request->type,
            'description'     => $request->description,
            'price_per_night' => $request->price_per_night,
            'capacity'        => $request->capacity,
            'status'          => $request->status,
        ]);

        return redirect()->route('rooms.index')->with('success', 'Habitación registrada correctamente.');
    }

    // RF5: Formulario de edición
    public function edit(Room $room)
    {
        $this->checkSession();
        $this->authorizeRoom($room);
        $hotels = User::where('role', 'hotel')->where('status', 'active')->get();
        return view('rooms.edit', compact('room', 'hotels'));
    }

    // RF5: Actualizar habitación
    public function update(Request $request, Room $room)
    {
        $this->checkSession();
        $this->authorizeRoom($room);

        $request->validate([
            'number'          => 'required|string|max:20',
            'type'            => 'required|in:single,double,suite,family,deluxe',
            'description'     => 'nullable|string|max:500',
            'price_per_night' => 'required|numeric|min:1',
            'capacity'        => 'required|integer|min:1|max:20',
            'status'          => 'required|in:available,occupied,maintenance',
        ], $this->messages());

        // Número único por hotel (excluyendo la actual)
        $exists = Room::where('hotel_id', $room->hotel_id)
            ->where('number', $request->number)
            ->where('id', '!=', $room->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['number' => 'Ya existe otra habitación con ese número en este hotel.'])->withInput();
        }

        $room->update([
            'number'          => $request->number,
            'type'            => $request->type,
            'description'     => $request->description,
            'price_per_night' => $request->price_per_night,
            'capacity'        => $request->capacity,
            'status'          => $request->status,
        ]);

        return redirect()->route('rooms.index')->with('success', 'Habitación actualizada correctamente.');
    }

    // RF6: Cambiar estado rápido (AJAX-friendly)
    public function updateStatus(Request $request, Room $room)
    {
        $this->checkSession();
        $this->authorizeRoom($room);

        $request->validate(['status' => 'required|in:available,occupied,maintenance']);
        $room->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Estado de la habitación actualizado.');
    }

    // Eliminar habitación
    public function destroy(Room $room)
    {
        $this->checkSession();
        $this->authorizeRoom($room);

        if ($room->reservations()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return redirect()->route('rooms.index')
                ->with('error', 'No se puede eliminar: la habitación tiene reservas activas.');
        }

        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Habitación eliminada correctamente.');
    }

    // Protege que un hotel no edite habitaciones ajenas
    private function authorizeRoom(Room $room): void
    {
        if (session('user_role') === 'hotel' && $room->hotel_id !== session('user_id')) {
            abort(403, 'No tienes permiso para gestionar esta habitación.');
        }
    }

    private function messages(): array
    {
        return [
            'hotel_id.required'        => 'Debes seleccionar un hotel.',
            'hotel_id.exists'          => 'El hotel seleccionado no existe.',
            'number.required'          => 'El número de habitación es obligatorio.',
            'type.required'            => 'El tipo es obligatorio.',
            'price_per_night.required' => 'El precio por noche es obligatorio.',
            'price_per_night.numeric'  => 'El precio debe ser un número.',
            'price_per_night.min'      => 'El precio mínimo es 1.',
            'capacity.required'        => 'La capacidad es obligatoria.',
            'capacity.integer'         => 'La capacidad debe ser un número entero.',
            'status.required'          => 'El estado es obligatorio.',
        ];
    }
}
