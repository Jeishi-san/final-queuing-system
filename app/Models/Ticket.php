<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
   protected $fillable = [
    'ticket_number',
    'agent_name',
    'agent_email',
    'team_leader_name',
    'team_leader_email',
    'component_name',
    'issue_description',
    'status',
    'it_personnel_name',
];
}


