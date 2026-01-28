<?php

namespace Database\Seeders;

use App\Enums\AvailabilityType;
use App\Enums\EmploymentStatus;
use App\Enums\PayType;
use App\Enums\PreferenceLevel;
use App\Enums\ShiftStatus;
use App\Enums\SystemRole;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserAvailability;
use App\Models\UserBusinessRole;
use App\Models\UserEmploymentDetails;
use App\Models\UserRoleAssignment;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private array $usedEmails = [];

    private int $employeeCounter = 0;

    private \Faker\Generator $faker;

    public function run(): void
    {
        $this->faker = Faker::create();
        $this->seedHotelChain();
        $this->seedMedicalFacility();
        $this->seedLogisticsWarehouse();

        $this->command->info('Demo data seeded successfully!');
        $this->command->newLine();
        $this->command->info('=== Hotel Chain (Grand Horizon Hotels) ===');
        $this->command->info('Admin: admin@grandhorizon.com / password');
        $this->command->newLine();
        $this->command->info('=== Medical Facility (Westside Medical Center) ===');
        $this->command->info('Admin: admin@westsidemedical.com / password');
        $this->command->newLine();
        $this->command->info('=== Logistics Warehouse (Swift Logistics) ===');
        $this->command->info('Admin: admin@swiftlogistics.com / password');
    }

    private function seedHotelChain(): void
    {
        $tenant = Tenant::create([
            'name' => 'Grand Horizon Hotels',
            'slug' => 'grand-horizon-hotels',
            'email' => 'info@grandhorizon.com',
            'phone' => '+1 (555) 100-0000',
            'address' => '1 Corporate Plaza, New York, NY 10001',
            'settings' => [
                'timezone' => 'America/New_York',
                'date_format' => 'M d, Y',
                'time_format' => 'h:i A',
            ],
            'is_active' => true,
            'trial_ends_at' => now()->addDays(30),
        ]);

        $admin = $this->createUser($tenant, 'Marcus', 'Reynolds', 'admin@grandhorizon.com');
        $this->assignSystemRole($admin, SystemRole::Admin);

        $locations = [
            [
                'name' => 'Grand Horizon Downtown',
                'address_line_1' => '500 Park Avenue',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10022',
                'country' => 'USA',
                'timezone' => 'America/New_York',
                'has_location_admin' => true,
            ],
            [
                'name' => 'Grand Horizon Beachfront',
                'address_line_1' => '1200 Ocean Drive',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33139',
                'country' => 'USA',
                'timezone' => 'America/New_York',
                'has_location_admin' => true,
            ],
            [
                'name' => 'Grand Horizon Resort & Spa',
                'address_line_1' => '8800 Las Vegas Blvd',
                'city' => 'Las Vegas',
                'state' => 'NV',
                'postal_code' => '89109',
                'country' => 'USA',
                'timezone' => 'America/Los_Angeles',
                'has_location_admin' => true,
            ],
            [
                'name' => 'Grand Horizon Airport',
                'address_line_1' => '1 Terminal Way',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60666',
                'country' => 'USA',
                'timezone' => 'America/Chicago',
                'has_location_admin' => true,
            ],
            [
                'name' => 'Grand Horizon Convention Center',
                'address_line_1' => '950 Convention Blvd',
                'city' => 'San Diego',
                'state' => 'CA',
                'postal_code' => '92101',
                'country' => 'USA',
                'timezone' => 'America/Los_Angeles',
                'has_location_admin' => false, // No location admin - managed by regional admin
            ],
            [
                'name' => 'Grand Horizon Mountain Lodge',
                'address_line_1' => '200 Ski Valley Road',
                'city' => 'Aspen',
                'state' => 'CO',
                'postal_code' => '81611',
                'country' => 'USA',
                'timezone' => 'America/Denver',
                'has_location_admin' => true,
            ],
            [
                'name' => 'Grand Horizon Waterfront',
                'address_line_1' => '75 Harbor Drive',
                'city' => 'Seattle',
                'state' => 'WA',
                'postal_code' => '98101',
                'country' => 'USA',
                'timezone' => 'America/Los_Angeles',
                'has_location_admin' => false, // No location admin - newer property
            ],
        ];

        $departmentConfigs = $this->getHotelDepartments();

        foreach ($locations as $locationData) {
            $hasLocationAdmin = $locationData['has_location_admin'] ?? true;
            unset($locationData['has_location_admin']);

            $location = Location::create([
                'tenant_id' => $tenant->id,
                ...$locationData,
                'is_active' => true,
            ]);

            if ($hasLocationAdmin) {
                $locationAdmin = $this->createUser(
                    $tenant,
                    $this->faker->firstName(),
                    $this->faker->lastName(),
                    $this->generateEmail('manager', $locationData['city'])
                );
                $this->assignSystemRole($locationAdmin, SystemRole::LocationAdmin, $location);
            }

            foreach ($departmentConfigs as $deptConfig) {
                $department = Department::create([
                    'tenant_id' => $tenant->id,
                    'location_id' => $location->id,
                    'name' => $deptConfig['name'],
                    'description' => $deptConfig['description'],
                    'color' => $deptConfig['color'],
                    'is_active' => true,
                ]);

                $deptAdmin = $this->createUser(
                    $tenant,
                    $this->faker->firstName(),
                    $this->faker->lastName(),
                    $this->generateEmail(Str::slug($deptConfig['name']).'-lead', $locationData['city'])
                );
                $this->assignSystemRole($deptAdmin, SystemRole::DepartmentAdmin, $location, $department);

                $roles = [];
                foreach ($deptConfig['roles'] as $roleConfig) {
                    $roles[] = BusinessRole::create([
                        'tenant_id' => $tenant->id,
                        'department_id' => $department->id,
                        'name' => $roleConfig['name'],
                        'description' => $roleConfig['description'],
                        'color' => $roleConfig['color'],
                        'default_hourly_rate' => $roleConfig['rate'],
                        'is_active' => true,
                    ]);
                }

                $this->createEmployeesForDepartment($tenant, $department, $roles, rand(4, 8));
            }
        }
    }

    private function seedMedicalFacility(): void
    {
        $tenant = Tenant::create([
            'name' => 'Westside Medical Center',
            'slug' => 'westside-medical-center',
            'email' => 'info@westsidemedical.com',
            'phone' => '+1 (555) 200-0000',
            'address' => '2500 Medical Center Drive, Chicago, IL 60612',
            'settings' => [
                'timezone' => 'America/Chicago',
                'date_format' => 'M d, Y',
                'time_format' => 'h:i A',
            ],
            'is_active' => true,
            'trial_ends_at' => now()->addDays(30),
        ]);

        $admin = $this->createUser($tenant, 'Dr. Elizabeth', 'Chen', 'admin@westsidemedical.com');
        $this->assignSystemRole($admin, SystemRole::Admin);

        $location = Location::create([
            'tenant_id' => $tenant->id,
            'name' => 'Westside Medical Center - Main Campus',
            'address_line_1' => '2500 Medical Center Drive',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60612',
            'country' => 'USA',
            'timezone' => 'America/Chicago',
            'is_active' => true,
        ]);

        $locationAdmin = $this->createUser($tenant, 'James', 'Morrison', 'operations@westsidemedical.com');
        $this->assignSystemRole($locationAdmin, SystemRole::LocationAdmin, $location);

        $departmentConfigs = $this->getMedicalDepartments();

        foreach ($departmentConfigs as $deptConfig) {
            $department = Department::create([
                'tenant_id' => $tenant->id,
                'location_id' => $location->id,
                'name' => $deptConfig['name'],
                'description' => $deptConfig['description'],
                'color' => $deptConfig['color'],
                'is_active' => true,
            ]);

            $deptAdmin = $this->createUser(
                $tenant,
                $this->faker->firstName(),
                $this->faker->lastName(),
                $this->generateEmail(Str::slug($deptConfig['name']).'-supervisor', 'westside')
            );
            $this->assignSystemRole($deptAdmin, SystemRole::DepartmentAdmin, $location, $department);

            $roles = [];
            foreach ($deptConfig['roles'] as $roleConfig) {
                $roles[] = BusinessRole::create([
                    'tenant_id' => $tenant->id,
                    'department_id' => $department->id,
                    'name' => $roleConfig['name'],
                    'description' => $roleConfig['description'],
                    'color' => $roleConfig['color'],
                    'default_hourly_rate' => $roleConfig['rate'],
                    'is_active' => true,
                ]);
            }

            $this->createEmployeesForDepartment($tenant, $department, $roles, rand(4, 8));
        }
    }

    private function seedLogisticsWarehouse(): void
    {
        $tenant = Tenant::create([
            'name' => 'Swift Logistics',
            'slug' => 'swift-logistics',
            'email' => 'info@swiftlogistics.com',
            'phone' => '+1 (555) 300-0000',
            'address' => '7500 Industrial Parkway, Dallas, TX 75247',
            'settings' => [
                'timezone' => 'America/Chicago',
                'date_format' => 'M d, Y',
                'time_format' => 'h:i A',
            ],
            'is_active' => true,
            'trial_ends_at' => now()->addDays(30),
        ]);

        $admin = $this->createUser($tenant, 'Robert', 'Thornton', 'admin@swiftlogistics.com');
        $this->assignSystemRole($admin, SystemRole::Admin);

        $location = Location::create([
            'tenant_id' => $tenant->id,
            'name' => 'Swift Logistics Distribution Center',
            'address_line_1' => '7500 Industrial Parkway',
            'city' => 'Dallas',
            'state' => 'TX',
            'postal_code' => '75247',
            'country' => 'USA',
            'timezone' => 'America/Chicago',
            'is_active' => true,
        ]);

        $locationAdmin = $this->createUser($tenant, 'Patricia', 'Vega', 'operations@swiftlogistics.com');
        $this->assignSystemRole($locationAdmin, SystemRole::LocationAdmin, $location);

        $departmentConfigs = $this->getLogisticsDepartments();

        foreach ($departmentConfigs as $deptConfig) {
            $department = Department::create([
                'tenant_id' => $tenant->id,
                'location_id' => $location->id,
                'name' => $deptConfig['name'],
                'description' => $deptConfig['description'],
                'color' => $deptConfig['color'],
                'is_active' => true,
            ]);

            $deptAdmin = $this->createUser(
                $tenant,
                $this->faker->firstName(),
                $this->faker->lastName(),
                $this->generateEmail(Str::slug($deptConfig['name']).'-lead', 'swift')
            );
            $this->assignSystemRole($deptAdmin, SystemRole::DepartmentAdmin, $location, $department);

            $roles = [];
            foreach ($deptConfig['roles'] as $roleConfig) {
                $roles[] = BusinessRole::create([
                    'tenant_id' => $tenant->id,
                    'department_id' => $department->id,
                    'name' => $roleConfig['name'],
                    'description' => $roleConfig['description'],
                    'color' => $roleConfig['color'],
                    'default_hourly_rate' => $roleConfig['rate'],
                    'is_active' => true,
                ]);
            }

            $this->createEmployeesForDepartment($tenant, $department, $roles, rand(4, 8));
        }
    }

    private function getHotelDepartments(): array
    {
        return [
            [
                'name' => 'Reception',
                'description' => 'Front desk operations and guest services',
                'color' => '#3B82F6',
                'roles' => [
                    ['name' => 'Front Desk Agent', 'description' => 'Check-in/out, reservations, guest inquiries', 'color' => '#60A5FA', 'rate' => 16.00],
                    ['name' => 'Concierge', 'description' => 'Guest assistance, recommendations, bookings', 'color' => '#2563EB', 'rate' => 18.00],
                    ['name' => 'Night Auditor', 'description' => 'Overnight front desk and daily reconciliation', 'color' => '#1D4ED8', 'rate' => 17.50],
                ],
            ],
            [
                'name' => 'Housekeeping',
                'description' => 'Room cleaning and maintenance of cleanliness standards',
                'color' => '#10B981',
                'roles' => [
                    ['name' => 'Room Attendant', 'description' => 'Guest room cleaning and turndown service', 'color' => '#34D399', 'rate' => 14.00],
                    ['name' => 'Housekeeping Supervisor', 'description' => 'Oversee housekeeping staff and inspect rooms', 'color' => '#059669', 'rate' => 18.00],
                    ['name' => 'Laundry Attendant', 'description' => 'Linens processing and uniform care', 'color' => '#047857', 'rate' => 13.50],
                ],
            ],
            [
                'name' => 'Restaurant',
                'description' => 'Hotel dining and room service operations',
                'color' => '#F59E0B',
                'roles' => [
                    ['name' => 'Server', 'description' => 'Food and beverage service to guests', 'color' => '#FBBF24', 'rate' => 12.00],
                    ['name' => 'Host/Hostess', 'description' => 'Guest seating and reservations management', 'color' => '#F59E0B', 'rate' => 13.00],
                    ['name' => 'Food Runner', 'description' => 'Deliver food from kitchen to tables', 'color' => '#D97706', 'rate' => 11.50],
                    ['name' => 'Room Service Attendant', 'description' => 'In-room dining delivery and setup', 'color' => '#B45309', 'rate' => 13.00],
                ],
            ],
            [
                'name' => 'Bar',
                'description' => 'Beverage service and lounge operations',
                'color' => '#8B5CF6',
                'roles' => [
                    ['name' => 'Bartender', 'description' => 'Craft cocktails and beverage service', 'color' => '#A78BFA', 'rate' => 15.00],
                    ['name' => 'Barback', 'description' => 'Bar setup, stocking, and support', 'color' => '#7C3AED', 'rate' => 12.00],
                ],
            ],
            [
                'name' => 'Kitchen',
                'description' => 'Food preparation and culinary operations',
                'color' => '#EF4444',
                'roles' => [
                    ['name' => 'Executive Chef', 'description' => 'Menu development and kitchen management', 'color' => '#F87171', 'rate' => 32.00],
                    ['name' => 'Sous Chef', 'description' => 'Assistant to executive chef, line supervision', 'color' => '#EF4444', 'rate' => 24.00],
                    ['name' => 'Line Cook', 'description' => 'Station cooking and food preparation', 'color' => '#DC2626', 'rate' => 16.00],
                    ['name' => 'Prep Cook', 'description' => 'Ingredient preparation and mise en place', 'color' => '#B91C1C', 'rate' => 14.00],
                    ['name' => 'Dishwasher', 'description' => 'Kitchen sanitation and dishwashing', 'color' => '#991B1B', 'rate' => 12.00],
                ],
            ],
            [
                'name' => 'Maintenance',
                'description' => 'Building maintenance and facility upkeep',
                'color' => '#6B7280',
                'roles' => [
                    ['name' => 'Maintenance Technician', 'description' => 'Repairs and preventive maintenance', 'color' => '#9CA3AF', 'rate' => 20.00],
                    ['name' => 'Groundskeeper', 'description' => 'Exterior maintenance and landscaping', 'color' => '#6B7280', 'rate' => 15.00],
                ],
            ],
            [
                'name' => 'Spa & Wellness',
                'description' => 'Spa services and wellness facilities',
                'color' => '#EC4899',
                'roles' => [
                    ['name' => 'Spa Receptionist', 'description' => 'Spa bookings and guest coordination', 'color' => '#F472B6', 'rate' => 14.00],
                    ['name' => 'Massage Therapist', 'description' => 'Therapeutic massage services', 'color' => '#EC4899', 'rate' => 25.00],
                    ['name' => 'Fitness Attendant', 'description' => 'Gym supervision and equipment assistance', 'color' => '#DB2777', 'rate' => 13.00],
                ],
            ],
        ];
    }

    private function getMedicalDepartments(): array
    {
        return [
            [
                'name' => 'Emergency Department',
                'description' => 'Emergency and urgent care services',
                'color' => '#EF4444',
                'roles' => [
                    ['name' => 'ER Nurse', 'description' => 'Emergency nursing care and triage', 'color' => '#F87171', 'rate' => 38.00],
                    ['name' => 'ER Technician', 'description' => 'Emergency medical technician support', 'color' => '#EF4444', 'rate' => 22.00],
                    ['name' => 'Triage Nurse', 'description' => 'Patient assessment and prioritization', 'color' => '#DC2626', 'rate' => 36.00],
                ],
            ],
            [
                'name' => 'Nursing',
                'description' => 'Inpatient nursing care and patient services',
                'color' => '#3B82F6',
                'roles' => [
                    ['name' => 'Registered Nurse', 'description' => 'Patient care and medication administration', 'color' => '#60A5FA', 'rate' => 35.00],
                    ['name' => 'Licensed Practical Nurse', 'description' => 'Basic nursing care under RN supervision', 'color' => '#3B82F6', 'rate' => 24.00],
                    ['name' => 'Certified Nursing Assistant', 'description' => 'Patient support and daily care assistance', 'color' => '#2563EB', 'rate' => 16.00],
                    ['name' => 'Charge Nurse', 'description' => 'Unit coordination and staff supervision', 'color' => '#1D4ED8', 'rate' => 40.00],
                ],
            ],
            [
                'name' => 'Administration',
                'description' => 'Patient registration and medical records',
                'color' => '#6B7280',
                'roles' => [
                    ['name' => 'Patient Registrar', 'description' => 'Patient check-in and registration', 'color' => '#9CA3AF', 'rate' => 16.00],
                    ['name' => 'Medical Records Clerk', 'description' => 'Health records management and filing', 'color' => '#6B7280', 'rate' => 17.00],
                    ['name' => 'Billing Specialist', 'description' => 'Insurance claims and patient billing', 'color' => '#4B5563', 'rate' => 20.00],
                    ['name' => 'Unit Secretary', 'description' => 'Administrative support for clinical units', 'color' => '#374151', 'rate' => 15.00],
                ],
            ],
            [
                'name' => 'Laboratory',
                'description' => 'Diagnostic testing and laboratory services',
                'color' => '#10B981',
                'roles' => [
                    ['name' => 'Medical Lab Technologist', 'description' => 'Laboratory testing and analysis', 'color' => '#34D399', 'rate' => 28.00],
                    ['name' => 'Phlebotomist', 'description' => 'Blood collection and specimen handling', 'color' => '#10B981', 'rate' => 18.00],
                    ['name' => 'Lab Assistant', 'description' => 'Laboratory support and specimen processing', 'color' => '#059669', 'rate' => 15.00],
                ],
            ],
            [
                'name' => 'Radiology',
                'description' => 'Medical imaging and diagnostic radiology',
                'color' => '#8B5CF6',
                'roles' => [
                    ['name' => 'Radiologic Technologist', 'description' => 'X-ray and imaging procedures', 'color' => '#A78BFA', 'rate' => 30.00],
                    ['name' => 'CT Technologist', 'description' => 'Computed tomography imaging', 'color' => '#8B5CF6', 'rate' => 32.00],
                    ['name' => 'MRI Technologist', 'description' => 'Magnetic resonance imaging', 'color' => '#7C3AED', 'rate' => 34.00],
                ],
            ],
            [
                'name' => 'Pharmacy',
                'description' => 'Medication dispensing and pharmaceutical services',
                'color' => '#F59E0B',
                'roles' => [
                    ['name' => 'Clinical Pharmacist', 'description' => 'Medication review and patient consultation', 'color' => '#FBBF24', 'rate' => 58.00],
                    ['name' => 'Pharmacy Technician', 'description' => 'Medication preparation and dispensing', 'color' => '#F59E0B', 'rate' => 18.00],
                ],
            ],
            [
                'name' => 'Environmental Services',
                'description' => 'Facility cleanliness and sanitation',
                'color' => '#14B8A6',
                'roles' => [
                    ['name' => 'EVS Technician', 'description' => 'Healthcare facility cleaning and sanitation', 'color' => '#2DD4BF', 'rate' => 14.00],
                    ['name' => 'EVS Supervisor', 'description' => 'Environmental services team supervision', 'color' => '#14B8A6', 'rate' => 18.00],
                ],
            ],
            [
                'name' => 'Patient Transport',
                'description' => 'Patient movement and transport services',
                'color' => '#EC4899',
                'roles' => [
                    ['name' => 'Patient Transporter', 'description' => 'Patient wheelchair and stretcher transport', 'color' => '#F472B6', 'rate' => 14.00],
                    ['name' => 'Transport Coordinator', 'description' => 'Transport scheduling and dispatch', 'color' => '#EC4899', 'rate' => 17.00],
                ],
            ],
        ];
    }

    private function getLogisticsDepartments(): array
    {
        return [
            [
                'name' => 'Receiving',
                'description' => 'Inbound freight and inventory receiving',
                'color' => '#3B82F6',
                'roles' => [
                    ['name' => 'Receiving Clerk', 'description' => 'Document verification and data entry', 'color' => '#60A5FA', 'rate' => 16.00],
                    ['name' => 'Dock Worker', 'description' => 'Unloading and staging incoming freight', 'color' => '#3B82F6', 'rate' => 17.00],
                    ['name' => 'Receiving Supervisor', 'description' => 'Receiving operations oversight', 'color' => '#2563EB', 'rate' => 22.00],
                ],
            ],
            [
                'name' => 'Warehouse Operations',
                'description' => 'Storage, inventory management, and order fulfillment',
                'color' => '#F59E0B',
                'roles' => [
                    ['name' => 'Warehouse Associate', 'description' => 'Picking, packing, and inventory handling', 'color' => '#FBBF24', 'rate' => 16.00],
                    ['name' => 'Forklift Operator', 'description' => 'Material handling and pallet movement', 'color' => '#F59E0B', 'rate' => 19.00],
                    ['name' => 'Inventory Specialist', 'description' => 'Cycle counts and inventory accuracy', 'color' => '#D97706', 'rate' => 18.00],
                    ['name' => 'Order Picker', 'description' => 'Order selection and fulfillment', 'color' => '#B45309', 'rate' => 16.50],
                ],
            ],
            [
                'name' => 'Shipping',
                'description' => 'Outbound logistics and freight dispatch',
                'color' => '#10B981',
                'roles' => [
                    ['name' => 'Shipping Clerk', 'description' => 'Shipping documentation and scheduling', 'color' => '#34D399', 'rate' => 16.00],
                    ['name' => 'Packer', 'description' => 'Order packaging and labeling', 'color' => '#10B981', 'rate' => 15.00],
                    ['name' => 'Shipping Supervisor', 'description' => 'Outbound operations management', 'color' => '#059669', 'rate' => 22.00],
                ],
            ],
            [
                'name' => 'Quality Control',
                'description' => 'Product inspection and quality assurance',
                'color' => '#8B5CF6',
                'roles' => [
                    ['name' => 'Quality Inspector', 'description' => 'Product inspection and defect identification', 'color' => '#A78BFA', 'rate' => 18.00],
                    ['name' => 'QC Auditor', 'description' => 'Quality audits and compliance checks', 'color' => '#8B5CF6', 'rate' => 20.00],
                ],
            ],
            [
                'name' => 'Administration',
                'description' => 'Office operations and administrative support',
                'color' => '#6B7280',
                'roles' => [
                    ['name' => 'Office Administrator', 'description' => 'General administrative duties and coordination', 'color' => '#9CA3AF', 'rate' => 18.00],
                    ['name' => 'Data Entry Clerk', 'description' => 'System data entry and record maintenance', 'color' => '#6B7280', 'rate' => 15.00],
                    ['name' => 'Customer Service Rep', 'description' => 'Client inquiries and order support', 'color' => '#4B5563', 'rate' => 16.00],
                ],
            ],
            [
                'name' => 'Facility Maintenance',
                'description' => 'Building and equipment maintenance',
                'color' => '#EF4444',
                'roles' => [
                    ['name' => 'Maintenance Technician', 'description' => 'Equipment repairs and preventive maintenance', 'color' => '#F87171', 'rate' => 22.00],
                    ['name' => 'Janitorial Staff', 'description' => 'Facility cleaning and sanitation', 'color' => '#EF4444', 'rate' => 14.00],
                ],
            ],
            [
                'name' => 'Transportation',
                'description' => 'Fleet operations and delivery coordination',
                'color' => '#EC4899',
                'roles' => [
                    ['name' => 'Delivery Driver', 'description' => 'Local delivery and customer service', 'color' => '#F472B6', 'rate' => 18.00],
                    ['name' => 'Route Coordinator', 'description' => 'Delivery route planning and optimization', 'color' => '#EC4899', 'rate' => 20.00],
                    ['name' => 'Fleet Dispatcher', 'description' => 'Driver dispatch and communication', 'color' => '#DB2777', 'rate' => 19.00],
                ],
            ],
        ];
    }

    private function createUser(Tenant $tenant, string $firstName, string $lastName, string $email): User
    {
        return User::create([
            'tenant_id' => $tenant->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $this->faker->phoneNumber(),
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function assignSystemRole(
        User $user,
        SystemRole $role,
        ?Location $location = null,
        ?Department $department = null
    ): void {
        UserRoleAssignment::create([
            'user_id' => $user->id,
            'system_role' => $role->value,
            'location_id' => $location?->id,
            'department_id' => $department?->id,
        ]);
    }

    private function createEmployeesForDepartment(
        Tenant $tenant,
        Department $department,
        array $roles,
        int $count
    ): void {
        $employees = [];

        for ($i = 0; $i < $count; $i++) {
            $this->employeeCounter++;
            $user = $this->createUser(
                $tenant,
                $this->faker->firstName(),
                $this->faker->lastName(),
                $this->generateEmail('employee'.$this->employeeCounter, Str::slug($tenant->name))
            );

            UserRoleAssignment::create([
                'user_id' => $user->id,
                'system_role' => SystemRole::Employee->value,
            ]);

            $primaryRole = $roles[array_rand($roles)];
            UserBusinessRole::create([
                'user_id' => $user->id,
                'business_role_id' => $primaryRole->id,
                'is_primary' => true,
            ]);

            if (count($roles) > 1 && rand(1, 100) <= 30) {
                $secondaryRoles = array_filter($roles, fn ($r) => $r->id !== $primaryRole->id);
                if (! empty($secondaryRoles)) {
                    $secondaryRole = $secondaryRoles[array_rand($secondaryRoles)];
                    UserBusinessRole::create([
                        'user_id' => $user->id,
                        'business_role_id' => $secondaryRole->id,
                        'is_primary' => false,
                    ]);
                }
            }

            $this->createEmploymentDetails($user, $primaryRole);
            $this->createAvailability($user);

            $employees[] = ['user' => $user, 'primaryRole' => $primaryRole];
        }

        $this->createShiftsForEmployees($tenant, $department, $employees);
    }

    private function createEmploymentDetails(User $user, BusinessRole $primaryRole): void
    {
        $statuses = [
            EmploymentStatus::Active,
            EmploymentStatus::Active,
            EmploymentStatus::Active,
            EmploymentStatus::Active,
            EmploymentStatus::OnLeave,
            EmploymentStatus::NoticePeriod,
        ];
        $status = $statuses[array_rand($statuses)];

        $payTypes = [PayType::Hourly, PayType::Hourly, PayType::Salaried];
        $payType = $payTypes[array_rand($payTypes)];

        $startDate = $this->faker->dateTimeBetween('-5 years', '-1 month');
        $probationEndDate = rand(1, 100) <= 20 ? now()->addDays(rand(30, 90)) : Carbon::parse($startDate)->addMonths(3);

        $data = [
            'user_id' => $user->id,
            'employment_start_date' => $startDate,
            'employment_status' => $status,
            'pay_type' => $payType,
            'currency' => 'GBP',
            'probation_end_date' => $probationEndDate,
            'target_hours_per_week' => $this->faker->randomElement([20, 30, 37.5, 40]),
            'min_hours_per_week' => $this->faker->randomElement([null, 16, 20, 24]),
            'max_hours_per_week' => $this->faker->randomElement([null, 40, 45, 48]),
            'overtime_eligible' => $this->faker->boolean(60),
        ];

        if ($payType === PayType::Hourly) {
            $data['base_hourly_rate'] = $primaryRole->default_hourly_rate + $this->faker->randomFloat(2, -2, 5);
        } else {
            $data['annual_salary'] = $this->faker->randomElement([28000, 32000, 38000, 45000, 52000, 65000]);
        }

        if ($status === EmploymentStatus::NoticePeriod) {
            $data['final_working_date'] = now()->addDays(rand(14, 60));
        }

        if ($status === EmploymentStatus::OnLeave) {
            $data['notes'] = $this->faker->randomElement([
                'On parental leave until ' . now()->addWeeks(rand(4, 12))->format('d M Y'),
                'Medical leave - expected return ' . now()->addWeeks(rand(2, 6))->format('d M Y'),
                'Sabbatical leave',
            ]);
        }

        UserEmploymentDetails::create($data);
    }

    private function createAvailability(User $user): void
    {
        if (rand(1, 100) <= 30) {
            return;
        }

        $availabilityPatterns = [
            'weekdays_day' => [
                ['day' => 1, 'start' => '09:00', 'end' => '17:00', 'level' => PreferenceLevel::Available],
                ['day' => 2, 'start' => '09:00', 'end' => '17:00', 'level' => PreferenceLevel::Available],
                ['day' => 3, 'start' => '09:00', 'end' => '17:00', 'level' => PreferenceLevel::Available],
                ['day' => 4, 'start' => '09:00', 'end' => '17:00', 'level' => PreferenceLevel::Available],
                ['day' => 5, 'start' => '09:00', 'end' => '17:00', 'level' => PreferenceLevel::Available],
                ['day' => 6, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
                ['day' => 0, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
            ],
            'flexible' => [
                ['day' => 1, 'start' => '06:00', 'end' => '22:00', 'level' => PreferenceLevel::Available],
                ['day' => 2, 'start' => '06:00', 'end' => '22:00', 'level' => PreferenceLevel::Available],
                ['day' => 3, 'start' => '06:00', 'end' => '22:00', 'level' => PreferenceLevel::Available],
                ['day' => 4, 'start' => '06:00', 'end' => '22:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 5, 'start' => '06:00', 'end' => '22:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 6, 'start' => '08:00', 'end' => '18:00', 'level' => PreferenceLevel::IfNeeded],
                ['day' => 0, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
            ],
            'part_time' => [
                ['day' => 1, 'start' => '09:00', 'end' => '14:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 2, 'start' => '09:00', 'end' => '14:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 3, 'start' => '09:00', 'end' => '14:00', 'level' => PreferenceLevel::Available],
                ['day' => 4, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
                ['day' => 5, 'start' => '09:00', 'end' => '14:00', 'level' => PreferenceLevel::Available],
                ['day' => 6, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
                ['day' => 0, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
            ],
            'evening_shift' => [
                ['day' => 1, 'start' => '14:00', 'end' => '23:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 2, 'start' => '14:00', 'end' => '23:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 3, 'start' => '14:00', 'end' => '23:00', 'level' => PreferenceLevel::Available],
                ['day' => 4, 'start' => '14:00', 'end' => '23:00', 'level' => PreferenceLevel::Available],
                ['day' => 5, 'start' => '14:00', 'end' => '23:00', 'level' => PreferenceLevel::Available],
                ['day' => 6, 'start' => '16:00', 'end' => '23:00', 'level' => PreferenceLevel::IfNeeded],
                ['day' => 0, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
            ],
            'weekend_preferred' => [
                ['day' => 1, 'start' => null, 'end' => null, 'level' => PreferenceLevel::IfNeeded],
                ['day' => 2, 'start' => null, 'end' => null, 'level' => PreferenceLevel::Unavailable],
                ['day' => 3, 'start' => '09:00', 'end' => '17:00', 'level' => PreferenceLevel::IfNeeded],
                ['day' => 4, 'start' => '09:00', 'end' => '17:00', 'level' => PreferenceLevel::Available],
                ['day' => 5, 'start' => '09:00', 'end' => '21:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 6, 'start' => '08:00', 'end' => '22:00', 'level' => PreferenceLevel::Preferred],
                ['day' => 0, 'start' => '08:00', 'end' => '18:00', 'level' => PreferenceLevel::Preferred],
            ],
        ];

        $patternKey = array_rand($availabilityPatterns);
        $pattern = $availabilityPatterns[$patternKey];

        foreach ($pattern as $slot) {
            UserAvailability::create([
                'user_id' => $user->id,
                'type' => AvailabilityType::Recurring,
                'day_of_week' => $slot['day'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'is_available' => $slot['level'] !== PreferenceLevel::Unavailable,
                'preference_level' => $slot['level'],
            ]);
        }

        if (rand(1, 100) <= 40) {
            $specificDate = now()->addDays(rand(7, 30));
            UserAvailability::create([
                'user_id' => $user->id,
                'type' => AvailabilityType::SpecificDate,
                'specific_date' => $specificDate,
                'is_available' => false,
                'preference_level' => PreferenceLevel::Unavailable,
                'notes' => $this->faker->randomElement([
                    'Medical appointment',
                    'Personal commitment',
                    'Family event',
                    'Vacation day',
                    'Training course',
                ]),
            ]);
        }
    }

    private function createShiftsForEmployees(Tenant $tenant, Department $department, array $employees): void
    {
        $startOfWeek = Carbon::now()->startOfWeek();

        $shiftPatterns = [
            ['start' => '06:00', 'end' => '14:00'],
            ['start' => '07:00', 'end' => '15:00'],
            ['start' => '08:00', 'end' => '16:00'],
            ['start' => '09:00', 'end' => '17:00'],
            ['start' => '14:00', 'end' => '22:00'],
            ['start' => '15:00', 'end' => '23:00'],
            ['start' => '22:00', 'end' => '06:00'],
        ];

        foreach ($employees as $index => $employeeData) {
            $user = $employeeData['user'];
            $role = $employeeData['primaryRole'];
            $pattern = $shiftPatterns[$index % count($shiftPatterns)];

            for ($day = 0; $day < 7; $day++) {
                if (rand(1, 100) <= 70) {
                    $date = $startOfWeek->copy()->addDays($day);

                    Shift::create([
                        'tenant_id' => $tenant->id,
                        'location_id' => $department->location_id,
                        'department_id' => $department->id,
                        'business_role_id' => $role->id,
                        'user_id' => $user->id,
                        'date' => $date,
                        'start_time' => $pattern['start'],
                        'end_time' => $pattern['end'],
                        'break_duration_minutes' => 30,
                        'status' => ShiftStatus::Published,
                    ]);
                }
            }
        }
    }

    private function generateEmail(string $prefix, string $domain): string
    {
        $baseEmail = Str::slug($prefix).'@'.Str::slug($domain).'.demo';
        $email = $baseEmail;
        $counter = 1;

        while (in_array($email, $this->usedEmails)) {
            $email = Str::slug($prefix).$counter.'@'.Str::slug($domain).'.demo';
            $counter++;
        }

        $this->usedEmails[] = $email;

        return $email;
    }
}
