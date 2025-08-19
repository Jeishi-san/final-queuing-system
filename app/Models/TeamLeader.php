<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamLeader extends Model {
    protected $fillable = ['name','production_id'];

    public function production() {
        return $this->belongsTo(Production::class);
    }
    public function agents() {
        return $this->hasMany(Agent::class);
    }
    public function tickets() {
        return $this->hasMany(Ticket::class);
    }
}

