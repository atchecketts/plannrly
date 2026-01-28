# Phase 13: SuperAdmin Analytics Dashboard

## 13.1 Revenue Reports
**Effort: Large**

MRR, ARR, and revenue tracking.

**Files to create:**
- `app/Services/SuperAdminAnalyticsService.php`
- `app/Http/Controllers/SuperAdmin/AnalyticsController.php`
- `resources/views/super-admin/analytics/index.blade.php`
- `resources/views/super-admin/analytics/revenue.blade.php`

**Metrics to Display:**
| Metric | Description |
|--------|-------------|
| MRR | Monthly Recurring Revenue |
| ARR | Annual Recurring Revenue |
| MRR Growth | Month-over-month change |
| ARPU | Average Revenue Per User |
| Revenue by Plan | Breakdown by tier |
| Revenue by Add-on | Breakdown by feature |

**Tasks:**
- [ ] Create SuperAdminAnalyticsService
- [ ] Implement MRR calculation
- [ ] Implement ARR calculation
- [ ] Create MRR history chart (12 months)
- [ ] Create revenue by plan chart
- [ ] Create revenue by add-on chart
- [ ] Build revenue dashboard view
- [ ] Add date range filters
- [ ] Write tests

---

## 13.2 Subscription Reports
**Effort: Medium**

Subscription counts and status tracking.

**Files to create:**
- `resources/views/super-admin/analytics/subscriptions.blade.php`

**Metrics to Display:**
| Metric | Description |
|--------|-------------|
| Total Active | Active subscriptions |
| By Plan | Breakdown by tier |
| By Status | Active, past_due, cancelled |
| Trial | Currently in trial |
| Conversions | Trial to paid this period |

**Tasks:**
- [ ] Create subscription counts queries
- [ ] Create subscriptions by plan chart
- [ ] Create subscription status breakdown
- [ ] Add trial conversion tracking
- [ ] Create subscription list with filters
- [ ] Write tests

---

## 13.3 Growth & Churn Reports
**Effort: Medium**

Track growth trends and churn.

**Files to create:**
- `resources/views/super-admin/analytics/growth.blade.php`
- `resources/views/super-admin/analytics/churn.blade.php`

**Growth Metrics:**
- New subscriptions
- Upgrades / Downgrades
- Reactivations
- Net growth

**Churn Metrics:**
- Churn rate (monthly)
- Churned subscriptions
- Churned MRR
- Churn reasons (if collected)
- Revenue retention

**Tasks:**
- [ ] Implement growth metrics calculation
- [ ] Implement churn rate calculation
- [ ] Create growth trends chart
- [ ] Create churn analysis view
- [ ] Add comparison to previous period
- [ ] Write tests

---

## 13.4 Tenant Health Dashboard
**Effort: Medium**

Monitor tenant activity and identify at-risk accounts.

**Files to create:**
- `resources/views/super-admin/analytics/tenant-health.blade.php`

**Health Indicators:**
| Indicator | Healthy | At-Risk |
|-----------|---------|---------|
| Last Login | < 7 days | > 30 days |
| Shifts Created | > 10/week | 0 in 2 weeks |
| Active Users | > 50% | < 20% |
| Payment Status | Current | Past due |

**Tasks:**
- [ ] Create tenant health score algorithm
- [ ] Create at-risk accounts list
- [ ] Add activity metrics per tenant
- [ ] Create health score visualization
- [ ] Add alert thresholds configuration
- [ ] Write tests

---

## 13.5 Analytics Export
**Effort: Small**

Export analytics data for external analysis.

**Tasks:**
- [ ] Create CSV export for revenue data
- [ ] Create CSV export for subscription data
- [ ] Create scheduled email reports option
- [ ] Add date range selection for exports
- [ ] Write tests
