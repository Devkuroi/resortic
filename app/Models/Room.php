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

    /* ── Accessors ──────────────────────────────── */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'single'  => 'Sencilla',
            'double'  => 'Doble',
            'suite'   => 'Suite',
            'family'  => 'Familiar',
            'deluxe'  => 'Deluxe',
            default   => ucfirst($this->type),
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

    /* ── Scopes ─────────────────────────────────── */

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Verifica si la habitación está libre en un rango de fechas
     * (excluye reservas canceladas; opcionalmente excluye una reserva al editar)
     */
    public function isAvailableForDates(string $checkIn, string $checkOut, ?int $excludeReservationId = null): bool
    {
        $query = $this->reservations()
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in',  [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in',  '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->count() === 0;
    }
}
