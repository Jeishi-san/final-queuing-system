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
        'team_leader_name',
        'component',
        'issue_description',
        'status',
        'it_personnel_id',
    ];

    // Relationship with IT Personnel (User)
    public function itPersonnel()
    {
        return $this->belongsTo(User::class, 'it_personnel_id');
    }
}
