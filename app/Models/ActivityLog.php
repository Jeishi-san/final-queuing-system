<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ticket; // Import Ticket Model

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id', // ✅ ADDED: Assuming this column exists in your activity_logs table
        'action'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // ✅ ADDED: Define the relationship to the Ticket model
    public function ticket()
    {
        // Assumes your activity_logs table has a 'ticket_id' column
        return $this->belongsTo(Ticket::class);
    }

    // Static log helper
    public static function log($userId, $action)
    {
        // NOTE: This static method needs to be updated if you want to log a ticket_id
        return self::create([
            'user_id' => $userId,
            'action' => $action
        ]);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper
    public function description()
    {
        return "{$this->log_date}: {$this->action} by User ID {$this->user_id}s";
    }
}