<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action'
    ];

    // Relationships
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static helper to add log
    public static function add($ticketId, $userId, $action)
    {
        return self::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'action' => $action
        ]);
    }

    // Scopes
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    // Helper for UI display
    public function summary()
    {
        return "{$this->created_at}: {$this->action}";
    }
}
