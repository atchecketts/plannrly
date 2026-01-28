# Phase 2: Enhanced Scheduling Features (Medium Priority)

## 2.1 Drag-and-Drop Shift Management COMPLETE
**Effort: Large**

Enable moving shifts between employees via drag-and-drop.

**Tasks:**
- [x] Add JavaScript drag-and-drop library (SortableJS or native) - *Using native HTML5 drag-drop with Alpine.js*
- [x] Implement drag handlers on shift blocks
- [x] Implement drop zones on employee rows
- [x] Create AJAX endpoint for shift reassignment - *Uses shifts.update endpoint*
- [x] Add visual feedback during drag
- [x] Handle validation (role compatibility, shift conflicts)
- [x] Update schedule views (week and day) - *Both views support drag-drop*
- [x] Write integration tests

**Also Implemented:**
- Shift resize on day view (drag top/bottom edges to adjust times)
- Visual drag ghost with shift details
- Drop zone highlighting during drag

---

## 2.2 Copy and Paste Shifts
**Effort: Medium**

Allow copying shifts or entire schedule patterns.

**Files to create:**
- `app/Http/Controllers/ShiftCopyController.php`

**Tasks:**
- [ ] Add copy button to shift context menu
- [ ] Implement clipboard state in JavaScript
- [ ] Add paste button on empty cells
- [ ] Create endpoint to duplicate shifts
- [ ] Support copying single shift
- [ ] Support copying entire day's shifts
- [ ] Support copying entire week's schedule
- [ ] Add keyboard shortcuts (Ctrl+C, Ctrl+V)
- [ ] Write tests

---

## 2.3 Recurring Shift Templates
**Effort: Medium**

The schema supports recurring shifts but no UI exists.

**Files to modify:**
- `app/Http/Controllers/ShiftController.php`
- `resources/views/components/shift-edit-modal.blade.php`

**Tasks:**
- [ ] Add recurrence options to shift create/edit modal
- [ ] Implement recurrence rule builder UI (daily, weekly, custom)
- [ ] Create job to generate recurring shift instances
- [ ] Add ability to edit single instance vs all instances
- [ ] Handle deletion of recurring shifts
- [ ] Write tests

---

## 2.4 Schedule History / Version Control
**Effort: Large**

Track changes to schedules for audit purposes.

**Files to create:**
- `app/Models/ScheduleHistory.php`
- `database/migrations/xxxx_create_schedule_history_table.php`
- `app/Observers/ShiftHistoryObserver.php`
- `resources/views/schedule/history.blade.php`

**Tasks:**
- [ ] Create schedule_history migration
- [ ] Create ScheduleHistory model
- [ ] Implement observer to log shift changes
- [ ] Create history view showing timeline of changes
- [ ] Add "View History" button to schedule
- [ ] Write tests

---

## 2.5 Staffing Requirements & Coverage Warnings
**Effort: Large**

Define minimum and maximum staffing levels per role and show warnings on schedule views.

**Database Changes:**
- [ ] Create migration for `staffing_requirements` table:
  - `id` (bigint unsigned, PK)
  - `tenant_id` (FK to tenants)
  - `location_id` (FK to locations, nullable)
  - `department_id` (FK to departments, nullable)
  - `business_role_id` (FK to business_roles)
  - `day_of_week` (tinyint, 0-6)
  - `start_time` (time)
  - `end_time` (time)
  - `min_employees` (integer, default 0)
  - `max_employees` (integer, nullable)
  - `is_active` (boolean, default true)
  - `notes` (text, nullable)
  - `timestamps`

**Files to create:**
- `app/Models/StaffingRequirement.php`
- `app/Enums/CoverageStatus.php`
- `app/Services/CoverageAnalysisService.php`
- `app/Http/Controllers/StaffingRequirementController.php`
- `app/Http/Controllers/CoverageController.php`
- `app/Http/Requests/StaffingRequirement/StoreStaffingRequirementRequest.php`
- `app/Http/Requests/StaffingRequirement/UpdateStaffingRequirementRequest.php`
- `app/Policies/StaffingRequirementPolicy.php`
- `resources/views/staffing-requirements/index.blade.php`
- `resources/views/staffing-requirements/create.blade.php`
- `resources/views/staffing-requirements/edit.blade.php`
- `resources/views/components/coverage-indicator.blade.php`
- `resources/views/components/coverage-summary.blade.php`
- `tests/Feature/StaffingRequirementTest.php`
- `tests/Unit/Services/CoverageAnalysisServiceTest.php`

**CoverageStatus Enum:**
| Value | Description | Color |
|-------|-------------|-------|
| adequate | Within min/max range | Green |
| understaffed | Below minimum | Red/Orange |
| overstaffed | Above maximum | Yellow |
| no_requirement | No rule defined | Gray |

**Features:**
| Feature | Description |
|---------|-------------|
| Rules Management | CRUD for staffing requirements |
| Per-Role Rules | Different min/max per business role |
| Day-of-Week | Rules can vary by day (e.g., weekends need more) |
| Time Windows | Split day into time slots with different requirements |
| Location/Dept Scope | Rules can be global, per-location, or per-department |
| Coverage Warnings | Visual indicators on week/day schedule views |
| Coverage Summary | Widget showing coverage status for the period |

**Tasks:**
- [ ] Create StaffingRequirement model with relationships
- [ ] Create CoverageStatus enum
- [ ] Create CoverageAnalysisService with:
  - `analyzeCoverage()` - Analyze full date range
  - `getDayCoverage()` - Get coverage for single day
  - `getTimeSlotCoverage()` - Check specific time slot
  - `countScheduledEmployees()` - Count scheduled for slot
- [ ] Create StaffingRequirementController with CRUD
- [ ] Create CoverageController for API
- [ ] Create policy for authorization
- [ ] Create index view with table of rules
- [ ] Create create/edit forms with:
  - Role selector
  - Day of week picker (multi-select or single)
  - Time window pickers (start/end)
  - Min/max employee inputs
- [ ] Add navigation link to sidebar under Settings
- [ ] Create coverage indicator component (shows colored badge)
- [ ] Create coverage summary widget (shows gaps count)
- [ ] Integrate indicators into schedule week view
- [ ] Integrate indicators into schedule day view
- [ ] Add API endpoint for coverage analysis
- [ ] Integrate with AI Scheduling as constraint
- [ ] Write unit tests for coverage calculations
- [ ] Write feature tests for CRUD operations
- [ ] Write tests for schedule view integration

**Schedule View Integration:**

On Week View:
- Show small colored indicator per cell when coverage issues exist
- Hover to show tooltip with details ("Need 1 more Waiter 09:00-10:00")
- Optional coverage summary bar above schedule

On Day View:
- Show coverage status per time slot in the timeline header
- Red highlight for understaffed time windows
- Yellow highlight for overstaffed time windows
- Coverage summary panel

---

## 2.6 Labor Cost Budgeting
**Effort: Large**

Control labor costs with budget planning and real-time tracking on schedule views.

**Database Changes:**
- [ ] Create migration for `labor_budgets` table
- [ ] Create migration for `labor_budget_snapshots` table

**Files to create:**
- `app/Models/LaborBudget.php`
- `app/Models/LaborBudgetSnapshot.php`
- `app/Services/LaborBudgetService.php`
- `app/Http/Controllers/LaborBudgetController.php`
- `app/Http/Requests/LaborBudget/StoreLaborBudgetRequest.php`
- `app/Http/Requests/LaborBudget/UpdateLaborBudgetRequest.php`
- `app/Policies/LaborBudgetPolicy.php`
- `app/Notifications/BudgetThresholdNotification.php`
- `app/Console/Commands/SendBudgetAlerts.php`
- `resources/views/budgets/index.blade.php`
- `resources/views/budgets/create.blade.php`
- `resources/views/budgets/edit.blade.php`
- `resources/views/components/budget-indicator.blade.php`
- `tests/Feature/LaborBudgetTest.php`

**Features:**
| Feature | Description |
|---------|-------------|
| Budget Setup | Set weekly/bi-weekly/monthly budgets per location |
| Department Budgets | Optional budget split by department |
| Threshold Alerts | Warning at 80%, critical at 95% (configurable) |
| Real-Time Tracking | See scheduled cost vs budget as shifts are added |
| Actual Tracking | Compare actual labor cost from time entries |
| Schedule Integration | Budget indicator on week view with color coding |
| Cost Breakdown | View cost by role, department, employee |
| Historical Snapshots | Track budget performance over time |

**Cost Calculation Logic:**
```
Scheduled Cost = Σ (shift.duration_hours × employee.hourly_rate)
- Use role-specific rate if employee has override for that role
- Apply overtime multiplier if tenant has overtime rules
- Deduct scheduled breaks from hours

Actual Cost = Σ (time_entry.worked_hours × employee.hourly_rate)
- Calculate from clock in/out times
- Apply overtime for hours over threshold
```

**Tasks:**
- [ ] Create LaborBudget model with relationships
- [ ] Create LaborBudgetSnapshot model
- [ ] Create LaborBudgetService with:
  - `setBudget()` - Create/update budget
  - `calculateScheduledCost()` - Sum of scheduled shift costs
  - `calculateActualCost()` - Sum of actual labor costs
  - `getBudgetStatus()` - Return status with percentages
  - `wouldExceedBudget()` - Check before creating shift
  - `getLaborCostByEmployee()` - Breakdown per employee
  - `createPeriodSnapshot()` - Archive completed period
- [ ] Create LaborBudgetController with CRUD
- [ ] Create form requests for validation
- [ ] Create LaborBudgetPolicy for authorization
- [ ] Create budget index view with list of budgets
- [ ] Create budget create/edit forms with:
  - Location selector
  - Optional department selector
  - Period type (weekly/bi-weekly/monthly)
  - Budget amount and currency
  - Warning/critical thresholds
- [ ] Create budget-indicator component for schedule view
- [ ] Integrate indicator into week view header
- [ ] Add tooltip showing: Budget, Scheduled, Actual, Remaining
- [ ] Add warning when creating shift would exceed budget
- [ ] Create budget status API endpoint
- [ ] Create budget breakdown API endpoint
- [ ] Create SendBudgetAlerts command
- [ ] Schedule command to run daily
- [ ] Create BudgetThresholdNotification
- [ ] Add navigation link under Settings
- [ ] Write unit tests for cost calculations
- [ ] Write feature tests for CRUD and API
- [ ] Write tests for schedule view integration

---

## 2.7 Print Schedule
**Effort: Medium**

Print-optimized schedule views for posting or distribution.

**Files to create:**
- `app/Http/Controllers/SchedulePrintController.php`
- `app/Services/SchedulePrintService.php`
- `resources/views/schedule/print.blade.php`
- `resources/views/schedule/print-day.blade.php`
- `tests/Feature/SchedulePrintTest.php`

**Features:**
| Feature | Description |
|---------|-------------|
| Landscape Layout | Optimized for landscape printing |
| Week View Print | Print full week schedule |
| Day View Print | Print single day timeline |
| Filter Support | Print filtered by location/department/role |
| PDF Export | Download as PDF file |
| Contact Details | Optional employee phone numbers |
| Stats Footer | Total shifts, hours, generated timestamp |

**Tasks:**
- [ ] Create SchedulePrintService
- [ ] Create SchedulePrintController with preview and PDF endpoints
- [ ] Create print-optimized week view template
- [ ] Create print-optimized day view template
- [ ] Add @media print CSS for proper printing
- [ ] Implement @page landscape orientation
- [ ] Add print button to schedule views
- [ ] Add print options modal (include contacts, filter selection)
- [ ] Implement PDF generation using DomPDF or similar
- [ ] Add page break handling for large schedules
- [ ] Write tests
