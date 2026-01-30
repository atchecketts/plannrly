<?php

namespace App\Console\Commands;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\Tenant;
use App\Notifications\ShiftReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendShiftRemindersCommand extends Command
{
    protected $signature = 'shifts:send-reminders';

    protected $description = 'Send reminders for upcoming shifts';

    public function handle(): int
    {
        $this->info('Starting shift reminder check...');

        $tenants = Tenant::where('is_active', true)->get();
        $totalReminders = 0;

        foreach ($tenants as $tenant) {
            $settings = $tenant->tenantSettings;

            if (! $settings || ! $settings->enable_shift_reminders) {
                continue;
            }

            $timezone = $settings->timezone ?? 'UTC';
            $now = Carbon::now($timezone);

            // Send day-before reminders (shifts tomorrow)
            if ($settings->remind_day_before) {
                $tomorrow = $now->copy()->addDay()->startOfDay();
                $tomorrowEnd = $tomorrow->copy()->endOfDay();

                $shifts = Shift::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', ShiftStatus::Published)
                    ->whereNotNull('user_id')
                    ->whereBetween('date', [$tomorrow, $tomorrowEnd])
                    ->whereNull('reminder_sent_at')
                    ->with(['user', 'location', 'businessRole'])
                    ->get();

                foreach ($shifts as $shift) {
                    if ($shift->user) {
                        $shift->user->notify(new ShiftReminderNotification($shift, 'day_before'));
                        $shift->update(['reminder_sent_at' => now()]);
                        $totalReminders++;
                    }
                }
            }

            // Send hour-before reminders (shifts starting within the next hour)
            if ($settings->remind_hours_before) {
                $hoursBeforeValue = $settings->remind_hours_before_value ?? 1;
                $reminderTime = $now->copy()->addHours($hoursBeforeValue);

                // Get shifts that start within the reminder window
                $shifts = Shift::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', ShiftStatus::Published)
                    ->whereNotNull('user_id')
                    ->whereDate('date', $now->toDateString())
                    ->whereNull('hour_reminder_sent_at')
                    ->with(['user', 'location', 'businessRole'])
                    ->get()
                    ->filter(function (Shift $shift) use ($now, $timezone, $hoursBeforeValue) {
                        $shiftStart = Carbon::parse(
                            $shift->date->format('Y-m-d').' '.$shift->start_time->format('H:i:s'),
                            $timezone
                        );
                        $hoursUntilShift = $now->diffInHours($shiftStart, false);

                        // Send reminder if shift starts within the reminder window (e.g., 1 hour)
                        return $hoursUntilShift > 0 && $hoursUntilShift <= $hoursBeforeValue;
                    });

                foreach ($shifts as $shift) {
                    if ($shift->user) {
                        $shift->user->notify(new ShiftReminderNotification($shift, 'hour_before'));
                        $shift->update(['hour_reminder_sent_at' => now()]);
                        $totalReminders++;
                    }
                }
            }
        }

        $this->info("Shift reminder check complete. Sent {$totalReminders} reminders.");

        return Command::SUCCESS;
    }
}
