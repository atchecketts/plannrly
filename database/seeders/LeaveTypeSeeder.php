<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTypes = [
            [
                'name' => 'Annual Leave',
                'color' => '#3B82F6',
                'requires_approval' => true,
                'affects_allowance' => true,
                'is_paid' => true,
            ],
            [
                'name' => 'Sick Leave',
                'color' => '#EF4444',
                'requires_approval' => true,
                'affects_allowance' => false,
                'is_paid' => true,
            ],
            [
                'name' => 'Unpaid Leave',
                'color' => '#6B7280',
                'requires_approval' => true,
                'affects_allowance' => false,
                'is_paid' => false,
            ],
            [
                'name' => 'Maternity/Paternity Leave',
                'color' => '#8B5CF6',
                'requires_approval' => true,
                'affects_allowance' => false,
                'is_paid' => true,
            ],
            [
                'name' => 'Bereavement Leave',
                'color' => '#374151',
                'requires_approval' => true,
                'affects_allowance' => false,
                'is_paid' => true,
            ],
            [
                'name' => 'Jury Duty',
                'color' => '#F59E0B',
                'requires_approval' => false,
                'affects_allowance' => false,
                'is_paid' => true,
            ],
        ];

        foreach ($defaultTypes as $type) {
            LeaveType::firstOrCreate(
                [
                    'tenant_id' => null,
                    'name' => $type['name'],
                ],
                array_merge($type, ['is_active' => true])
            );
        }
    }
}
