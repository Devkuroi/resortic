<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Admin ve todo. Hotel ve reservas de sus habitaciones. Cliente ve las suyas.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return match ($user->role) {
            'admin'  => true,
            'hotel'  => $reservation->room->hotel_id === $user->id,
            'client' => $reservation->client_id === $user->id,
            default  => false,
        };
    }

    /**
     * Solo admin y hotel pueden crear reservas.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isHotel();
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return match ($user->role) {
            'admin'  => true,
            'hotel'  => $reservation->room->hotel_id === $user->id,
            'client' => false,
            default  => false,
        };
    }

    /**
     * Solo admin y hotel pueden cancelar reservas.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return match ($user->role) {
            'admin'  => true,
            'hotel'  => $reservation->room->hotel_id === $user->id,
            'client' => false,
            default  => false,
        };
    }
}
