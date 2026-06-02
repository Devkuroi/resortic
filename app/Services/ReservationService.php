<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Encapsula toda la lógica de negocio de reservas.
 * Los controladores delegan aquí; ellos solo validan entrada HTTP y devuelven respuestas.
 */
class ReservationService
{
    /**
     * Crea una nueva reserva tras verificar disponibilidad y capacidad.
     *
     * @throws ValidationException
     */
    public function create(array $data, User $client): Reservation
    {
        $room = Room::findOrFail($data['room_id']);

        $this->assertCapacity($room, (int) $data['guests']);
        $this->assertAvailability($room, $data['check_in'], $data['check_out']);

        return Reservation::create([
            'room_id'     => $room->id,
            'client_id'   => $client->id,
            'check_in'    => $data['check_in'],
            'check_out'   => $data['check_out'],
            'guests'      => $data['guests'],
            'total_price' => $this->calculatePrice($room, $data['check_in'], $data['check_out']),
            'status'      => 'pending',
            'notes'       => $data['notes'] ?? null,
        ]);
    }

    /**
     * Actualiza una reserva existente y sincroniza el estado de la habitación.
     *
     * @throws ValidationException
     */
    public function update(Reservation $reservation, array $data): Reservation
    {
        $datesChanged = $reservation->check_in->format('Y-m-d') !== $data['check_in']
            || $reservation->check_out->format('Y-m-d') !== $data['check_out'];

        if ($datesChanged) {
            $this->assertAvailability(
                $reservation->room,
                $data['check_in'],
                $data['check_out'],
                $reservation->id
            );
        }

        $reservation->update([
            'check_in'    => $data['check_in'],
            'check_out'   => $data['check_out'],
            'guests'      => $data['guests'],
            'total_price' => $this->calculatePrice($reservation->room, $data['check_in'], $data['check_out']),
            'status'      => $data['status'],
            'notes'       => $data['notes'] ?? null,
        ]);

        $this->syncRoomStatus($reservation->fresh());

        return $reservation;
    }

    /**
     * Cancela una reserva y libera la habitación.
     *
     * @throws ValidationException
     */
    public function cancel(Reservation $reservation): void
    {
        if (! $reservation->isCancellable()) {
            throw ValidationException::withMessages([
                'status' => 'No se puede cancelar una reserva ya completada.',
            ]);
        }

        $reservation->update(['status' => 'cancelled']);
        $reservation->room->update(['status' => 'available']);
    }

    /* ── Helpers privados ───────────────────────── */

    private function calculatePrice(Room $room, string $checkIn, string $checkOut): float
    {
        $nights = Carbon::parse($checkIn)->diffInDays($checkOut);
        return $nights * $room->price_per_night;
    }

    /** @throws ValidationException */
    private function assertCapacity(Room $room, int $guests): void
    {
        if ($guests > $room->capacity) {
            throw ValidationException::withMessages([
                'guests' => "La habitación tiene capacidad máxima de {$room->capacity} personas.",
            ]);
        }
    }

    /** @throws ValidationException */
    private function assertAvailability(
        Room $room,
        string $checkIn,
        string $checkOut,
        ?int $excludeId = null
    ): void {
        if (! $room->isAvailableForDates($checkIn, $checkOut, $excludeId)) {
            throw ValidationException::withMessages([
                'check_in' => 'La habitación no está disponible en las fechas seleccionadas.',
            ]);
        }
    }

    /**
     * Mantiene el estado de la habitación sincronizado con el estado de la reserva.
     * Solo actúa sobre estados que implican un cambio definitivo.
     */
    private function syncRoomStatus(Reservation $reservation): void
    {
        $room = $reservation->room;

        match ($reservation->status) {
            'confirmed'            => $room->update(['status' => 'occupied']),
            'cancelled', 'completed' => $room->update(['status' => 'available']),
            default                => null,
        };
    }
}
