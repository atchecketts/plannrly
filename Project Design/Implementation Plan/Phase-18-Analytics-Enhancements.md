# Phase 18: Analytics Enhancements

## 18.1 Schedule Fairness Analytics
**Effort: Medium**

Measure and improve schedule fairness.

**Files to create:**
- `app/Services/FairnessAnalyticsService.php`
- `resources/views/analytics/fairness.blade.php`

**Tasks:**
- [ ] Create FairnessAnalyticsService
- [ ] Calculate weekend distribution fairness
- [ ] Calculate holiday distribution fairness
- [ ] Calculate preference satisfaction score
- [ ] Calculate hours variance from target
- [ ] Calculate short-notice change impact
- [ ] Generate per-employee fairness score
- [ ] Show team fairness dashboard
- [ ] Warn when scheduling creates unfairness
- [ ] Write tests

---

## 18.2 Predictive Absence Analytics
**Effort: Medium**

Identify absence patterns and predict risks.

**Files to create:**
- `app/Services/AbsenceAnalyticsService.php`
- `resources/views/analytics/absence-patterns.blade.php`

**Tasks:**
- [ ] Create AbsenceAnalyticsService
- [ ] Detect Monday/Friday patterns
- [ ] Detect pre/post holiday patterns
- [ ] Detect seasonal patterns
- [ ] Calculate absence trend per employee
- [ ] Generate risk scores
- [ ] Create pattern visualization
- [ ] Add manager alerts for at-risk employees
- [ ] Write tests
