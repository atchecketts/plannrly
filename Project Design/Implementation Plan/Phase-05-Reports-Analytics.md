# Phase 5: Reports & Analytics (Lower Priority)

## 5.1 Reports Dashboard
**Effort: Large**

**Files to create:**
- `app/Http/Controllers/ReportController.php`
- `resources/views/reports/index.blade.php`
- `resources/views/reports/hours.blade.php`
- `resources/views/reports/labor-costs.blade.php`
- `resources/views/reports/leave.blade.php`

**Tasks:**
- [ ] Create ReportController
- [ ] Create reports index/dashboard
- [ ] Implement hours worked report (by employee, department, period)
- [ ] Implement labor costs report (using hourly rates)
- [ ] Implement schedule adherence report (actual vs scheduled)
- [ ] Implement leave usage report
- [ ] Add chart visualizations
- [ ] Add export functionality (CSV, PDF)
- [ ] Write tests

---

## 5.2 Employee Statistics
**Effort: Medium**

Show statistics on employee profile pages.

**Tasks:**
- [ ] Add hours worked this week/month/year to user show page
- [ ] Add leave balance to user show page
- [ ] Add attendance rate (shifts worked vs scheduled)
- [ ] Show shift history on user profile
