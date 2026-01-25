<?php

namespace App\Console\Commands;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateShiftStatusesCommand extends Command
{
    protected $signature = 'shifts:update-statuses';

    protected $description = 'Automatically update shift statuses based on time (Published→InProgress, InProgress→Completed, Published→Missed)';

    public function handle(): int
    {
        $this->info('Updating shift statuses...');

        $publishedToInProgress = 0;
        $inProgressToCompleted = 0;
        $publishedToMissed = 0;

        // Process each tenant separately to respect their settings
        Tenant::with('tenantSettings')->each(function (Tenant $tenant) use (&$publishedToInProgress, &$inProgressToCompleted, &$publishedToMissed) {
            $timezone = $tenant->tenantSettings?->timezone ?? 'UTC';
            $now = Carbon::now($timezone);
            $today = $now->toDateString();

            // Published → InProgress: when start_time has passed
            $toInProgress = Shift::where('tenant_id', $tenant->id)
                ->where('status', ShiftStatus::Published)
                ->where('date', $today)
                ->get()
                ->filter(function (Shift $shift) use ($now, $timezone) {
                    $startDateTime = Carbon::parse($shift->date->format('Y-m-d').' '.$shift->start_time->format('H:i:s'), $timezone);

                    return $now->gte($startDateTime);
                });

            foreach ($toInProgress as $shift) {
                $shift->update(['status' => ShiftStatus::InProgress]);
                $publishedToInProgress++;
            }

            // InProgress → Completed: when end_time has passed
            $toCompleted = Shift::where('tenant_id', $tenant->id)
                ->where('status', ShiftStatus::InProgress)
                ->where('date', $today)
                ->get()
                ->filter(function (Shift $shift) use ($now, $timezone) {
                    $endDateTime = Carbon::parse($shift->date->format('Y-m-d').' '.$shift->end_time->format('H:i:s'), $timezone);

                    return $now->gte($endDateTime);
                });

            foreach ($toCompleted as $shift) {
                $shift->update(['status' => ShiftStatus::Completed]);
                $inProgressToCompleted++;
            }

            // Published → Missed: only if clock_in_out is enabled
            if ($tenant->tenantSettings?->enable_clock_in_out) {
                $graceMinutes = $tenant->tenantSettings->missed_grace_minutes ?? 15;

                $toMissed = Shift::where('tenant_id', $tenant->id)
                    ->where('status', ShiftStatus::Published)
                    ->where('date', $today)
                    ->whereNotNull('user_id') // Only assigned shifts can be missed
                    ->whereDoesntHave('timeEntry') // No clock-in recorded
                    ->get()
                    ->filter(function (Shift $shift) use ($now, $timezone, $graceMinutes) {
                        $startDateTime = Carbon::parse($shift->date->format('Y-m-d').' '.$shift->start_time->format('H:i:s'), $timezone);
                        $missedThreshold = $startDateTime->addMinutes($graceMinutes);

                        return $now->gte($missedThreshold);
                    });

                foreach ($toMissed as $shift) {
                    $shift->update(['status' => ShiftStatus::Missed]);
                    $publishedToMissed++;
                }
            }
        });

        $this->info("Published → InProgress: {$publishedToInProgress}");
        $this->info("InProgress → Completed: {$inProgressToCompleted}");
        $this->info("Published → Missed: {$publishedToMissed}");

        return Command::SUCCESS;
    }
}
