<?php

namespace App\Notifications;

use App\Models\Shift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Shift $shift
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

        return (new MailMessage)
            ->subject('New Shift Scheduled')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("You have been scheduled to work on {$date} from {$startTime} to {$endTime} at {$location}.")
            ->line('Please log in to view your shift details.')
            ->action('View Schedule', url('/schedule'))
            ->line('Thank you for being part of our team!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $shift = $this->shift;

        return [
            'type' => 'shift_published',
            'shift_id' => $shift->id,
            'date' => $shift->date->format('Y-m-d'),
            'start_time' => $shift->start_time->format('H:i'),
            'end_time' => $shift->end_time->format('H:i'),
            'location' => $shift->location?->name,
            'message' => "You have been scheduled to work on {$shift->date->format('M j')} from {$shift->start_time->format('g:i A')} to {$shift->end_time->format('g:i A')} at {$shift->location?->name}.",
        ];
    }
}
