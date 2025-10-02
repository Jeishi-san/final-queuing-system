<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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
        // 'role', // Uncomment if you use a role column to separate IT staff from others
    ];

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /* ============================================================
     |  Relationships
     |============================================================
     */

    /**
     * All activity logs performed by this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Tickets that were assigned to this user (IT personnel).
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'it_personnel_id');
    }

    /* ============================================================
     |  Helper / Convenience Methods
     |============================================================
     */

    /**
     * Check if this user is an IT personnel.
     * Useful if you distinguish staff by a 'role' column in users table.
     */
    public function isITPersonnel(): bool
    {
        // If you store a role:
        // return $this->role === 'it';

        // If you only use this user as IT staff when they have assigned tickets:
        return $this->assignedTickets()->exists();
    }
}
