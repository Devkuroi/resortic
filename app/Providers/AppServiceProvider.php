<?php

namespace App\Providers;

use App\Models\Reservation;
use App\Models\Room;
use App\Policies\ReservationPolicy;
use App\Policies\RoomPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registrar policies explícitamente
        // (Laravel también las detecta por convención App\Models\X → App\Policies\XPolicy)
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);

        // Los admin bypasean todos los gates
        Gate::before(function ($user) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
