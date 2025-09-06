<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'it_personnel_id',
        'action',
        'remarks',
    ];

    /**
     * Relationship: QueueLog belongs to a Ticket
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relationship: QueueLog belongs to an IT Personnel (User)
     */
    public function itPersonnel()
    {
        return $this->belongsTo(User::class, 'it_personnel_id');
    }
}
