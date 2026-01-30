# Phase 6: Employee Management & Self-Service (High Priority)

## 6.1 Employee Profile (Self-Service) ✅
**Effort: Medium** | **Status: Completed**

Allow employees to view and update their own information.

**Files created:**
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Requests/Profile/UpdateProfileRequest.php`
- `app/Http/Requests/Profile/ChangePasswordRequest.php`
- `resources/views/profile/index.blade.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/profile/change-password.blade.php`
- `tests/Feature/ProfileTest.php` (13 tests)

**Features:**
- View personal profile information
- Update contact details (phone, email, address)
- Upload/change profile photo (avatar)
- Change password with current password verification
- View employment details (read-only: start date, role, pay info)
- View current availability settings

**Tasks:**
- [x] Create ProfileController with index/edit/update actions
- [x] Create profile index view (overview of all info)
- [x] Create profile edit view for contact information
- [x] Implement avatar upload with image processing
- [x] Create password change form with validation
- [x] Add profile link to user dropdown menu
- [x] Write tests

---

## 6.2 Employee HR Records (Admin) ✅
**Effort: Large** | **Status: Completed**

Comprehensive employment records for administrators.

**Database Changes:**
- [x] Create migration for `user_employment_details` table:
  - `user_id` (FK, unique)
  - `employment_start_date` (date)
  - `employment_end_date` (date, nullable - for fixed-term)
  - `final_working_date` (date, nullable - leaving date)
  - `probation_end_date` (date, nullable)
  - `employment_status` (enum: active, on_leave, suspended, notice_period, terminated)
  - `pay_type` (enum: hourly, salaried)
  - `base_hourly_rate` (decimal, nullable)
  - `annual_salary` (decimal, nullable)
  - `currency` (varchar, default 'GBP')
  - `target_hours_per_week` (decimal, nullable)
  - `min_hours_per_week` (decimal, nullable)
  - `max_hours_per_week` (decimal, nullable)
  - `overtime_eligible` (boolean)
  - `notes` (text, nullable - private HR notes)

**Files created:**
- `app/Models/UserEmploymentDetails.php`
- `app/Enums/EmploymentStatus.php`
- `app/Enums/PayType.php`
- `app/Http/Controllers/EmploymentDetailsController.php`
- `app/Http/Requests/Employment/UpdateEmploymentDetailsRequest.php`
- `app/Policies/UserEmploymentDetailsPolicy.php`
- `resources/views/users/employment.blade.php`
- `database/factories/UserEmploymentDetailsFactory.php`
- `tests/Feature/EmploymentDetailsTest.php` (22 tests)

**Features:**
| Field | Description |
|-------|-------------|
| Employment Start Date | First day of employment |
| Employment End Date | Contract end date (fixed-term only) |
| Final Working Date | Last day (when leaving) |
| Probation End Date | End of probation period |
| Employment Status | Active, On Leave, Suspended, Notice Period, Terminated |
| Pay Type | Hourly or Salaried |
| Base Hourly Rate | Rate per hour (if hourly) |
| Annual Salary | Yearly salary (if salaried) |
| Currency | Payment currency |
| Target Hours/Week | Planned weekly hours for scheduling |
| Min Hours/Week | Minimum guaranteed hours |
| Max Hours/Week | Maximum allowed hours |
| Overtime Eligible | Whether overtime rates apply |
| HR Notes | Private administrative notes |

**Role-Specific Pay Rates:**
The `user_business_roles` pivot table has `hourly_rate` column for role-specific rates.
- Employment Details form includes "Role-Specific Hourly Rates" section
- Shows all assigned business roles with input for custom hourly rate
- Displays role's default rate as reference (e.g., "Default: 15.00")
- User show page displays effective rate per role with "Custom rate" or "Default rate" label
- Employee profile shows their rates (read-only)

**Rate Hierarchy:**
1. If user has a custom rate for a role, use that
2. Otherwise fall back to role's default_hourly_rate

**Tasks:**
- [x] Create UserEmploymentDetails model with casts and accessors
- [x] Create EmploymentStatus enum
- [x] Create PayType enum
- [x] Create EmploymentDetailsController
- [x] Create employment details form (tab on user edit page)
- [x] Add role-specific hourly rates section to employment form
- [x] Display role hourly rates on user show page
- [x] Display role hourly rates on employee profile (read-only)
- [x] Implement pay rate hierarchy logic
- [x] Add employment status badges to user list
- [ ] Show employees on notice period/leaving soon on dashboard (future enhancement)
- [ ] Create report for employment status overview (future enhancement)
- [x] Write tests

---

## 6.3 Employee Availability Management ✅
**Effort: Large** | **Status: Completed**

Allow employees to set availability and admins to view it during scheduling.

**Database Changes:**
- [x] Create migration for `user_availability` table:
  - `user_id` (FK)
  - `type` (enum: recurring, specific_date)
  - `day_of_week` (tinyint, nullable - 0=Sun to 6=Sat)
  - `specific_date` (date, nullable)
  - `start_time` (time, nullable)
  - `end_time` (time, nullable)
  - `is_available` (boolean)
  - `preference_level` (enum: preferred, available, if_needed, unavailable)
  - `notes` (text, nullable)
  - `effective_from` (date, nullable)
  - `effective_until` (date, nullable)

**Files created:**
- `app/Models/UserAvailability.php`
- `app/Enums/AvailabilityType.php`
- `app/Enums/PreferenceLevel.php`
- `app/Http/Controllers/AvailabilityController.php`
- `app/Http/Requests/Availability/StoreAvailabilityRequest.php`
- `app/Http/Requests/Availability/UpdateAvailabilityRequest.php`
- `app/Services/AvailabilityService.php`
- `resources/views/availability/index.blade.php`
- `resources/views/availability/edit.blade.php`
- `resources/views/availability/show.blade.php`
- `database/factories/UserAvailabilityFactory.php`
- `tests/Feature/AvailabilityTest.php` (20 tests)

**Features:**
- Set recurring weekly availability (e.g., "Mon-Fri 9am-5pm")
- Mark specific dates as unavailable
- Set preference levels (preferred, available, if needed, unavailable)
- Set effective date ranges for availability rules
- Visual calendar showing availability

**Tasks:**
- [x] Create UserAvailability model
- [x] Create AvailabilityType enum
- [x] Create PreferenceLevel enum
- [x] Create AvailabilityService for checking availability
- [x] Create AvailabilityController
- [x] Create availability calendar component (weekly summary in index view)
- [x] Build recurring availability form
- [x] Build specific date exception form
- [x] Show availability warnings on schedule view (via AvailabilityService)
- [ ] Integrate with AI scheduling (Phase 7 dependency)
- [x] Write tests

---

## 6.4 Leave Calendar View (Schedule Integration) ✅
**Effort: Medium** | **Status: Completed**

Integrated leave display into existing schedule views rather than creating a separate calendar.

**Files modified:**
- `app/Http/Controllers/ScheduleController.php` - Added leaveType eager loading
- `resources/views/schedule/index.blade.php` - Week view with colored leave blocks and half-day support
- `resources/views/schedule/day.blade.php` - Day view with positioned leave bars and half-day support
- `database/factories/LeaveRequestFactory.php` - Added startHalfDay() and endHalfDay() states
- `tests/Feature/LeaveCalendarViewTest.php` (new - 15 tests)

**Features:**
- Leave displayed in schedule week view with leave type colors
- Leave displayed in schedule day view with positioned bars
- Half-day leave support:
  - AM leave shows left half of cell (week view) or start-to-noon (day view)
  - PM leave shows right half of cell (week view) or noon-to-end (day view)
- Colors use LeaveType.color field (e.g., blue for Annual, red for Sick)

**Tasks:**
- [x] Update ScheduleController to eager load leaveType relationship
- [x] Update week view leave blocks to use leave type colors
- [x] Implement half-day display in week view (AM/PM split cells)
- [x] Update day view leave blocks to use leave type colors
- [x] Implement half-day positioning in day view (time-based)
- [x] Write tests
