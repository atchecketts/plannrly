<?php

namespace App\Notifications;

use App\Models\ShiftSwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SwapRequestResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ShiftSwapRequest $swapRequest,
        public string $responseType
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
        $shift = $swap->requestingShift;
        $date = $shift->date->format('l, F j, Y');
        $targetUser = $swap->targetUser;

        $subject = match ($this->responseType) {
            'accepted' => 'Shift Swap Accepted',
            'rejected' => 'Shift Swap Rejected',
            'admin_approved' => 'Shift Swap Approved & Executed',
            'cancelled' => 'Shift Swap Cancelled',
            default => 'Shift Swap Update',
        };

        $message = match ($this->responseType) {
            'accepted' => "{$targetUser->full_name} has accepted your swap request for your shift on {$date}.",
            'rejected' => "{$targetUser->full_name} has declined your swap request for your shift on {$date}.",
            'admin_approved' => "Your shift swap for {$date} has been approved by an admin. The swap has been executed.",
            'cancelled' => "The swap request for your shift on {$date} has been cancelled.",
            default => "There's an update on your swap request for {$date}.",
        };

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->first_name}!")
            ->line($message);

        if ($this->responseType === 'accepted') {
            $mailMessage->line('The swap is now pending admin approval.');
        }

        return $mailMessage
            ->action('View Swap Requests', url('/shift-swaps'))
            ->line('Thank you for using our scheduling system.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $swap = $this->swapRequest;
        $shift = $swap->requestingShift;
        $targetUser = $swap->targetUser;

        $message = match ($this->responseType) {
            'accepted' => "{$targetUser->full_name} accepted your swap request for {$shift->date->format('M j')}.",
            'rejected' => "{$targetUser->full_name} declined your swap request for {$shift->date->format('M j')}.",
            'admin_approved' => "Your shift swap for {$shift->date->format('M j')} has been approved and executed.",
            'cancelled' => "The swap request for {$shift->date->format('M j')} was cancelled.",
            default => "Update on your swap request for {$shift->date->format('M j')}.",
        };

        return [
            'type' => 'swap_request_response',
            'swap_request_id' => $swap->id,
            'response_type' => $this->responseType,
            'target_user_id' => $targetUser->id,
            'target_user_name' => $targetUser->full_name,
            'shift_date' => $shift->date->format('Y-m-d'),
            'message' => $message,
        ];
    }
}
