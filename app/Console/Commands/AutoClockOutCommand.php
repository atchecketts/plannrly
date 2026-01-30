<?php

namespace App\Console\Commands;

use App\Enums\TimeEntryStatus;
use App\Models\Tenant;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoClockOutCommand extends Command
{
    protected $signature = 'attendance:auto-clock-out';

    protected $description = 'Automatically clock out employees who are still clocked in past the configured auto clock-out time';

    public function handle(): int
    {
        $this->info('Running auto clock-out...');

        $autoClockOutCount = 0;

        // Process each tenant with auto clock-out enabled
        Tenant::with('tenantSettings')
            ->whereHas('tenantSettings', function ($query) {
                $query->where('auto_clock_out_enabled', true)
                    ->where('enable_clock_in_out', true);
            })
            ->each(function (Tenant $tenant) use (&$autoClockOutCount) {
                $settings = $tenant->tenantSettings;
                $timezone = $settings->timezone ?? 'UTC';
                $now = Carbon::now($timezone);

                $autoClockOutTime = $settings->auto_clock_out_time;
                if (! $autoClockOutTime) {
                    return;
                }

                // Parse the auto clock-out time for today
                $clockOutDateTime = Carbon::parse($now->format('Y-m-d').' '.$autoClockOutTime, $timezone);

                // Only process if we've passed the auto clock-out time
                if ($now->lt($clockOutDateTime)) {
                    return;
                }

                // Find all active time entries (clocked_in or on_break) for this tenant
                $activeEntries = TimeEntry::where('tenant_id', $tenant->id)
                    ->whereIn('status', [TimeEntryStatus::ClockedIn, TimeEntryStatus::OnBreak])
                    ->whereNull('clock_out_at')
                    ->get();

                foreach ($activeEntries as $entry) {
                    // Auto clock-out at the configured time
                    $entry->update([
                        'clock_out_at' => $clockOutDateTime,
                        'status' => TimeEntryStatus::AutoClockedOut,
                        'notes' => $this->appendNote(
                            $entry->notes,
                            'Automatically clocked out by system at '.$clockOutDateTime->format('H:i').'.'
                        ),
                    ]);

                    // If they were on break, end the break too
                    if ($entry->break_start_at && ! $entry->break_end_at) {
                        $entry->update([
                            'break_end_at' => $clockOutDateTime,
                        ]);
                    }

                    $autoClockOutCount++;
                }
            });

        $this->info("Auto clocked out: {$autoClockOutCount} entries");

        return Command::SUCCESS;
    }

    /**
     * Append a note to existing notes.
     */
    protected function appendNote(?string $existingNotes, string $newNote): string
    {
        if (empty($existingNotes)) {
            return $newNote;
        }

        return $existingNotes."\n".$newNote;
    }
}
