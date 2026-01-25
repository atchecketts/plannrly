<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantSettings>
 */
class TenantSettingsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'enable_clock_in_out' => false,
            'enable_shift_acknowledgement' => false,
            'day_starts_at' => '00:00:00',
            'day_ends_at' => '23:59:59',
            'week_starts_on' => 1,
            'timezone' => fake()->timezone(),
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
        ];
    }
}
