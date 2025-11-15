<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_id',
        'log_date',
        'action',
        'details'
    ];

    protected $casts = [
        'log_date' => 'datetime',
    ];

    /**
     * User (IT personnel) who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ticket that was acted upon
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

}