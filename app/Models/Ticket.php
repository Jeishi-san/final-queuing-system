<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * ------------------------------------------------------
     * Mass assignable attributes
     * ------------------------------------------------------
     */
    protected $fillable = [
        'ticket_number',
        'issue_description',
        'status',
        'agent_id',
        'team_leader_id',
        'it_personnel_id',   // assigned IT personnel
        'component_id',      // ✅ for the main/primary component
    ];

    /**
     * ------------------------------------------------------
     * Relationships
     * ------------------------------------------------------
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
     * Assigned IT Personnel (from the Users table)
     */
    public function itPersonnel()
    {
        return $this->belongsTo(User::class, 'it_personnel_id')->withDefault();
    }

    /**
     * ✅ Primary Component (simple belongsTo for quick display on dashboard)
     */
    public function component()
    {
        return $this->belongsTo(Component::class)->withDefault();
    }

    /**
     * ✅ Many-to-Many Components
     * If a ticket involves multiple components,
     * this relation handles it via the ticket_components pivot table
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
     * Activity logs linked to this ticket
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
