<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /* ── Relaciones ─────────────────────────────── */

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

    /* ── Helpers de rol ─────────────────────────── */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHotel(): bool
    {
        return $this->role === 'hotel';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /* ── Accessors ──────────────────────────────── */

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'  => 'Administrador',
            'hotel'  => 'Hotel',
            'client' => 'Cliente',
            default  => $this->role,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Activo' : 'Inactivo';
    }

    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            'admin'  => 'warning',
            'hotel'  => 'info',
            'client' => 'success',
            default  => 'secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }
}
