<?php

namespace Database\Seeders;

use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $plannrly = Tenant::firstOrCreate(
            ['slug' => 'plannrly'],
            [
                'name' => 'Plannrly',
                'email' => 'admin@plannrly.com',
                'phone' => null,
                'address' => null,
                'settings' => [
                    'timezone' => 'UTC',
                    'date_format' => 'Y-m-d',
                    'time_format' => 'H:i',
                ],
                'is_active' => true,
            ]
        );

        $superAdmin = User::firstOrCreate(
            ['email' => 'atchecketts@gmail.com'],
            [
                'tenant_id' => $plannrly->id,
                'first_name' => 'Adrian',
                'last_name' => 'SuperAdmin',
                'password' => Hash::make('Holly952179'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        UserRoleAssignment::firstOrCreate(
            [
                'user_id' => $superAdmin->id,
                'system_role' => SystemRole::SuperAdmin->value,
            ],
            [
                'location_id' => null,
                'department_id' => null,
                'assigned_by' => null,
            ]
        );
    }
}
