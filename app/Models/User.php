<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $attributes = ['role' => 'client'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function attorneyProfile(): BelongsTo
    {
        return $this->belongsTo(Attorney::class, 'attorney_id');
    }

    public function recoveryCodes(): HasMany
    {
        return $this->hasMany(AccountRecoveryCode::class);
    }

    /** Accounts that hold (or held) the administrator role, active or not. */
    public function scopeAdministrators(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->where('role', 'admin')->orWhere('is_admin', true));
    }

    /** Accounts that have not been deactivated. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deactivated_at');
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    /** Has the administrator role, whether or not the account is currently deactivated. */
    public function hasAdminRole(): bool
    {
        return $this->role === 'admin' || (bool) $this->is_admin;
    }

    /** A deactivated administrator is not an administrator for any access check. */
    public function isAdministrator(): bool
    {
        return $this->hasAdminRole() && ! $this->isDeactivated();
    }

    public function isAttorney(): bool
    {
        return $this->role === 'attorney' && ! $this->hasAdminRole() && $this->attorney_id !== null;
    }

    public function isClient(): bool
    {
        return $this->role === 'client' && ! $this->hasAdminRole();
    }
}
