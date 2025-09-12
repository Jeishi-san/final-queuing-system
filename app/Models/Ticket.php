<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'agent_id',
        'team_leader_id',
        'component_id',
        'issue_description',
        'status',
        'it_personnel_name',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function teamLeader()
    {
        return $this->belongsTo(TeamLeader::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
