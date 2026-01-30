# Phase 1: Core Missing Features (High Priority)

## 1.1 Shift Swap Views ✅
**Effort: Small** | **Status: Completed**

The backend for shift swaps is complete but views are missing.

**Files to create:**
- `resources/views/shift-swaps/index.blade.php` - List all swap requests
- `resources/views/shift-swaps/create.blade.php` - Create swap request form

**Tasks:**
- [x] Create shift swap index view with status filtering
- [x] Create shift swap create view with eligible users/shifts selection
- [x] Add navigation link to shift swaps in sidebar (already existed)
- [x] Write tests for shift swap views

---

## 1.2 Employee Dashboard ✅
**Effort: Medium** | **Status: Completed**

Employees need a personalized dashboard showing their schedule and upcoming shifts.

**Files to create:**
- `resources/views/dashboard/employee.blade.php`

**Tasks:**
- [x] Create dedicated employee dashboard view
- [x] Show upcoming shifts for the employee (next 7 days)
- [x] Display pending leave requests
- [x] Show active shift swap requests
- [x] Add quick action buttons (request leave, view schedule)
- [x] Display next shift countdown/reminder
- [x] Update `DashboardController::employeeDashboard()` to use new view
- [x] Write tests for employee dashboard

---

## 1.3 Leave Types Management ✅
**Effort: Medium** | **Status: Completed**

Admins need to manage leave types (Annual Leave, Sick Leave, etc.).

**Files created:**
- `app/Http/Controllers/LeaveTypeController.php`
- `app/Http/Requests/LeaveType/StoreLeaveTypeRequest.php`
- `app/Http/Requests/LeaveType/UpdateLeaveTypeRequest.php`
- `app/Policies/LeaveTypePolicy.php`
- `resources/views/leave-types/index.blade.php`
- `resources/views/leave-types/create.blade.php`
- `resources/views/leave-types/edit.blade.php`
- `tests/Feature/LeaveTypeManagementTest.php`

**Tasks:**
- [x] Create LeaveTypeController with CRUD actions
- [x] Create form request validation classes
- [x] Create LeaveTypePolicy for authorization
- [x] Create index view listing all leave types
- [x] Create create/edit forms
- [x] Add routes to web.php
- [x] Add navigation link to sidebar
- [x] Write comprehensive tests (13 tests)

---

## 1.4 Leave Allowances Management ✅
**Effort: Medium** | **Status: Completed**

Manage annual leave allowances per employee per leave type.

**Files created:**
- `app/Http/Controllers/LeaveAllowanceController.php`
- `app/Http/Requests/LeaveAllowance/StoreLeaveAllowanceRequest.php`
- `app/Http/Requests/LeaveAllowance/UpdateLeaveAllowanceRequest.php`
- `app/Policies/LeaveAllowancePolicy.php`
- `database/factories/LeaveAllowanceFactory.php`
- `resources/views/leave-allowances/index.blade.php`
- `resources/views/leave-allowances/create.blade.php`
- `resources/views/leave-allowances/edit.blade.php`
- `tests/Feature/LeaveAllowanceManagementTest.php`

**Tasks:**
- [x] Create LeaveAllowanceController with CRUD actions
- [x] Create form request validation
- [x] Create LeaveAllowancePolicy for authorization
- [x] Create index view (filterable by year)
- [x] Create create view for new allowances
- [x] Create edit view for adjusting allowances
- [x] Add routes to web.php
- [x] Add navigation link to sidebar
- [x] Write comprehensive tests (14 tests)

---

## 1.5 Tenant Settings Management ✅
**Effort: Medium** | **Status: Completed**

Allow business admins to configure organization settings.

**Files created:**
- `app/Http/Controllers/TenantSettingsController.php`
- `app/Http/Requests/TenantSettings/UpdateTenantSettingsRequest.php`
- `resources/views/settings/edit.blade.php`
- `database/migrations/2026_01_27_151031_add_settings_columns_to_tenant_settings_table.php`
- `tests/Feature/TenantSettingsManagementTest.php`

**Settings managed:**
- Day start/end times
- Week start day
- Timezone
- Date/time format
- Clock in/out enabled
- Shift acknowledgement enabled
- Notification on publish
- Missed shift grace minutes
- Leave carryover mode
- Default currency
- Primary color

**Tasks:**
- [x] Create migration for new settings columns
- [x] Update TenantSettings model with new fields
- [x] Create TenantSettingsController with edit/update
- [x] Create settings view with form
- [x] Add routes and navigation
- [x] Write comprehensive tests (10 tests)

---

## 1.6 Subscription & Feature Management ✅
**Effort: Large** | **Status: Completed**

Manage tenant subscriptions and premium feature add-ons (required for AI Scheduling).

**Files created:**
- `app/Models/TenantSubscription.php`
- `app/Models/TenantFeatureAddon.php`
- `app/Enums/SubscriptionPlan.php`
- `app/Enums/SubscriptionStatus.php`
- `app/Enums/BillingCycle.php`
- `app/Enums/FeatureAddon.php`
- `app/Http/Middleware/RequiresFeature.php`
- `app/Http/Controllers/SubscriptionController.php`
- `app/Http/Controllers/FeatureController.php`
- `app/Services/SubscriptionService.php`
- `resources/views/subscription/index.blade.php`
- `resources/views/subscription/upgrade.blade.php`
- `resources/views/components/feature-gate.blade.php`
- `database/migrations/2026_01_28_000001_create_tenant_subscriptions_table.php`
- `database/migrations/2026_01_28_000002_create_tenant_feature_addons_table.php`
- `tests/Feature/SubscriptionManagementTest.php`
- `tests/Unit/SubscriptionPlanTest.php`

**Tasks:**
- [x] Create TenantSubscription model with relationships
- [x] Create TenantFeatureAddon model
- [x] Create all subscription/feature enums
- [x] Add `hasFeature()` method to Tenant model
- [x] Add `hasAIScheduling()` convenience method to Tenant model
- [x] Create RequiresFeature middleware
- [x] Register middleware alias in bootstrap/app.php
- [x] Create SubscriptionService for subscription management
- [x] Create SubscriptionController for viewing/managing subscription
- [x] Create FeatureController with status endpoint
- [x] Create subscription index view (shows current plan, features)
- [x] Create upgrade prompt view
- [x] Create `@feature` Blade directive
- [x] Create feature-gate component for UI
- [x] Update Tenant model with subscription relationships
- [x] Write unit tests for feature checks
- [x] Write feature tests for middleware
- [x] Write tests for subscription management

---

## 1.7 Sortable Table Headers ✅
**Effort: Medium** | **Status: Completed**

Add sortable and groupable column headers to all 10 list views.

**Files created:**
- `app/Traits/HandlesSorting.php`
- `resources/views/components/table/sortable-header.blade.php`
- `resources/views/components/table/group-header.blade.php`
- `tests/Feature/TableSortingTest.php`

**Tasks:**
- [x] Create HandlesSorting trait for controller reuse
- [x] Create sortable-header Blade component
- [x] Create group-header Blade component for groupable columns
- [x] Integrate sorting into all 10 list views
- [x] Add query string persistence for sort/direction/group
- [x] Write tests for sorting functionality
