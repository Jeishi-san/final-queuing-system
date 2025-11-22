<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'holder_name',
        'holder_email',
        'ticket_number',
        'issue',
        'status'
    ];

    // Relationships
    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Status Checks
    public function isPendingApproval() { return $this->status === 'pending approval'; }
    public function isQueued() { return $this->status === 'queued'; }
    public function isInProgress() { return $this->status === 'in progress'; }
    public function isOnHold() { return $this->status === 'on hold'; }
    public function isResolved() { return $this->status === 'resolved'; }
    public function isCancelled() { return $this->status === 'cancelled'; }

    // Status Setters
    public function markAs($status)
    {
        $this->update(['status' => $status]);
        return $this;
    }

    public function markAsPendingApproval() { return $this->markAs('pending approval'); }
    public function markAsQueued() { return $this->markAs('queued'); }
    public function markAsInProgress() { return $this->markAs('in progress'); }
    public function markAsOnHold() { return $this->markAs('on hold'); }
    public function markAsResolved() { return $this->markAs('resolved'); }
    public function markAsCancelled() { return $this->markAs('cancelled'); }

    // Assignment
    public function assignTo($userId)
    {
        $this->update(['assigned_to' => $userId]);
        return $this;
    }

    // Logging
    public function addLog($userId, $action, $details)
    {
        return TicketLog::create([
            'ticket_id' => $this->id,
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ]);
    }

    // Query Scopes
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
