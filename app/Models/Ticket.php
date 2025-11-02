<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    /**
     * ------------------------------------------------------
     * Constants
     * ------------------------------------------------------
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';

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
        'component_id',      // primary component for quick display
    ];

    /**
     * ------------------------------------------------------
     * Casting attributes
     * ------------------------------------------------------
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ------------------------------------------------------
     * Default attribute values
     * ------------------------------------------------------
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    /**
     * ------------------------------------------------------
     * Model event booting
     * ------------------------------------------------------
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

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
     * Primary Component (for quick display on dashboard)
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class)->withDefault();
    }

    /**
     * Many-to-Many Components
     * For tickets involving multiple components
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'ticket_components')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Queue logs for ticket processing
     */
    public function queueLogs(): HasMany
    {
        return $this->hasMany(QueueLog::class);
    }

    /**
     * Activity logs linked to this ticket
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
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
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for in-progress tickets
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope for resolved tickets
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
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
     * Scope for tickets with primary component
     */
    public function scopeWithComponents($query)
    {
        return $query->whereNotNull('component_id');
    }

    /**
     * Scope for tickets with multiple components
     */
    public function scopeWithMultipleComponents($query)
    {
        return $query->whereHas('components');
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
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if ticket is in progress
     */
    public function getIsInProgressAttribute(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if ticket is resolved
     */
    public function getIsResolvedAttribute(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
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
     * Get primary component name
     */
    public function getComponentNameAttribute(): string
    {
        return $this->component ? $this->component->name : 'No Component';
    }

    /**
     * Get all component names (including multiple components)
     */
    public function getAllComponentNamesAttribute(): string
    {
        if ($this->components->isNotEmpty()) {
            return $this->components->pluck('name')->join(', ');
        }
        
        return $this->component_name;
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
     * Check if ticket has multiple components
     */
    public function getHasMultipleComponentsAttribute(): bool
    {
        return $this->components->isNotEmpty();
    }

    /**
     * Get total components count (primary + multiple)
     */
    public function getTotalComponentsCountAttribute(): int
    {
        $count = $this->components->count();
        if ($this->component_id && !$this->components->contains('id', $this->component_id)) {
            $count++;
        }
        return $count;
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
            'status' => self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Unassign ticket from IT personnel
     */
    public function unassign(): bool
    {
        return $this->update([
            'it_personnel_id' => null,
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Mark ticket as resolved
     */
    public function markAsResolved(): bool
    {
        if (!$this->canBeResolved()) {
            throw new \Exception('Ticket cannot be resolved at this time');
        }
        
        return $this->update([
            'status' => self::STATUS_RESOLVED,
        ]);
    }

    /**
     * Mark ticket as in progress
     */
    public function markAsInProgress(): bool
    {
        return $this->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Reopen a resolved ticket
     */
    public function reopen(): bool
    {
        return $this->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Add multiple components to ticket
     */
    public function addComponent(Component $component, int $quantity = 1): void
    {
        $this->components()->syncWithoutDetaching([
            $component->id => ['quantity' => $quantity]
        ]);
    }

    /**
     * Remove component from ticket
     */
    public function removeComponent(Component $component): void
    {
        $this->components()->detach($component->id);
    }

    /**
     * Set primary component
     */
    public function setPrimaryComponent(Component $component): bool
    {
        return $this->update(['component_id' => $component->id]);
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
            self::STATUS_PENDING => '<span class="badge badge-warning">Pending</span>',
            self::STATUS_IN_PROGRESS => '<span class="badge badge-info">In Progress</span>',
            self::STATUS_RESOLVED => '<span class="badge badge-success">Resolved</span>',
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
            'with_multiple_components' => self::withMultipleComponents()->count(),
        ];
    }
}   