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

## 1.6 Subscription & Feature Management
**Effort: Large**

Manage tenant subscriptions and premium feature add-ons (required for AI Scheduling).

**Database Changes:**
- [ ] Create migration for `tenant_subscriptions` table:
  - `id` (bigint unsigned, PK)
  - `tenant_id` (FK to tenants, unique)
  - `plan` (varchar, default 'basic')
  - `status` (varchar, default 'active')
  - `billing_cycle` (varchar, default 'monthly')
  - `current_period_start` (timestamp)
  - `current_period_end` (timestamp)
  - `cancelled_at` (timestamp, nullable)
  - `stripe_subscription_id` (varchar, nullable)
  - `timestamps`

- [ ] Create migration for `tenant_feature_addons` table:
  - `id` (bigint unsigned, PK)
  - `tenant_id` (FK to tenants)
  - `feature` (varchar)
  - `enabled_at` (timestamp)
  - `expires_at` (timestamp, nullable)
  - `stripe_subscription_item_id` (varchar, nullable)
  - `timestamps`
  - UNIQUE (tenant_id, feature)

**Files to create:**
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
- `tests/Feature/SubscriptionTest.php`
- `tests/Unit/FeatureGateTest.php`

**Subscription Plans:**
| Plan | Description | Includes |
|------|-------------|----------|
| basic | Base subscription | All core features |
| professional | Professional plan | + AI Scheduling, Analytics |
| enterprise | Enterprise plan | All features included |

**Feature Add-ons:**
| Feature | Description |
|---------|-------------|
| ai_scheduling | AI-Powered Scheduling |
| advanced_analytics | Advanced Analytics & Reports |
| api_access | API Access for Integrations |
| priority_support | Priority Support |

**Tasks:**
- [ ] Create TenantSubscription model with relationships
- [ ] Create TenantFeatureAddon model
- [ ] Create all subscription/feature enums
- [ ] Add `hasFeature()` method to Tenant model
- [ ] Add `hasAIScheduling()` convenience method to Tenant model
- [ ] Create RequiresFeature middleware
- [ ] Register middleware alias in bootstrap/app.php
- [ ] Create SubscriptionService for subscription management
- [ ] Create SubscriptionController for viewing/managing subscription
- [ ] Create FeatureController with status endpoint
- [ ] Create subscription index view (shows current plan, features)
- [ ] Create upgrade prompt view
- [ ] Create `@feature` Blade directive
- [ ] Create feature-gate component for UI
- [ ] Update Tenant model with subscription relationships
- [ ] Write unit tests for feature checks
- [ ] Write feature tests for middleware
- [ ] Write tests for subscription management

**UI Integration:**
- Add "Subscription" link in admin settings menu
- Show current plan badge in header/sidebar
- Show upgrade prompts where premium features are unavailable
- Hide/disable AI Scheduling UI when feature not enabled
