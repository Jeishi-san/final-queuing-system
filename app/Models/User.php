<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'employee_id',
        'role',
        'department',
        'contact_number',
        'account_status'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    // Relationships
    public function ticketsHandled()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Helper: Check if staff is active employee
    public function isActive()
    {
        return $this->account_status === 'active';
    }

    // Tickets this staff is currently working on
    public function currentQueueTickets()
    {
        return $this->ticketsHandled()->whereIn('status', [
            'queued', 'in progress', 'on hold'
        ]);
    }

    // Log activity in the system
    public function logActivity($ticketId, $action, $details)
    {
        return ActivityLog::create([
            'user_id' => $this->id,
            'ticket_id' => $ticketId,
            'action' => $action,
            'details' => $details
        ]);
    }

    // ✅ FIXED: This function is now INSIDE the class brackets
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
// ✅ The class ends HERE. Nothing should be below this line.
