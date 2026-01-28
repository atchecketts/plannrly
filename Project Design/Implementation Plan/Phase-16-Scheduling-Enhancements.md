# Phase 16: Scheduling Enhancements

## 16.1 Shift Preferences
**Effort: Medium**

Capture employee shift preferences beyond availability.

**Files to create:**
- `app/Models/UserShiftPreference.php`
- `app/Models/UserCoworkerPreference.php`
- `app/Http/Controllers/ShiftPreferenceController.php`
- `resources/views/settings/shift-preferences.blade.php`

**Tasks:**
- [ ] Create preference migrations
- [ ] Create preference management UI
- [ ] Add day/time preferences with levels (strong, mild, avoid)
- [ ] Add coworker preferences
- [ ] Show preferences in scheduling view
- [ ] Calculate preference satisfaction score
- [ ] Write tests

---

## 16.2 Calendar Integration
**Effort: Medium**

Sync schedules to personal calendars.

**Files to create:**
- `app/Http/Controllers/CalendarFeedController.php`
- `app/Services/ICalService.php`
- `resources/views/settings/calendar-sync.blade.php`

**Tasks:**
- [ ] Create unique iCal feed URL per user
- [ ] Generate ICS format for shifts
- [ ] Include shift details (location, role, notes)
- [ ] Auto-update feed when schedule changes
- [ ] Add feed regeneration if URL compromised
- [ ] Create calendar settings UI
- [ ] Add feed URL copy button
- [ ] Write tests

---

## 16.3 Open Shift Marketplace
**Effort: Large**

Let employees claim unassigned shifts.

**Files to create:**
- `app/Models/OpenShiftClaim.php`
- `app/Services/OpenShiftService.php`
- `app/Http/Controllers/OpenShiftController.php`
- `resources/views/open-shifts/index.blade.php`
- `resources/views/open-shifts/claims.blade.php`

**Tasks:**
- [ ] Create open_shift_claims migration
- [ ] Create open shift list for employees
- [ ] Filter by qualifications and availability
- [ ] Implement claim request flow
- [ ] Create manager approval queue
- [ ] Add auto-approve option per tenant
- [ ] Configure priority rules (seniority, hours, etc.)
- [ ] Send notifications for new open shifts
- [ ] Auto-assign after deadline (optional)
- [ ] Write tests

---

## 16.4 Schedule Templates
**Effort: Large**

Save and reuse schedule patterns.

**Files to create:**
- `app/Models/ScheduleTemplate.php`
- `app/Models/ScheduleTemplateShift.php`
- `app/Services/ScheduleTemplateService.php`
- `app/Http/Controllers/ScheduleTemplateController.php`
- `resources/views/schedule-templates/index.blade.php`
- `resources/views/schedule-templates/create.blade.php`

**Tasks:**
- [ ] Create template migrations
- [ ] Save current schedule as template
- [ ] Create template library view
- [ ] Categorize templates (standard, seasonal, etc.)
- [ ] Apply template to future week
- [ ] Smart employee matching by role
- [ ] Share templates across locations
- [ ] Import/export templates
- [ ] Write tests

---

## 16.5 Smart Fill (Auto-Scheduling Assistant)
**Effort: Large**

One-click filling of unassigned shifts.

**Files to create:**
- `app/Services/SmartFillService.php`
- `app/DTOs/SmartFillResult.php`
- `app/DTOs/ShiftSuggestion.php`

**Tasks:**
- [ ] Create SmartFillService
- [ ] Find candidates based on availability and qualifications
- [ ] Rank candidates by fairness score
- [ ] Respect target hours and overtime limits
- [ ] Consider recent shifts for fairness
- [ ] Generate suggestions with alternatives
- [ ] Add "Smart Fill" button to schedule view
- [ ] Preview suggestions before applying
- [ ] Show warnings (overtime, preferences)
- [ ] Write tests

---

## 16.6 Real-Time Operations Dashboard
**Effort: Medium**

Live view of current workforce status.

**Files to create:**
- `app/Http/Controllers/OperationsDashboardController.php`
- `resources/views/operations/dashboard.blade.php`

**Tasks:**
- [ ] Create "Who's Working Now" view
- [ ] Show clocked-in employees with details
- [ ] Show expected arrivals (next 1-2 hours)
- [ ] Highlight late/missing employees
- [ ] Add quick contact actions
- [ ] Show coverage status per location
- [ ] Auto-refresh every minute
- [ ] Add send reminder action for late employees
- [ ] Write tests

---

## 16.7 Shift Notes & Handover
**Effort: Medium**

Enable communication between shifts.

**Files to create:**
- `app/Models/ShiftNote.php`
- `app/Models/ShiftTask.php`
- `app/Http/Controllers/ShiftNoteController.php`
- `resources/views/shifts/notes.blade.php`

**Tasks:**
- [ ] Create shift_notes and shift_tasks migrations
- [ ] Add manager instructions when creating shift
- [ ] Add employee handover notes during/after shift
- [ ] Create task checklists per shift
- [ ] Track task completion
- [ ] Show handover notes to next shift
- [ ] Require acknowledgment for instructions
- [ ] Write tests

---

## 16.8 Conflict Detection & Warnings
**Effort: Medium**

Proactive alerts for scheduling issues.

**Files to create:**
- `app/Services/ConflictDetectionService.php`
- `app/DTOs/ScheduleConflict.php`

**Tasks:**
- [ ] Create ConflictDetectionService
- [ ] Detect leave conflicts
- [ ] Detect availability conflicts
- [ ] Detect rest period violations
- [ ] Detect overtime warnings
- [ ] Detect qualification gaps
- [ ] Show warnings in schedule view
- [ ] Require acknowledgment for warnings
- [ ] Add "Fix All" suggestions
- [ ] Log overridden warnings
- [ ] Write tests
