<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'ticket_number',
        'issue_description',
        'status',
        'agent_id',
        'team_leader_id',
        'it_personnel_id', // still named this, but references users table
    ];

    /**
     * -------------------------------
     * Relationships
     * -------------------------------
     */

    /**
     * Agent who submitted the ticket
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class)->withDefault();
    }

    /**
     * Team leader associated with the agent
     */
    public function teamLeader()
    {
        return $this->belongsTo(TeamLeader::class)->withDefault();
    }

    /**
     * IT Personnel (actually a User)
     * We keep the column name it_personnel_id but reference the users table
     */
    public function itPersonnel()
    {
        return $this->belongsTo(User::class, 'it_personnel_id')->withDefault();
    }

    /**
     * Components associated with this ticket
     */
    public function components()
    {
        return $this->belongsToMany(Component::class, 'ticket_components')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Queue logs for ticket processing
     */
    public function queueLogs()
    {
        return $this->hasMany(QueueLog::class);
    }

    /**
     * Activity logs linked to the ticket
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
