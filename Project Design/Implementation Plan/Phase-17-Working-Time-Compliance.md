# Phase 17: Working Time Compliance

## 17.1 EU Working Time Directive Monitoring
**Effort: Large**

Monitor and warn about working time regulation violations.

**Files to create:**
- `app/Services/WorkingTimeComplianceService.php`
- `app/Models/ComplianceViolation.php`
- `app/Http/Controllers/ComplianceController.php`
- `resources/views/admin/compliance/dashboard.blade.php`
- `resources/views/admin/compliance/report.blade.php`

**Compliance Rules:**
| Rule | Description |
|------|-------------|
| 11h rest | Minimum rest between shifts |
| 48h max/week | Maximum weekly hours (average) |
| 24h rest/week | Weekly rest period |
| 20min break | After 6 hours work |

**Tasks:**
- [ ] Create compliance_violations migration
- [ ] Create WorkingTimeComplianceService
- [ ] Check rest period between shifts
- [ ] Check weekly hours limit
- [ ] Check weekly rest requirement
- [ ] Check break requirements
- [ ] Show warnings when creating shifts
- [ ] Require acknowledgment for violations
- [ ] Log violation overrides with reason
- [ ] Create compliance dashboard for admins
- [ ] Generate compliance reports
- [ ] Configure rules per tenant (enable/disable, thresholds)
- [ ] Write tests
