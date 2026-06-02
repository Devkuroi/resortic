<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * Admin y hotel pueden listar. Cliente no tiene acceso a la gestión de habitaciones.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'hotel']);
    }

    /**
     * Admin ve cualquier habitación. Hotel solo las suyas.
     */
    public function view(User $user, Room $room): bool
    {
        return $user->isAdmin() || $room->hotel_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'hotel']);
    }

    public function update(User $user, Room $room): bool
    {
        return $user->isAdmin() || $room->hotel_id === $user->id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->isAdmin() || $room->hotel_id === $user->id;
    }
}
