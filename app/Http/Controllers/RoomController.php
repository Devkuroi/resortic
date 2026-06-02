<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Room::class);

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $query = Room::with('hotel');

        if ($user->isHotel()) {
            $query->forHotel($user->id);
        }

        if ($request->filled('hotel_id') && $user->isAdmin()) {
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

        $rooms  = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        $hotels = User::where('role', 'hotel')->where('status', 'active')->get();
        $stats  = $this->buildStats($user);

        return view('rooms.index', compact('rooms', 'hotels', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', Room::class);

        $hotels = Auth::user()->isAdmin()
            ? User::where('role', 'hotel')->where('status', 'active')->get()
            : collect();

        return view('rooms.create', compact('hotels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $hotelId = $user->isHotel() ? $user->id : $request->hotel_id;

        $request->merge(['hotel_id' => $hotelId]);
        $request->validate($this->roomRules(), $this->roomMessages());

        $duplicate = Room::where('hotel_id', $hotelId)
            ->where('number', $request->number)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['number' => 'Ya existe una habitación con ese número en este hotel.'])
                ->withInput();
        }

        Room::create($request->only(
            'hotel_id', 'number', 'type', 'description', 'price_per_night', 'capacity', 'status'
        ));

        return redirect()->route('rooms.index')
            ->with('success', 'Habitación registrada correctamente.');
    }

    public function edit(Room $room): View
    {
        $this->authorize('update', $room);

        $hotels = User::where('role', 'hotel')->where('status', 'active')->get();

        return view('rooms.edit', compact('room', 'hotels'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $request->validate(
            array_diff_key($this->roomRules(), ['hotel_id' => '']),
            $this->roomMessages()
        );

        $duplicate = Room::where('hotel_id', $room->hotel_id)
            ->where('number', $request->number)
            ->where('id', '!=', $room->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['number' => 'Ya existe otra habitación con ese número en este hotel.'])
                ->withInput();
        }

        $room->update($request->only(
            'number', 'type', 'description', 'price_per_night', 'capacity', 'status'
        ));

        return redirect()->route('rooms.index')
            ->with('success', 'Habitación actualizada correctamente.');
    }

    public function updateStatus(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $request->validate(['status' => 'required|in:available,occupied,maintenance']);

        $room->update(['status' => $request->status]);

        return back()->with('success', 'Estado de la habitación actualizado.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        if ($room->reservations()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return redirect()->route('rooms.index')
                ->with('error', 'No se puede eliminar: la habitación tiene reservas activas.');
        }

        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', 'Habitación eliminada correctamente.');
    }

    /* ── Helpers privados ───────────────────────── */

    private function buildStats(User $user): array
    {
        $base = Room::query();

        if ($user->isHotel()) {
            $base->forHotel($user->id);
        }

        return [
            'total'       => (clone $base)->count(),
            'available'   => (clone $base)->where('status', 'available')->count(),
            'occupied'    => (clone $base)->where('status', 'occupied')->count(),
            'maintenance' => (clone $base)->where('status', 'maintenance')->count(),
        ];
    }

    private function roomRules(): array
    {
        return [
            'hotel_id'        => 'required|exists:users,id',
            'number'          => 'required|string|max:20',
            'type'            => 'required|in:single,double,suite,family,deluxe',
            'description'     => 'nullable|string|max:500',
            'price_per_night' => 'required|numeric|min:1',
            'capacity'        => 'required|integer|min:1|max:20',
            'status'          => 'required|in:available,occupied,maintenance',
        ];
    }

    private function roomMessages(): array
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
