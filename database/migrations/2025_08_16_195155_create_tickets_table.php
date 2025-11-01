<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'it_personnel_id',
        'component_id',
    ];

    /**
     * ------------------------------------------------------
     * Casting attributes
     * ------------------------------------------------------
     */
    protected $casts = [
        // Add any necessary casts here
    ];

    /**
     * ------------------------------------------------------
     * Default attribute values
     * ------------------------------------------------------
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * ------------------------------------------------------
     * Relationships
     * ------------------------------------------------------
     */

    /**
     * Agent who reported the issue
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class)->withDefault();
    }

    /**
     * Team leader associated with the agent
     */
    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(TeamLeader::class)->withDefault();
    }

    /**
     * IT personnel assigned to handle the ticket
     */
    public function itPersonnel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'it_personnel_id')->withDefault();
    }

    /**
     * Component (e.g., hardware or software involved)
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class)->withDefault();
    }

    /**
     * ------------------------------------------------------
     * Scopes for common queries
     * ------------------------------------------------------
     */

    /**
     * Scope for pending tickets
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in-progress tickets
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for resolved tickets
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for tickets assigned to specific IT personnel
     */
    public function scopeAssignedTo($query, $itPersonnelId)
    {
        return $query->where('it_personnel_id', $itPersonnelId);
    }

    /**
     * Scope for unassigned tickets (no IT personnel assigned)
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('it_personnel_id');
    }

    /**
     * Scope for tickets with components
     */
    public function scopeWithComponents($query)
    {
        return $query->whereNotNull('component_id');
    }

    /**
     * ------------------------------------------------------
     * Accessors & Mutators
     * ------------------------------------------------------
     */

    /**
     * Check if ticket is assigned to IT personnel
     */
    public function getIsAssignedAttribute(): bool
    {
        return !is_null($this->it_personnel_id);
    }

    /**
     * Check if ticket is pending
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if ticket is in progress
     */
    public function getIsInProgressAttribute(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if ticket is resolved
     */
    public function getIsResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Get assigned IT personnel name
     */
    public function getAssignedPersonnelNameAttribute(): string
    {
        return $this->itPersonnel ? $this->itPersonnel->name : 'Unassigned';
    }

    /**
     * Get agent name
     */
    public function getAgentNameAttribute(): string
    {
        return $this->agent ? $this->agent->name : 'N/A';
    }

    /**
     * Get team leader name
     */
    public function getTeamLeaderNameAttribute(): string
    {
        return $this->teamLeader ? $this->teamLeader->name : 'N/A';
    }

    /**
     * Get component name
     */
    public function getComponentNameAttribute(): string
    {
        return $this->component ? $this->component->name : 'No Component';
    }

    /**
     * Get the time since the ticket was created
     */
    public function getCreatedAtForHumansAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get short issue description (truncated)
     */
    public function getShortIssueDescriptionAttribute(): string
    {
        return strlen($this->issue_description) > 100 
            ? substr($this->issue_description, 0, 100) . '...' 
            : $this->issue_description;
    }

    /**
     * ------------------------------------------------------
     * Business Logic Methods
     * ------------------------------------------------------
     */

    /**
     * Assign ticket to IT personnel
     */
    public function assignTo(User $user): bool
    {
        return $this->update([
            'it_personnel_id' => $user->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Unassign ticket from IT personnel
     */
    public function unassign(): bool
    {
        return $this->update([
            'it_personnel_id' => null,
            'status' => 'pending',
        ]);
    }

    /**
     * Mark ticket as resolved
     */
    public function markAsResolved(): bool
    {
        return $this->update([
            'status' => 'resolved',
        ]);
    }

    /**
     * Mark ticket as in progress
     */
    public function markAsInProgress(): bool
    {
        return $this->update([
            'status' => 'in_progress',
        ]);
    }

    /**
     * Reopen a resolved ticket
     */
    public function reopen(): bool
    {
        return $this->update([
            'status' => 'in_progress',
        ]);
    }

    /**
     * Check if ticket can be assigned
     */
    public function canBeAssigned(): bool
    {
        return $this->is_pending || $this->is_in_progress;
    }

    /**
     * Check if ticket can be resolved
     */
    public function canBeResolved(): bool
    {
        return $this->is_in_progress && $this->is_assigned;
    }

    /**
     * Get ticket status with badge color
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'in_progress' => '<span class="badge badge-info">In Progress</span>',
            'resolved' => '<span class="badge badge-success">Resolved</span>',
            default => '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>'
        };
    }

    /**
     * ------------------------------------------------------
     * Static Methods
     * ------------------------------------------------------
     */

    /**
     * Generate a unique ticket number
     */
    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');
        
        do {
            $random = strtoupper(substr(uniqid(), -6));
            $ticketNumber = "{$prefix}-{$date}-{$random}";
        } while (self::where('ticket_number', $ticketNumber)->exists());
        
        return $ticketNumber;
    }

    /**
     * Get ticket statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'pending' => self::pending()->count(),
            'in_progress' => self::inProgress()->count(),
            'resolved' => self::resolved()->count(),
            'unassigned' => self::unassigned()->count(),
        ];
    }
}