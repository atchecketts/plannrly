<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest
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
        $request = $this->leaveRequest;
        $status = $request->status->label();
        $leaveType = $request->leaveType?->name ?? 'Leave';
        $startDate = $request->start_date->format('l, F j, Y');
        $endDate = $request->end_date->format('l, F j, Y');
        $isApproved = $request->isApproved();

        $subject = $isApproved ? 'Leave Request Approved' : 'Leave Request Rejected';

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->first_name}!");

        if ($isApproved) {
            $mailMessage->line("Great news! Your {$leaveType} request has been approved.");
        } else {
            $mailMessage->line("Your {$leaveType} request has been rejected.");
        }

        $mailMessage
            ->line('**Leave Details:**')
            ->line("- Type: {$leaveType}")
            ->line("- From: {$startDate}")
            ->line("- To: {$endDate}")
            ->line("- Total Days: {$request->total_days}");

        if ($request->review_notes) {
            $mailMessage->line("**Reviewer Notes:** {$request->review_notes}");
        }

        return $mailMessage
            ->action('View Leave Requests', url('/leave-requests'))
            ->line('Thank you for your patience.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $request = $this->leaveRequest;
        $isApproved = $request->isApproved();
        $leaveType = $request->leaveType?->name ?? 'Leave';

        return [
            'type' => 'leave_request_status',
            'leave_request_id' => $request->id,
            'status' => $request->status->value,
            'leave_type' => $leaveType,
            'start_date' => $request->start_date->format('Y-m-d'),
            'end_date' => $request->end_date->format('Y-m-d'),
            'total_days' => $request->total_days,
            'message' => $isApproved
                ? "Your {$leaveType} request ({$request->start_date->format('M j')} - {$request->end_date->format('M j')}) has been approved."
                : "Your {$leaveType} request ({$request->start_date->format('M j')} - {$request->end_date->format('M j')}) has been rejected.",
        ];
    }
}
