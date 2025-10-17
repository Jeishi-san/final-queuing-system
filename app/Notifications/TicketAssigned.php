<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAssigned extends Notification
{
    use Queueable;

    public Ticket $ticket;
    public bool $reassigned;

    public function __construct(Ticket $ticket, bool $reassigned = false)
    {
        $this->ticket = $ticket;
        $this->reassigned = $reassigned;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->reassigned ? 'Ticket Reassigned to You' : 'New Ticket Assigned';
        return (new MailMessage)
            ->subject($subject)
            ->line("Ticket #{$this->ticket->ticket_number} has been {$subject}.")
            ->line('Issue: ' . $this->ticket->issue_description)
            ->action('View Ticket', url('/dashboard'))
            ->line('Please take action as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'message' => $this->reassigned
                ? 'Ticket was reassigned to you.'
                : 'A new ticket has been assigned to you.',
        ];
    }
}
