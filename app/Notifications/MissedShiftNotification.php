<?php

namespace App\Notifications;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MissedShiftNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Shift $shift,
        public User $employee
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
        $employee = $this->employee;
        $date = $shift->date->format('l, F j, Y');
        $startTime = $shift->start_time->format('g:i A');
        $endTime = $shift->end_time->format('g:i A');
        $location = $shift->location?->name ?? 'Unknown Location';

        return (new MailMessage)
            ->subject('Missed Shift Alert')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("{$employee->full_name} did not clock in for their scheduled shift.")
            ->line('**Shift Details:**')
            ->line("- Date: {$date}")
            ->line("- Time: {$startTime} to {$endTime}")
            ->line("- Location: {$location}")
            ->action('View Timesheets', url('/timesheets'))
            ->line('Please follow up with the employee regarding this missed shift.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $shift = $this->shift;
        $employee = $this->employee;

        return [
            'type' => 'missed_shift',
            'shift_id' => $shift->id,
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'date' => $shift->date->format('Y-m-d'),
            'start_time' => $shift->start_time->format('H:i'),
            'end_time' => $shift->end_time->format('H:i'),
            'location' => $shift->location?->name,
            'message' => "{$employee->full_name} missed their shift on {$shift->date->format('M j')} at {$shift->start_time->format('g:i A')}.",
        ];
    }
}
