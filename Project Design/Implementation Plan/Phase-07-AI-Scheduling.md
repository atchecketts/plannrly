# Phase 7: AI Scheduling *(Premium Add-On)*

> **Prerequisite:** Phase 1.6 (Subscription & Feature Management) must be completed first. AI Scheduling is a premium feature that requires the `ai_scheduling` add-on or Professional/Enterprise subscription plan.

## 7.1 AI Scheduling Service Core
**Effort: Large**

Build the intelligent scheduling engine.

**Files to create:**
- `app/Services/Scheduling/AISchedulingService.php`
- `app/Services/Scheduling/ScheduleOptimizer.php`
- `app/Services/Scheduling/ScheduleContext.php`
- `app/Services/Scheduling/ScheduleSuggestion.php`
- `app/Services/Scheduling/ScheduleAnalysis.php`
- `app/Services/Scheduling/Constraints/HardConstraint.php`
- `app/Services/Scheduling/Constraints/SoftConstraint.php`
- `tests/Unit/Services/AISchedulingServiceTest.php`
- `tests/Feature/AISchedulingTest.php`

**Hard Constraints (Must satisfy):**
| Constraint | Description |
|------------|-------------|
| Role Qualification | Employee has required business role |
| Availability | Employee available during shift hours |
| Not On Leave | No approved leave during shift |
| No Overlap | Not already scheduled for overlapping shift |
| Active Employment | Employment status is active |
| Max Hours | Doesn't exceed max_hours_per_week |

**Soft Constraints (Optimize):**
| Constraint | Weight | Description |
|------------|--------|-------------|
| Target Hours | High | Meet target_hours_per_week |
| Preference Level | Medium | Honor availability preferences |
| Fair Distribution | Medium | Balance hours across employees |
| Minimize Overtime | Medium | Avoid exceeding target hours |
| Consecutive Shifts | Low | Group shifts when possible |

**Tasks:**
- [ ] Create AISchedulingService with main interface
- [ ] Implement ScheduleContext to track state during optimization
- [ ] Implement hard constraint checkers
- [ ] Implement soft constraint scorers
- [ ] Create ScheduleOptimizer with scoring algorithm
- [ ] Create ScheduleSuggestion response class
- [ ] Create ScheduleAnalysis for schedule quality metrics
- [ ] Write comprehensive unit tests
- [ ] Write integration tests

---

## 7.2 AI Scheduling API & UI
**Effort: Medium**

Expose AI scheduling through API and UI controls.

**Files to create:**
- `app/Http/Controllers/AIScheduleController.php`
- `app/Http/Requests/AISchedule/GenerateRequest.php`
- `app/Http/Requests/AISchedule/FillUnassignedRequest.php`
- `resources/views/components/ai-schedule-modal.blade.php`
- `resources/js/ai-scheduling.js`

**API Endpoints:**
```
POST /schedule/ai/generate         - Generate full schedule
POST /schedule/ai/fill-unassigned  - Fill only unassigned shifts
POST /schedule/ai/find-replacement/{shift} - Find replacement employee
POST /schedule/ai/analyze          - Analyze current schedule
```

**UI Features:**
- "Auto-Fill" button on schedule toolbar
- "Generate Schedule" modal with options
- Preview suggested assignments before applying
- Show warnings and conflicts
- One-click apply suggestions
- Undo capability

**Tasks:**
- [ ] Create AIScheduleController
- [ ] Create request validation classes
- [ ] Add API routes with `feature:ai_scheduling` middleware
- [ ] Create AI scheduling modal component
- [ ] Add "Auto-Fill Shifts" button to schedule toolbar (conditionally shown)
- [ ] Implement preview/apply workflow
- [ ] Show scoring breakdown for assignments
- [ ] Display warnings for unfillable shifts
- [ ] Add analytics/metrics display
- [ ] Use `@feature('ai_scheduling')` directive to show/hide UI
- [ ] Show upgrade prompt when feature not enabled
- [ ] Write tests (including feature gate tests)

---

## 7.3 Schedule Analysis & Recommendations
**Effort: Medium**

Analyze schedules and provide improvement suggestions.

**Features:**
- Identify understaffed periods
- Flag overtime risks
- Highlight availability conflicts
- Show target hours progress per employee
- Suggest schedule improvements

**Tasks:**
- [ ] Implement schedule gap analysis
- [ ] Create overtime prediction
- [ ] Build target hours tracking dashboard
- [ ] Create schedule quality score display
- [ ] Add recommendations panel to schedule view
- [ ] Write tests
