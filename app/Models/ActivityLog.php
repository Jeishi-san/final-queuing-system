<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static log helper
    public static function log($userId, $action)
    {
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
