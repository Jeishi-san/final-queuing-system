<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model {
    protected $fillable = ['name','email','team_leader_id'];

    public function teamLeader() {
        return $this->belongsTo(TeamLeader::class);
    }
    public function tickets() {
        return $this->hasMany(Ticket::class);
    }
}
