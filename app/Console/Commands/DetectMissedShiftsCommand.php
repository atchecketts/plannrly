<?php

namespace App\Console\Commands;

use App\Enums\ShiftStatus;
use App\Enums\SystemRole;
use App\Enums\TimeEntryStatus;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use App\Notifications\MissedShiftNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DetectMissedShiftsCommand extends Command
{
    protected $signature = 'attendance:detect-missed-shifts';

    protected $description = 'Detect shifts where employees did not clock in and create missed time entries';

    public function handle(): int
    {
        $this->info('Detecting missed shifts...');

        $missedCount = 0;
        $notificationCount = 0;

        // Process each tenant separately to respect their settings
        $tenants = Tenant::with('tenantSettings')->get();

        foreach ($tenants as $tenant) {
            // Only process if clock in/out is enabled
            if (! $tenant->tenantSettings?->enable_clock_in_out) {
                continue;
            }

            $timezone = $tenant->tenantSettings->timezone ?? 'UTC';
            $now = Carbon::now($timezone);
            $today = $now->toDateString();
            $graceMinutes = $tenant->tenantSettings->missed_grace_minutes ?? 15;

            // Find published, assigned shifts for today that:
            // - Have passed the grace period
            // - Don't have a time entry yet
            // Use withoutGlobalScopes to avoid tenant scope issues in console context
            $missedShifts = Shift::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('status', ShiftStatus::Published)
                ->whereDate('date', $today)
                ->whereNotNull('user_id')
                ->whereDoesntHave('timeEntry')
                ->with(['user', 'department', 'location', 'businessRole', 'tenant'])
                ->get()
                ->filter(function (Shift $shift) use ($now, $timezone, $graceMinutes) {
                    $startDateTime = Carbon::parse(
                        $shift->date->format('Y-m-d').' '.$shift->start_time->format('H:i:s'),
                        $timezone
                    );
                    $missedThreshold = $startDateTime->copy()->addMinutes($graceMinutes);

                    return $now->gte($missedThreshold);
                });

            foreach ($missedShifts as $shift) {
                // Create a missed time entry
                TimeEntry::create([
                    'tenant_id' => $shift->tenant_id,
                    'user_id' => $shift->user_id,
                    'shift_id' => $shift->id,
                    'status' => TimeEntryStatus::Missed,
                    'notes' => 'Auto-detected: Employee did not clock in within grace period.',
                ]);

                $missedCount++;

                // Notify managers
                $managers = $this->getManagersForShift($shift);
                foreach ($managers as $manager) {
                    $manager->notify(new MissedShiftNotification($shift, $shift->user));
                    $notificationCount++;
                }
            }
        }

        $this->info("Missed shifts detected: {$missedCount}");
        $this->info("Notifications sent: {$notificationCount}");

        return Command::SUCCESS;
    }

    /**
     * Get managers who should be notified about a missed shift.
     */
    protected function getManagersForShift(Shift $shift): array
    {
        $managers = collect();

        // Get department admins if shift has a department
        if ($shift->department_id) {
            $departmentAdmins = User::whereHas('roleAssignments', function ($query) use ($shift) {
                $query->where('system_role', SystemRole::DepartmentAdmin)
                    ->where('department_id', $shift->department_id);
            })->where('tenant_id', $shift->tenant_id)->get();

            $managers = $managers->merge($departmentAdmins);
        }

        // Get location admins if shift has a location
        if ($shift->location_id) {
            $locationAdmins = User::whereHas('roleAssignments', function ($query) use ($shift) {
                $query->where('system_role', SystemRole::LocationAdmin)
                    ->where('location_id', $shift->location_id);
            })->where('tenant_id', $shift->tenant_id)->get();

            $managers = $managers->merge($locationAdmins);
        }

        // Get tenant admins
        $tenantAdmins = User::whereHas('roleAssignments', function ($query) {
            $query->where('system_role', SystemRole::Admin);
        })->where('tenant_id', $shift->tenant_id)->get();

        $managers = $managers->merge($tenantAdmins);

        // Remove duplicates by user ID
        return $managers->unique('id')->values()->all();
    }
}
