<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements MustVerifyEmail
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
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function attorneyProfile(): BelongsTo
    {
        return $this->belongsTo(Attorney::class, 'attorney_id');
    }

    public function isAdministrator(): bool
    {
        return $this->role === 'admin' || $this->is_admin;
    }

    public function isAttorney(): bool
    {
        return $this->role === 'attorney' && ! $this->isAdministrator() && $this->attorney_id !== null;
    }

    public function isClient(): bool
    {
        return $this->role === 'client' && ! $this->isAdministrator();
    }
}
