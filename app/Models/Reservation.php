<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'room_id',
        'client_id',
        'check_in',
        'check_out',
        'guests',
        'total_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in'    => 'date',
        'check_out'   => 'date',
        'total_price' => 'decimal:2',
    ];

    /* ── Relaciones ─────────────────────────────── */

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /* ── Accessors ──────────────────────────────── */

    public function getNightsAttribute(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            'completed' => 'Completada',
            default     => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'secondary',
            'completed' => 'info',
            default     => 'secondary',
        };
    }

    /* ── Helpers de estado ──────────────────────── */

    public function isCancellable(): bool
    {
        return ! in_array($this->status, ['completed', 'cancelled']);
    }

    public function isEditable(): bool
    {
        return ! in_array($this->status, ['completed', 'cancelled']);
    }
}
