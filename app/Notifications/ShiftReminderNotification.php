<?php

namespace App\Notifications;

use App\Models\Shift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Shift $shift,
        public string $reminderType = 'day_before'
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
        $role = $shift->businessRole?->name ?? 'Your Role';

        $subject = match ($this->reminderType) {
            'hour_before' => 'Shift Starting Soon',
            'day_before' => 'Shift Reminder - Tomorrow',
            default => 'Upcoming Shift Reminder',
        };

        $timeframe = match ($this->reminderType) {
            'hour_before' => 'in 1 hour',
            'day_before' => 'tomorrow',
            default => 'soon',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("This is a reminder that you have a shift {$timeframe}.")
            ->line('**Shift Details:**')
            ->line("- Date: {$date}")
            ->line("- Time: {$startTime} to {$endTime}")
            ->line("- Location: {$location}")
            ->line("- Role: {$role}")
            ->action('View Schedule', url('/schedule'))
            ->line('See you there!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $shift = $this->shift;

        $message = match ($this->reminderType) {
            'hour_before' => "Your shift at {$shift->location?->name} starts in 1 hour ({$shift->start_time->format('g:i A')}).",
            'day_before' => "Reminder: You have a shift tomorrow at {$shift->start_time->format('g:i A')} at {$shift->location?->name}.",
            default => "Upcoming shift reminder for {$shift->date->format('M j')} at {$shift->start_time->format('g:i A')}.",
        };

        return [
            'type' => 'shift_reminder',
            'reminder_type' => $this->reminderType,
            'shift_id' => $shift->id,
            'date' => $shift->date->format('Y-m-d'),
            'start_time' => $shift->start_time->format('H:i'),
            'end_time' => $shift->end_time->format('H:i'),
            'location' => $shift->location?->name,
            'message' => $message,
        ];
    }
}
