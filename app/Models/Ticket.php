<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * ------------------------------------------------------
     * Constants
     * ------------------------------------------------------
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * ------------------------------------------------------
     * Mass assignable attributes
     * ------------------------------------------------------
     */
    protected $fillable = [
        'ticket_number',
        'title',
        'issue_description',
        'status',
        'priority',
        'category',
        'agent_id',
        'handled_by',       // IT user handling the ticket
        'component_id',
        'resolved_at',      // Added for resolution time tracking
    ];

    /**
     * ------------------------------------------------------
     * Casting attributes
     * ------------------------------------------------------
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * ------------------------------------------------------
     * Default attribute values
     * ------------------------------------------------------
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'priority' => self::PRIORITY_MEDIUM,
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

        static::updating(function ($ticket) {
            // Automatically set resolved_at when status changes to resolved
            if ($ticket->isDirty('status') && $ticket->status === self::STATUS_RESOLVED) {
                $ticket->resolved_at = now();
            }
            
            // Clear resolved_at when reopening a ticket
            if ($ticket->isDirty('status') && $ticket->status !== self::STATUS_RESOLVED) {
                $ticket->resolved_at = null;
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
        return $this->belongsTo(Agent::class);
    }

    /**
     * IT personnel assigned to handle the ticket
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Primary Component
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
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
     * Scope for tickets handled by specific IT personnel
     */
    public function scopeHandledBy($query, $userId)
    {
        return $query->where('handled_by', $userId);
    }

    /**
     * Scope for unassigned tickets (no IT personnel assigned)
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('handled_by');
    }

    /**
     * Scope by priority
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for tickets with resolution time data
     */
    public function scopeWithResolutionTime($query)
    {
        return $query->whereNotNull('resolved_at');
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
        return !is_null($this->handled_by);
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
    public function getHandledByNameAttribute(): string
    {
        return $this->handledBy ? $this->handledBy->name : 'Unassigned';
    }

    /**
     * Get agent name
     */
    public function getAgentNameAttribute(): string
    {
        return $this->agent ? $this->agent->name : 'N/A';
    }

    /**
     * Get resolution time in minutes
     */
    public function getResolutionTimeMinutesAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->resolved_at);
    }

    /**
     * Get resolution time in human readable format
     */
    public function getResolutionTimeHumanAttribute(): ?string
    {
        if (!$this->resolved_at) {
            return null;
        }

        $minutes = $this->resolution_time_minutes;
        
        $days = floor($minutes / (24 * 60));
        $hours = floor(($minutes % (24 * 60)) / 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . ' day' . ($days > 1 ? 's' : '');
        if ($hours > 0) $parts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($mins > 0) $parts[] = $mins . ' minute' . ($mins > 1 ? 's' : '');

        return implode(', ', $parts) ?: '0 minutes';
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
     * Get the time since the ticket was created
     */
    public function getCreatedAtForHumansAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the time since the ticket was resolved
     */
    public function getResolvedAtForHumansAttribute(): ?string
    {
        return $this->resolved_at ? $this->resolved_at->diffForHumans() : null;
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
            'handled_by' => $user->id,
            'status' => self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Unassign ticket from IT personnel
     */
    public function unassign(): bool
    {
        return $this->update([
            'handled_by' => null,
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
            'resolved_at' => now(),
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
            'resolved_at' => null,
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
        } while (self::where('ticket_number', $ticket_number)->exists());
        
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

    /**
     * Get average resolution time across all resolved tickets
     */
    public static function getAverageResolutionTime(): float
    {
        $resolvedTickets = self::resolved()->withResolutionTime()->get();
        
        if ($resolvedTickets->count() === 0) {
            return 0;
        }

        $totalTime = $resolvedTickets->sum(function($ticket) {
            return $ticket->resolution_time_minutes;
        });

        return round($totalTime / $resolvedTickets->count(), 2);
    }
}