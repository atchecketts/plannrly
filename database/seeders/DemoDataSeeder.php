<?php

namespace Database\Seeders;

use App\Enums\ShiftStatus;
use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserBusinessRole;
use App\Models\UserRoleAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::create([
            'name' => 'Demo Coffee Shop',
            'slug' => 'demo-coffee-shop',
            'email' => 'demo@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main Street, Demo City',
            'settings' => [
                'timezone' => 'America/New_York',
                'date_format' => 'M d, Y',
                'time_format' => 'h:i A',
            ],
            'is_active' => true,
            'trial_ends_at' => now()->addDays(14),
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'John',
            'last_name' => 'Admin',
            'email' => 'admin@demo.com',
            'phone' => '+1234567891',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        UserRoleAssignment::create([
            'user_id' => $admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $downtown = Location::create([
            'tenant_id' => $tenant->id,
            'name' => 'Downtown Branch',
            'address_line_1' => '100 Main Street',
            'city' => 'Demo City',
            'state' => 'DC',
            'postal_code' => '12345',
            'country' => 'USA',
            'timezone' => 'America/New_York',
            'is_active' => true,
        ]);

        $suburban = Location::create([
            'tenant_id' => $tenant->id,
            'name' => 'Suburban Mall',
            'address_line_1' => '500 Mall Road',
            'city' => 'Demo City',
            'state' => 'DC',
            'postal_code' => '12346',
            'country' => 'USA',
            'timezone' => 'America/New_York',
            'is_active' => true,
        ]);

        $locationAdmin = User::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Sarah',
            'last_name' => 'Manager',
            'email' => 'sarah@demo.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        UserRoleAssignment::create([
            'user_id' => $locationAdmin->id,
            'system_role' => SystemRole::LocationAdmin->value,
            'location_id' => $downtown->id,
        ]);

        $frontDesk = Department::create([
            'tenant_id' => $tenant->id,
            'location_id' => $downtown->id,
            'name' => 'Front Counter',
            'description' => 'Customer service and order taking',
            'color' => '#3B82F6',
            'is_active' => true,
        ]);

        $kitchen = Department::create([
            'tenant_id' => $tenant->id,
            'location_id' => $downtown->id,
            'name' => 'Kitchen',
            'description' => 'Food and beverage preparation',
            'color' => '#10B981',
            'is_active' => true,
        ]);

        $deptAdmin = User::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Mike',
            'last_name' => 'Lead',
            'email' => 'mike@demo.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        UserRoleAssignment::create([
            'user_id' => $deptAdmin->id,
            'system_role' => SystemRole::DepartmentAdmin->value,
            'location_id' => $downtown->id,
            'department_id' => $frontDesk->id,
        ]);

        $barista = BusinessRole::create([
            'tenant_id' => $tenant->id,
            'department_id' => $frontDesk->id,
            'name' => 'Barista',
            'description' => 'Coffee preparation and customer service',
            'color' => '#8B5CF6',
            'default_hourly_rate' => 15.00,
            'is_active' => true,
        ]);

        $cashier = BusinessRole::create([
            'tenant_id' => $tenant->id,
            'department_id' => $frontDesk->id,
            'name' => 'Cashier',
            'description' => 'Order taking and payment processing',
            'color' => '#F59E0B',
            'default_hourly_rate' => 13.50,
            'is_active' => true,
        ]);

        $cook = BusinessRole::create([
            'tenant_id' => $tenant->id,
            'department_id' => $kitchen->id,
            'name' => 'Cook',
            'description' => 'Food preparation',
            'color' => '#EF4444',
            'default_hourly_rate' => 16.00,
            'is_active' => true,
        ]);

        $employees = [];
        $employeeData = [
            ['Jane', 'Doe', 'jane@demo.com', $barista],
            ['Bob', 'Smith', 'bob@demo.com', $cashier],
            ['Alice', 'Johnson', 'alice@demo.com', $barista],
            ['Charlie', 'Brown', 'charlie@demo.com', $cook],
            ['Diana', 'Wilson', 'diana@demo.com', $cashier],
        ];

        foreach ($employeeData as [$first, $last, $email, $role]) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            UserRoleAssignment::create([
                'user_id' => $user->id,
                'system_role' => SystemRole::Employee->value,
            ]);

            UserBusinessRole::create([
                'user_id' => $user->id,
                'business_role_id' => $role->id,
                'is_primary' => true,
            ]);

            $employees[] = $user;
        }

        $startOfWeek = Carbon::now()->startOfWeek();

        $shifts = [
            [$employees[0], $barista, '06:00', '14:00'],
            [$employees[1], $cashier, '06:00', '14:00'],
            [$employees[2], $barista, '14:00', '22:00'],
            [$employees[3], $cook, '06:00', '14:00'],
            [$employees[4], $cashier, '14:00', '22:00'],
        ];

        for ($day = 0; $day < 7; $day++) {
            $date = $startOfWeek->copy()->addDays($day);

            if ($day < 5) {
                foreach ($shifts as [$user, $role, $start, $end]) {
                    Shift::create([
                        'tenant_id' => $tenant->id,
                        'location_id' => $downtown->id,
                        'department_id' => $role->department_id,
                        'business_role_id' => $role->id,
                        'user_id' => $user->id,
                        'date' => $date,
                        'start_time' => $start,
                        'end_time' => $end,
                        'break_duration_minutes' => 30,
                        'status' => ShiftStatus::Published,
                        'created_by' => $admin->id,
                    ]);
                }
            }

            Shift::create([
                'tenant_id' => $tenant->id,
                'location_id' => $downtown->id,
                'department_id' => $frontDesk->id,
                'business_role_id' => $barista->id,
                'user_id' => null,
                'date' => $date,
                'start_time' => '10:00',
                'end_time' => '18:00',
                'break_duration_minutes' => 30,
                'status' => ShiftStatus::Published,
                'created_by' => $admin->id,
            ]);
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Admin login: admin@demo.com / password');
        $this->command->info('Location Admin: sarah@demo.com / password');
        $this->command->info('Department Admin: mike@demo.com / password');
        $this->command->info('Employee: jane@demo.com / password');
    }
}
