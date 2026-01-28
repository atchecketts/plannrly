# Phase 27: Advanced Shift Features

## 27.1 Advanced Shift Patterns
**Effort: Large**

Support for split, rotating, on-call, and overnight shifts.

**Database Changes:**
- [ ] Create migration for `shift_patterns` table
- [ ] Create migration for `rotation_schedules` table
- [ ] Create migration for `rotation_assignments` table
- [ ] Add shift_type, parent_shift_id, is_on_call, crosses_midnight to shifts

**Files to create:**
- `app/Models/ShiftPattern.php`
- `app/Models/RotationSchedule.php`
- `app/Models/RotationAssignment.php`
- `app/Services/ShiftPatternService.php`
- `app/Enums/ShiftType.php`
- `app/Http/Controllers/RotationController.php`
- `resources/views/rotations/index.blade.php`
- `tests/Feature/ShiftPatternTest.php`

**Tasks:**
- [ ] Create shift pattern models
- [ ] Implement split shift creation and display
- [ ] Implement rotation schedule builder
- [ ] Create rotation assignment UI
- [ ] Handle overnight shifts crossing midnight
- [ ] Add on-call shift support with special rates
- [ ] Update schedule views for new shift types
- [ ] Write tests for all patterns

---

## 27.2 Public Holiday Management
**Effort: Medium**

Holiday import, pay rates, and scheduling integration.

**Database Changes:**
- [ ] Create migration for `public_holidays` table
- [ ] Create migration for `holiday_work_preferences` table

**Files to create:**
- `app/Models/PublicHoliday.php`
- `app/Models/HolidayWorkPreference.php`
- `app/Services/HolidayService.php`
- `app/Http/Controllers/HolidayController.php`
- `resources/views/settings/holidays.blade.php`
- `tests/Feature/HolidayTest.php`

**Tasks:**
- [ ] Create holiday models
- [ ] Integrate holiday API (Nager.Date or similar)
- [ ] Implement country/region holiday import
- [ ] Add holiday pay multiplier support
- [ ] Show holidays on schedule calendar
- [ ] Create holiday work preference submission
- [ ] Calculate holiday pay in timesheets
- [ ] Write tests

---

## 27.3 Urgent Shift Coverage
**Effort: Large**

Find replacement workflow for last-minute vacancies.

**Database Changes:**
- [ ] Create migration for `coverage_requests` table
- [ ] Create migration for `coverage_responses` table
- [ ] Create migration for `coverage_notifications` table

**Files to create:**
- `app/Models/CoverageRequest.php`
- `app/Models/CoverageResponse.php`
- `app/Services/UrgentCoverageService.php`
- `app/Http/Controllers/CoverageController.php`
- `app/Notifications/UrgentCoverageNotification.php`
- `resources/views/coverage/index.blade.php`
- `tests/Feature/UrgentCoverageTest.php`

**Tasks:**
- [ ] Create coverage request models
- [ ] Implement priority calculation (critical/urgent/normal)
- [ ] Create escalated notification system (push+SMS)
- [ ] Build employee response interface
- [ ] Implement auto-assign option
- [ ] Create manager coverage dashboard
- [ ] Add coverage tracking to reports
- [ ] Write tests
