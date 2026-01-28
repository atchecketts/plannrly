# Phase 3: Time & Attendance (High Priority)

## 3.1 Clock In/Out Core System
**Effort: Large**

Enable employees to clock in and out of shifts with full time tracking.

**Database Changes:**
- [ ] Add migration for new columns on `time_entries` table:
  - `approved_by` (bigint unsigned, nullable, FK to users)
  - `approved_at` (timestamp, nullable)
  - `adjustment_reason` (text, nullable)
- [ ] Add migration for new columns on `tenant_settings` table:
  - `clock_in_grace_minutes` (integer, default 15)
  - `require_gps_clock_in` (boolean, default false)
  - `auto_clock_out_enabled` (boolean, default false)
  - `auto_clock_out_time` (time, default '23:59:00')
  - `overtime_threshold_minutes` (integer, default 0)
  - `require_manager_approval` (boolean, default false)

**Files to create:**
- `app/Http/Controllers/TimeEntryController.php`
- `app/Http/Requests/TimeEntry/ClockInRequest.php`
- `app/Http/Requests/TimeEntry/ClockOutRequest.php`
- `app/Http/Requests/TimeEntry/StartBreakRequest.php`
- `app/Http/Requests/TimeEntry/AdjustTimeEntryRequest.php`
- `app/Policies/TimeEntryPolicy.php`
- `app/Exceptions/ClockInException.php`
- `app/Exceptions/ClockOutException.php`
- `resources/views/time-entries/index.blade.php`
- `resources/views/time-entries/show.blade.php`
- `resources/views/components/clock-widget.blade.php`
- `tests/Feature/TimeEntryTest.php`
- `tests/Feature/ClockInOutTest.php`

**Controller Actions:**
```
TimeEntryController:
- index()           - List time entries (admin: all, employee: own)
- show()            - View single time entry details
- clockIn()         - Employee clocks in
- clockOut()        - Employee clocks out
- startBreak()      - Start break
- endBreak()        - End break
- adjust()          - Manager adjusts time entry
- approve()         - Manager approves time entry
- currentStatus()   - Get current clock status (API)
```

**Tasks:**
- [ ] Create TimeEntryController with all actions
- [ ] Create form request validation classes
- [ ] Create TimeEntryPolicy for authorization
- [ ] Implement clock-in logic with validations:
  - Check tenant has clock-in enabled
  - Check employee not already clocked in
  - Validate within grace period of shift start
  - Capture GPS location if required
- [ ] Implement clock-out logic with validations:
  - Check employee is currently clocked in
  - Calculate actual worked minutes
  - Capture GPS location if configured
- [ ] Implement break tracking (start/end break)
- [ ] Add manager adjustment functionality with reason
- [ ] Add manager approval workflow if tenant requires it
- [ ] Create time entries index view with filtering
- [ ] Add routes to web.php
- [ ] Write comprehensive tests

---

## 3.2 Clock Widget Component
**Effort: Medium**

Create the clock-in/out widget for employee dashboard.

**Files to create:**
- `resources/views/components/clock-widget.blade.php`
- `resources/js/clock-widget.js` (Alpine.js component)

**Features:**
- Show current clock status (clocked in/out/on break)
- Display current shift information if applicable
- Show elapsed time since clock-in
- One-click clock in/out buttons
- Break start/end buttons
- Show scheduled vs current time
- Real-time clock display

**Tasks:**
- [ ] Create clock widget Blade component
- [ ] Add Alpine.js interactivity for real-time updates
- [ ] Handle clock-in via AJAX
- [ ] Handle clock-out via AJAX
- [ ] Handle break start/end via AJAX
- [ ] Show confirmation dialogs
- [ ] Display success/error messages
- [ ] Integrate widget into employee dashboard
- [ ] Write tests

---

## 3.3 Scheduled vs Actual Time Variance
**Effort: Medium**

Calculate and display variances between scheduled and actual times.

**Files to modify:**
- `app/Models/TimeEntry.php` - Add computed accessors

**Computed Fields to Implement:**
```php
// On TimeEntry model
getActualDurationMinutesAttribute()      // Total actual work time
getScheduledDurationMinutesAttribute()   // Shift scheduled duration
getVarianceMinutesAttribute()            // Difference (+ overtime, - undertime)
getClockInVarianceMinutesAttribute()     // Early/late arrival
getClockOutVarianceMinutesAttribute()    // Early/late departure
getIsLateAttribute()                     // Boolean: arrived late
getIsEarlyDepartureAttribute()           // Boolean: left early
getIsOvertimeAttribute()                 // Boolean: worked overtime
getIsNoShowAttribute()                   // Boolean: missed shift entirely
```

**Tasks:**
- [ ] Add all computed accessor methods to TimeEntry model
- [ ] Create helper methods for variance calculations
- [ ] Add variance display to time entry views
- [ ] Add color coding (green=good, yellow=minor, red=issue)
- [ ] Write unit tests for all calculations

---

## 3.4 Timesheet Views
**Effort: Medium**

View and manage timesheets with scheduled vs actual comparison.

**Files to create:**
- `app/Http/Controllers/TimesheetController.php`
- `resources/views/timesheets/index.blade.php` (admin view)
- `resources/views/timesheets/employee.blade.php` (employee view)
- `resources/views/timesheets/weekly.blade.php` (weekly summary)
- `tests/Feature/TimesheetTest.php`

**Features:**
- Weekly/bi-weekly timesheet views
- Side-by-side scheduled vs actual times
- Variance highlighting (late, early, overtime)
- Total hours summary
- Filter by employee, department, date range
- Manager approval workflow if enabled

**Tasks:**
- [ ] Create TimesheetController
- [ ] Create admin timesheet index (all employees)
- [ ] Create employee timesheet view (own entries)
- [ ] Create weekly summary view with totals
- [ ] Add date range picker
- [ ] Show scheduled shift times alongside actual
- [ ] Calculate and display variances
- [ ] Add approval buttons for managers
- [ ] Add navigation to sidebar
- [ ] Write tests

---

## 3.5 Missed Shift Detection
**Effort: Medium**

Automatically detect and flag missed shifts.

**Files to create:**
- `app/Console/Commands/DetectMissedShiftsCommand.php`
- `app/Notifications/MissedShiftNotification.php`

**Logic:**
- Run via scheduler every 15 minutes
- Find published shifts that started > grace_period ago
- Check if time_entry exists for that shift
- If no entry, create one with status='missed'
- Notify manager of missed shift

**Tasks:**
- [ ] Create DetectMissedShiftsCommand
- [ ] Implement missed shift detection logic
- [ ] Create MissedShiftNotification
- [ ] Register command in scheduler (every 15 min)
- [ ] Add missed shift indicator to dashboards
- [ ] Write tests

---

## 3.6 Auto Clock-Out
**Effort: Small**

Automatically clock out employees at end of day if enabled.

**Files to create:**
- `app/Console/Commands/AutoClockOutCommand.php`

**Tasks:**
- [ ] Create AutoClockOutCommand
- [ ] Find all active clock-ins past auto_clock_out_time
- [ ] Clock them out with status='auto_clocked_out'
- [ ] Add note about automatic clock-out
- [ ] Register command in scheduler (run at configured time)
- [ ] Write tests

---

## 3.7 Attendance Reports
**Effort: Large**

Generate attendance and time variance reports.

**Files to create:**
- `app/Http/Controllers/AttendanceReportController.php`
- `resources/views/reports/attendance/index.blade.php`
- `resources/views/reports/attendance/employee.blade.php`
- `resources/views/reports/attendance/department.blade.php`
- `app/Services/AttendanceReportService.php`
- `tests/Feature/AttendanceReportTest.php`

**Reports to Include:**
| Report | Description |
|--------|-------------|
| Punctuality Report | On-time vs late arrivals by employee/dept |
| Hours Worked Report | Scheduled vs actual hours comparison |
| Overtime Report | Overtime hours by employee/department |
| Absence Report | No-shows and missed shifts |
| Attendance Summary | Overall attendance rate metrics |

**Metrics to Calculate:**
- Attendance rate: (shifts worked / shifts scheduled) x 100
- Punctuality rate: (on-time arrivals / total arrivals) x 100
- Average variance: Mean difference from scheduled times
- Total overtime hours: Sum of positive variances
- Total undertime hours: Sum of negative variances

**Tasks:**
- [ ] Create AttendanceReportService for calculations
- [ ] Create AttendanceReportController
- [ ] Create punctuality report view
- [ ] Create hours worked report with scheduled vs actual
- [ ] Create overtime analysis report
- [ ] Create absence/no-show report
- [ ] Create attendance summary dashboard
- [ ] Add date range filtering
- [ ] Add department/location filtering
- [ ] Add export to CSV/PDF
- [ ] Add charts/visualizations
- [ ] Write tests

---

## 3.8 Timesheet Export
**Effort: Medium**

Export timesheets for payroll processing.

**Files to create:**
- `app/Http/Controllers/TimesheetExportController.php`
- `app/Exports/TimesheetExport.php` (Laravel Excel)
- `app/Services/TimesheetExportService.php`

**Export Formats:**
- CSV (basic)
- Excel (formatted)
- PDF (printable)

**Export Data:**
- Employee name
- Date
- Scheduled start/end
- Actual start/end
- Scheduled hours
- Actual hours
- Variance
- Break time
- Notes
- Status

**Tasks:**
- [ ] Install Laravel Excel package
- [ ] Create TimesheetExportController
- [ ] Create TimesheetExport class
- [ ] Implement CSV export
- [ ] Implement Excel export with formatting
- [ ] Implement PDF export
- [ ] Add export buttons to timesheet views
- [ ] Write tests

---

## 3.9 Kiosk Mode
**Effort: Large**

Shared clock-in terminals for locations where employees don't have individual devices.

**Database Changes:**
- [ ] Create migration for `kiosks` table
- [ ] Create migration for `employee_pins` table
- [ ] Create migration for `kiosk_events` table

**Files to create:**
- `app/Models/Kiosk.php`
- `app/Models/EmployeePin.php`
- `app/Models/KioskEvent.php`
- `app/Services/KioskService.php`
- `app/Http/Controllers/KioskController.php`
- `app/Http/Controllers/KioskTerminalController.php`
- `app/Http/Controllers/EmployeePinController.php`
- `app/Http/Middleware/KioskAuthentication.php`
- `app/Http/Requests/Kiosk/StoreKioskRequest.php`
- `app/Http/Requests/Kiosk/TerminalAuthenticateRequest.php`
- `app/Policies/KioskPolicy.php`
- `resources/views/kiosks/index.blade.php`
- `resources/views/kiosks/create.blade.php`
- `resources/views/kiosks/edit.blade.php`
- `resources/views/kiosk-terminal/index.blade.php`
- `resources/views/kiosk-terminal/clock.blade.php`
- `tests/Feature/KioskTest.php`
- `tests/Feature/KioskTerminalTest.php`

**Features:**
| Feature | Description |
|---------|-------------|
| Kiosk Registration | Admin creates kiosks per location |
| PIN Authentication | Employees enter 4-6 digit PIN |
| Badge Scan | Optional barcode/QR badge scanning |
| QR Code Auth | Employee shows QR from their phone |
| Photo Capture | Optional photo on clock in for verification |
| Touch Interface | Large buttons for tablet/touchscreen |
| Session Timeout | Auto-logout after clock action |
| Manager Override | PIN override for corrections |
| Activity Log | Full audit trail of all kiosk events |
| Remote Lock | Admin can lock kiosk remotely |

**Kiosk Terminal Flow:**
```
1. Kiosk loads terminal view (authenticated via access token)
2. Employee taps "Clock In" or "Clock Out"
3. Employee authenticates (PIN/badge/QR)
4. Optional: Photo capture
5. Clock action processed
6. Confirmation shown with shift details
7. Auto-return to idle screen after timeout
```

**Tasks:**
- [ ] Create Kiosk model with relationships
- [ ] Create EmployeePin model (stores hashed PINs)
- [ ] Create KioskEvent model for audit trail
- [ ] Create KioskService with:
  - `registerKiosk()` - Create new kiosk
  - `generateAccessToken()` - Create secure token
  - `authenticateKiosk()` - Validate kiosk token
  - `authenticateEmployee()` - Verify PIN/badge/QR
  - `processClockAction()` - Handle clock in/out
  - `setEmployeePin()` - Set/update PIN
  - `verifyPin()` - Check PIN with lockout
  - `setKioskLock()` - Remote lock/unlock
  - `getClockedInEmployees()` - List currently clocked in
  - `getActivityLog()` - Audit trail
  - `managerOverride()` - Manager correction
- [ ] Create KioskAuthentication middleware
- [ ] Create KioskController for admin management
- [ ] Create KioskTerminalController for terminal API
- [ ] Create EmployeePinController for PIN management
- [ ] Create kiosk management views:
  - Index with list of kiosks per location
  - Create/edit forms with settings
  - Activity log viewer
- [ ] Create kiosk terminal interface:
  - Full-screen touch-optimized layout
  - Large clock in/out buttons
  - PIN entry keypad
  - Photo capture (using device camera)
  - Confirmation screen with shift details
  - Idle screen with time display
- [ ] Add PIN management to employee profile/settings
- [ ] Add "Set PIN" option in user management for admins
- [ ] Create manager override flow
- [ ] Implement failed attempt lockout (5 attempts, 15 min lock)
- [ ] Add routes with kiosk middleware
- [ ] Create dedicated /kiosk-terminal route for terminals
- [ ] Write tests for authentication flows
- [ ] Write tests for clock actions
- [ ] Write tests for audit logging
- [ ] Test on tablet devices

**Security Considerations:**
- PINs stored as bcrypt hashes
- Kiosk access tokens are long-lived but revokable
- Failed PIN attempts trigger temporary lockout
- All events logged with timestamps
- Photo evidence for disputed entries
- IP/device restrictions optional
