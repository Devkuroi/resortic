<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = ['password'];

    // Etiquetas legibles para rol
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'  => 'Administrador',
            'hotel'  => 'Hotel',
            'client' => 'Cliente',
            default  => $this->role,
        };
    }

    // Etiqueta legible para estado
    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Activo' : 'Inactivo';
    }

    // Badge Bootstrap para rol
    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            'admin'  => 'warning',
            'hotel'  => 'info',
            'client' => 'success',
            default  => 'secondary',
        };
    }

    // Badge Bootstrap para estado
    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
