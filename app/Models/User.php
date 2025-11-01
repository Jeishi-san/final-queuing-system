<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

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
        'profile_picture', // ✅ Added for profile image support
        // 'role', // Uncomment if you use roles (e.g., 'admin', 'it', 'agent')
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
     * Tickets created/reported by this user.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * Tickets assigned to this user (as IT personnel).
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'it_personnel_id');
    }

    /* ============================================================
     |  Notifications (from Notifiable trait)
     |============================================================
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
     */
    public function isITPersonnel(): bool
    {
        // Example with roles:
        // return $this->role === 'it';

        // Dynamic check based on assigned tickets
        return $this->assignedTickets()->exists();
    }

    /**
     * ✅ Get the full URL of the user's profile picture.
     */
    public function profileImageUrl(): string
    {
        if ($this->profile_picture && Storage::disk('public')->exists($this->profile_picture)) {
            return asset('storage/' . $this->profile_picture);
        }

        // Default fallback image
        return asset('images/default-avatar.png');
    }

    /**
     * ✅ Delete old profile picture from storage.
     */
    public function deleteProfileImage(): void
    {
        if ($this->profile_picture && Storage::disk('public')->exists($this->profile_picture)) {
            Storage::disk('public')->delete($this->profile_picture);
        }
    }
}
