<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'agent_name',
        'agent_email',
        'team_leader_name',
        'component',
        'issue_description',
        'status',
        'it_personnel_name',
    ];
}
