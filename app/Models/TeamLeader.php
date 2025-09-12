<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamLeader extends Model
{
    protected $fillable = [
        'name',
        'email',          // ✅ added so you can mass assign email
        'production_id',
    ];

    /**
     * A team leader belongs to a production.
     */
    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    /**
     * A team leader can manage many agents.
     */
    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    /**
     * A team leader can be linked to many tickets.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
