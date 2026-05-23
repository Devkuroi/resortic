<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password', 'role', 'status'];
    protected $hidden   = ['password'];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

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

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
