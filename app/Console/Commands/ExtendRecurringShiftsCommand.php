<?php

namespace App\Console\Commands;

use App\Services\RecurringShiftService;
use Illuminate\Console\Command;

class ExtendRecurringShiftsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shifts:extend-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extend recurring shifts that are approaching their generation window end';

    /**
     * Execute the console command.
     */
    public function handle(RecurringShiftService $recurringService): int
    {
        $this->info('Extending recurring shifts...');

        $extendedCount = $recurringService->extendRecurringShifts();

        if ($extendedCount > 0) {
            $this->info("Created {$extendedCount} new recurring shift instances.");
        } else {
            $this->info('No recurring shifts needed extension.');
        }

        return Command::SUCCESS;
    }
}
