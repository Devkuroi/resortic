<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'hotel_id',
        'number',
        'type',
        'description',
        'price_per_night',
        'capacity',
        'status',
        'image',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
    ];

    /* ── Relaciones ─────────────────────────────── */

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hotel_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /* ── Scopes ─────────────────────────────────── */

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeForHotel($query, int $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    /* ── Accessors ──────────────────────────────── */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'single' => 'Sencilla',
            'double' => 'Doble',
            'suite'  => 'Suite',
            'family' => 'Familiar',
            'deluxe' => 'Deluxe',
            default  => ucfirst($this->type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'Disponible',
            'occupied'    => 'Ocupada',
            'maintenance' => 'Mantenimiento',
            default       => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'success',
            'occupied'    => 'danger',
            'maintenance' => 'warning',
            default       => 'secondary',
        };
    }

    /* ── Lógica de dominio ──────────────────────── */

    /**
     * Verifica si la habitación está libre en un rango de fechas.
     * Cubre todos los casos de solapamiento (parcial, completo, exacto).
     * Excluye reservas canceladas y opcionalmente una reserva al editar.
     */
    public function isAvailableForDates(
        string $checkIn,
        string $checkOut,
        ?int $excludeReservationId = null
    ): bool {
        $query = $this->reservations()
            ->whereNotIn('status', ['cancelled'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn);

        if ($excludeReservationId !== null) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->doesntExist();
    }
}
