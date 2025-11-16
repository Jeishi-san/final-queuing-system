<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'ticket_id',
        'assigned_to'
    ];

    // Relationships
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Helpers
    public static function enqueue($ticketId)
    {
        return self::create([
            'ticket_id' => $ticketId,
            'queue_number' => self::max('queue_number') + 1
        ]);
    }

    public function dequeue()
    {
        return $this->delete();
    }

    public static function nextTicket()
    {
        return self::orderBy('queue_number')->first();
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('ticket_id');
    }
}
