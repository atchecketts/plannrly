<?php

namespace App\Notifications;

use App\Models\ShiftSwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SwapRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ShiftSwapRequest $swapRequest
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
        $swap = $this->swapRequest;
        $requester = $swap->requestingUser;
        $shift = $swap->requestingShift;
        $date = $shift->date->format('l, F j, Y');
        $startTime = $shift->start_time->format('g:i A');
        $endTime = $shift->end_time->format('g:i A');
        $location = $shift->location?->name ?? 'Unknown Location';

        $mailMessage = (new MailMessage)
            ->subject('Shift Swap Request')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("{$requester->full_name} would like to swap shifts with you.")
            ->line('**Their Shift:**')
            ->line("- Date: {$date}")
            ->line("- Time: {$startTime} to {$endTime}")
            ->line("- Location: {$location}");

        if ($swap->targetShift) {
            $targetDate = $swap->targetShift->date->format('l, F j, Y');
            $targetStart = $swap->targetShift->start_time->format('g:i A');
            $targetEnd = $swap->targetShift->end_time->format('g:i A');
            $mailMessage->line('**Your Shift They Want:**')
                ->line("- Date: {$targetDate}")
                ->line("- Time: {$targetStart} to {$targetEnd}");
        }

        if ($swap->reason) {
            $mailMessage->line("**Reason:** {$swap->reason}");
        }

        return $mailMessage
            ->action('View Swap Requests', url('/shift-swaps'))
            ->line('Please respond to this swap request.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $swap = $this->swapRequest;
        $requester = $swap->requestingUser;
        $shift = $swap->requestingShift;

        return [
            'type' => 'swap_request',
            'swap_request_id' => $swap->id,
            'requester_id' => $requester->id,
            'requester_name' => $requester->full_name,
            'shift_date' => $shift->date->format('Y-m-d'),
            'shift_time' => $shift->start_time->format('H:i'),
            'message' => "{$requester->full_name} wants to swap their shift on {$shift->date->format('M j')} with you.",
        ];
    }
}
