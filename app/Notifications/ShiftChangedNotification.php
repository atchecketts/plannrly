<?php

namespace App\Notifications;

use App\Models\Shift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Shift $shift,
        public string $changeType = 'updated'
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $shift = $this->shift;
        $date = $shift->date->format('l, F j, Y');
        $startTime = $shift->start_time->format('g:i A');
        $endTime = $shift->end_time->format('g:i A');
        $location = $shift->location?->name ?? 'Unknown Location';

        $subject = match ($this->changeType) {
            'deleted' => 'Shift Cancelled',
            'reassigned' => 'Shift Reassigned',
            default => 'Shift Updated',
        };

        $message = match ($this->changeType) {
            'deleted' => "Your shift on {$date} at {$location} has been cancelled.",
            'reassigned' => "You have been assigned to a shift on {$date} from {$startTime} to {$endTime} at {$location}.",
            default => "Your shift on {$date} has been updated. The new time is {$startTime} to {$endTime} at {$location}.",
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->first_name}!")
            ->line($message)
            ->action('View Schedule', url('/schedule'))
            ->line('Please review your updated schedule.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $shift = $this->shift;

        $message = match ($this->changeType) {
            'deleted' => "Your shift on {$shift->date->format('M j')} has been cancelled.",
            'reassigned' => "You've been assigned to work on {$shift->date->format('M j')} at {$shift->start_time->format('g:i A')}.",
            default => "Your shift on {$shift->date->format('M j')} has been updated to {$shift->start_time->format('g:i A')} - {$shift->end_time->format('g:i A')}.",
        };

        return [
            'type' => 'shift_changed',
            'change_type' => $this->changeType,
            'shift_id' => $shift->id,
            'date' => $shift->date->format('Y-m-d'),
            'start_time' => $shift->start_time->format('H:i'),
            'end_time' => $shift->end_time->format('H:i'),
            'location' => $shift->location?->name,
            'message' => $message,
        ];
    }
}
