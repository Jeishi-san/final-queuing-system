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

class TicketUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $changes;
    protected $updater;

    public function __construct(Ticket $ticket, array $changes, User $updater = null)
    {
        $this->ticket = $ticket;
        $this->changes = $changes;
        $this->updater = $updater;

        Log::info('TicketUpdatedNotification created', [
            'ticket_id' => $ticket->id,
            'changes_count' => count($changes),
            'changes_keys' => array_keys($changes),
            'updater_id' => $updater ? $updater->id : 'null'
        ]);
    }

    public function via($notifiable)
    {
        Log::debug('Determining notification channels', [
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id
        ]);

        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        Log::info('Sending email notification', [
            'notifiable_id' => $notifiable->id,
            'notifiable_email' => $notifiable->email,
            'ticket_id' => $this->ticket->id
        ]);

        $mailMessage = (new MailMessage)
            ->subject('🔔 Ticket Updated: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The ticket "' . $this->ticket->title . '" has been updated.')
            ->line('Updated by: ' . ($this->updater->name ?? 'System'));

        foreach ($this->changes as $field => $change) {
            $fieldName = $this->getFieldDisplayName($field);
            $fromValue = $this->formatDisplayValue($field, $change['from']);
            $toValue = $this->formatDisplayValue($field, $change['to']);
            
            $mailMessage->line("{$fieldName}: {$fromValue} → {$toValue}");
        }

        $mailMessage->action('View Ticket', url('/tickets/' . $this->ticket->id))
                   ->line('Thank you for using our system!');

        return $mailMessage;
    }

    public function toDatabase($notifiable)
    {
        Log::info('Creating database notification', [
            'notifiable_id' => $notifiable->id,
            'ticket_id' => $this->ticket->id,
            'changes_count' => count($this->changes)
        ]);

        $notificationData = [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'ticket_title' => $this->ticket->title,
            'updated_by' => $this->updater->name ?? 'System',
            'changes' => $this->changes,
            'message' => $this->getNotificationMessage(),
            'type' => 'ticket_updated',
            'timestamp' => now()->toISOString()
        ];

        Log::debug('Database notification data', $notificationData);

        return $notificationData;
    }

    protected function getNotificationMessage()
    {
        $updater = $this->updater->name ?? 'System';
        $changes = [];
        
        foreach ($this->changes as $field => $change) {
            $fieldName = $this->getFieldDisplayName($field);
            $fromValue = $this->formatDisplayValue($field, $change['from']);
            $toValue = $this->formatDisplayValue($field, $change['to']);
            
            switch ($field) {
                case 'status':
                    $changes[] = "status changed from {$fromValue} to {$toValue}";
                    break;
                case 'it_personnel_id':
                    $changes[] = "assignment changed from {$fromValue} to {$toValue}";
                    break;
                case 'component_id':
                    $changes[] = "component changed from {$fromValue} to {$toValue}";
                    break;
                default:
                    $changes[] = "{$fieldName} updated from {$fromValue} to {$toValue}";
                    break;
            }
        }
        
        $ticketNumber = $this->ticket->ticket_number ?? 'Unknown Ticket';
        $ticketTitle = $this->ticket->title ?? 'Untitled Ticket';
        
        $message = "Ticket #{$ticketNumber} \"{$ticketTitle}\" was updated by {$updater}. " . implode(', ', $changes);
        
        Log::debug('Generated notification message', [
            'message' => $message,
            'ticket_number' => $ticketNumber,
            'ticket_title' => $ticketTitle
        ]);
        
        return $message;
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
        Log::debug("Formatting display value", [
            'field' => $field,
            'value' => $value,
            'value_type' => gettype($value)
        ]);

        // Handle null or empty values
        if ($value === null || $value === '' || $value === 'None') {
            return 'None';
        }

        // Handle numeric strings by converting to integer for ID lookups
        if (is_string($value) && is_numeric($value)) {
            $value = (int) $value;
        }

        switch ($field) {
            case 'it_personnel_id':
                // If value is already a name (string), return it directly
                if (is_string($value) && !is_numeric($value)) {
                    return $value;
                }
                
                // If value is numeric, look up the user
                if (is_numeric($value) && $value > 0) {
                    $user = User::find($value);
                    return $user ? $user->name : 'Unassigned';
                }
                
                return 'Unassigned';
            
            case 'component_id':
                // If value is already a name (string), return it directly
                if (is_string($value) && !is_numeric($value)) {
                    return $value;
                }
                
                // If value is numeric, look up the component
                if (is_numeric($value) && $value > 0) {
                    $component = Component::find($value);
                    return $component ? $component->name : 'No Component';
                }
                
                return 'No Component';
            
            case 'status':
                // Handle status values
                if (is_string($value)) {
                    return ucfirst(str_replace('_', ' ', $value));
                }
                return 'Unknown Status';
            
            default:
                // For any other field, return the value as is
                return is_string($value) ? $value : (string) $value;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}