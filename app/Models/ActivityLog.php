<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'action',
        'details'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Static log helper
    public static function log($userId, $ticketId, $action, $details)
    {
        return self::create([
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'action' => $action,
            'details' => $details
        ]);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    // Helper
    public function description()
    {
        return "{$this->log_date}: {$this->action} - {$this->details}";
    }
}
