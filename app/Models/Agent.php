<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Agent extends Model
{
    use HasFactory, Notifiable; // ✅ Added HasFactory & Notifiable

    protected $fillable = [
        'name',
        'email',
        'team_leader_id',
    ];

    public function teamLeader()
    {
        return $this->belongsTo(TeamLeader::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
