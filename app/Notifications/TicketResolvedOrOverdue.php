<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketResolvedOrOverdue extends Notification
{
    use Queueable;

    public Ticket $ticket;
    public string $type; // 'resolved' or 'overdue'

    public function __construct(Ticket $ticket, string $type)
    {
        $this->ticket = $ticket;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->type === 'resolved' ? 'Ticket Resolved' : 'Ticket Overdue';
        return (new MailMessage)
            ->subject($subject)
            ->line("Ticket #{$this->ticket->ticket_number} is now {$this->type}.")
            ->action('View Ticket', url('/dashboard'))
            ->line('Thank you for your attention.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'message' => "Ticket has been marked as {$this->type}.",
        ];
    }
}
