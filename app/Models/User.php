<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
        // 'role', // Uncomment if you use roles (e.g., 'it', 'agent', 'admin')
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
            'password' => 'hashed',
        ];
    }

    /* ============================================================
     |  Relationships
     |============================================================
     */

    /**
     * All activity logs performed by this user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Tickets assigned to this user (as IT personnel).
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'it_personnel_id');
    }

    /* ============================================================
     |  Notifications (provided by Notifiable trait)
     |============================================================
     |
     | The Notifiable trait already defines:
     | - $this->notifications()
     | - $this->unreadNotifications()
     |
     | But we can define helper methods to simplify access.
     */

    /**
     * Get unread notifications count.
     */
    public function unreadCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Get all notifications (latest first).
     */
    public function allNotifications(): Collection
    {
        return $this->notifications()->latest()->get();
    }

    /* ============================================================
     |  Helper / Convenience Methods
     |============================================================
     */

    /**
     * Determine if the user is an IT personnel.
     * 
     * @return bool
     */
    public function isITPersonnel(): bool
    {
        // Example with roles:
        // return $this->role === 'it';

        // Or dynamic check based on assignments:
        return $this->assignedTickets()->exists();
    }
}
