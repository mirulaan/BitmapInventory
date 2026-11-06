<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'is_active', 'password_reset_code', 'password_reset_code_expires_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_WAREHOUSE = 'warehouse';
    public const ROLE_ENGINEER = 'engineer';
    public const ROLE_VIEWER = 'viewer';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'password_reset_code_expires_at' => 'datetime',
        ];
    }

    /**
     * Check if user has a valid active reset code.
     */
    public function hasActiveResetCode(): bool
    {
        return ! empty($this->password_reset_code) 
            && $this->password_reset_code_expires_at 
            && $this->password_reset_code_expires_at->isFuture();
    }

    /**
     * Check if user is Super Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is Warehouse Manager.
     */
    public function isWarehouse(): bool
    {
        return $this->role === self::ROLE_WAREHOUSE;
    }

    /**
     * Check if user is Field / Sales Engineer.
     */
    public function isEngineer(): bool
    {
        return $this->role === self::ROLE_ENGINEER;
    }

    /**
     * Check if user is Auditor / Viewer (Read-only).
     */
    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param string|array<string> ...$roles
     */
    public function hasRole(string|array ...$roles): bool
    {
        $flattened = [];
        foreach ($roles as $role) {
            if (is_array($role)) {
                $flattened = array_merge($flattened, $role);
            } else {
                $flattened = array_merge($flattened, array_map('trim', explode(',', $role)));
            }
        }

        return in_array($this->role, $flattened, true);
    }

    /**
     * Get human-friendly display title for the role.
     */
    public function getRoleTitleAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Super Admin',
            self::ROLE_WAREHOUSE => 'Warehouse Manager',
            self::ROLE_ENGINEER => 'Field Engineer',
            self::ROLE_VIEWER => 'Auditor / Viewer',
            default => ucfirst(str_replace('_', ' ', $this->role ?? 'Viewer')),
        };
    }

    /**
     * Get styling badge colors for the role.
     */
    public function getRoleBadgeColorAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'bg-purple-50 text-purple-700 border-purple-200/80',
            self::ROLE_WAREHOUSE => 'bg-blue-50 text-blue-700 border-blue-200/80',
            self::ROLE_ENGINEER => 'bg-amber-50 text-amber-700 border-amber-200/80',
            self::ROLE_VIEWER => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}
