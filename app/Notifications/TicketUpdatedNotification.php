<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Component;
use Illuminate\Support\Facades\Log;

class TicketUpdatedNotification extends Notification
{
    use Queueable;

    protected $ticket;
    protected $changes;
    protected $updater;

    /**
     * @param Ticket $ticket
     * @param array $changes  e.g., ['status' => ['from' => 'x', 'to' => 'y']] OR ['is_new' => true]
     * @param User|null $updater Null if Guest
     */
    public function __construct(Ticket $ticket, array $changes, User $updater = null)
    {
        $this->ticket = $ticket;
        $this->changes = $changes;
        $this->updater = $updater;
    }

    public function via($notifiable)
    {
        return ['database']; // Add 'mail' here if you have email configured
    }

    public function toDatabase($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            // Use 'issue' as title since 'title' might not exist in your model
            'ticket_title' => $this->ticket->issue ?? $this->ticket->ticket_number, 
            'updated_by' => $this->updater ? $this->updater->name : 'Guest',
            'changes' => $this->changes,
            'message' => $this->getNotificationMessage(),
            'type' => $this->determineType(),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Determine if this is a new ticket or an update
     */
    protected function determineType()
    {
        if (isset($this->changes['is_new']) && $this->changes['is_new']) {
            return 'new_ticket';
        }
        return 'ticket_updated';
    }

    protected function getNotificationMessage()
    {
        $ticketNumber = $this->ticket->ticket_number;
        $updaterName = $this->updater ? $this->updater->name : 'Guest';

        // SCENARIO 1: New Ticket
        if (isset($this->changes['is_new']) && $this->changes['is_new']) {
            return "New Ticket #{$ticketNumber} submitted by {$this->ticket->holder_name}";
        }

        // SCENARIO 2: Status Update or other changes
        $changesStrings = [];
        foreach ($this->changes as $field => $change) {
            $fieldName = $this->getFieldDisplayName($field);
            $fromValue = $this->formatDisplayValue($field, $change['from'] ?? null);
            $toValue = $this->formatDisplayValue($field, $change['to'] ?? null);
            
            switch ($field) {
                case 'status':
                    $changesStrings[] = "status changed to '{$toValue}'";
                    break;
                case 'it_personnel_id':
                    $changesStrings[] = "assigned to {$toValue}";
                    break;
                default:
                    $changesStrings[] = "{$fieldName} updated";
                    break;
            }
        }
        
        $changeDetails = implode(', ', $changesStrings);
        return "Ticket #{$ticketNumber} updated by {$updaterName}: {$changeDetails}";
    }

    protected function getFieldDisplayName($field)
    {
        $fieldNames = [
            'status' => 'Status',
            'it_personnel_id' => 'Assigned To',
            'component_id' => 'Component'
        ];

        return $fieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    protected function formatDisplayValue($field, $value)
    {
        if ($value === null || $value === '' || $value === 'None') {
            return 'None';
        }

        switch ($field) {
            case 'it_personnel_id':
                if (is_numeric($value)) {
                    $user = User::find($value);
                    return $user ? $user->name : 'Unassigned';
                }
                return $value; // If it's already a string
            
            case 'status':
                return ucfirst($value);
            
            default:
                return (string) $value;
        }
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}