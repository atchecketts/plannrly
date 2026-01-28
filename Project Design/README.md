# Plannrly Project Design

This folder contains all design documentation for the Plannrly workforce scheduling platform.

---

## Quick Status

| Category | Count | Status |
|----------|-------|--------|
| Total Phases | 29 | |
| Completed | 2 | ✅ |
| In Progress | 1 | 🔵 |
| Not Started | 26 | |

### Currently Active

✅ **Phase 1: Core Missing Features** - 6/6 complete
✅ **Phase 6: Employee Management** - 3/4 complete (6.1-6.3 done)
🔵 **Phase 2: Enhanced Scheduling Features** - 1/7 complete (2.1 Drag-Drop done)

📊 **Progress is tracked in the tables below**

---

## Documents

| Document | Description |
|----------|-------------|
| [HIGH_LEVEL_DESIGN.md](HIGH_LEVEL_DESIGN.md) | Strategic overview of features, user flows, and business requirements |
| [LOW_LEVEL_DESIGN.md](LOW_LEVEL_DESIGN.md) | Technical implementation details, database schemas, services, and APIs |
| [UAT/](UAT/) | User Acceptance Testing - manual test cases for QA |

---

## Implementation Status

### Fully Implemented
- Multi-tenant architecture with data isolation
- Database schema (all 21 tables)
- Authentication (login/registration)
- Role-based access control (5-tier: SuperAdmin, Admin, LocationAdmin, DepartmentAdmin, Employee)
- Super Admin tenant management & impersonation
- Location CRUD with views
- Department CRUD with views
- Business Role CRUD with views
- User CRUD with role assignment
- Leave Request workflow (create, submit, review)
- Schedule Week View with filtering
- Schedule Day View with timeline
- Shift CRUD (create, update, delete, assign)
- Draft/Publish workflow for shifts
- Shift status automation command
- Admin/Location Admin/Department Admin dashboards
- User filter preferences
- Shift published notification
- **Drag-and-Drop shift management (week & day views)**
- **Shift resize on day view**
- **Shift Swap Views (index & create forms)**
- **Employee Dashboard (personalized view)**
- **Leave Types Management (full CRUD)**
- **Leave Allowances Management (full CRUD)**
- **Tenant Settings Management (edit form)**
- **Subscription & Feature Management (plans, add-ons, feature gating)**
- **Employee Profile (self-service contact details, avatar, password)**
- **Employee HR Records (employment status, pay rates, hours constraints)**
- **Employee Availability Management (recurring weekly, specific dates, preferences)**

### Partially Implemented
- Phase 2: Enhanced Scheduling (2.1 Drag-Drop done, 6 remaining)
- Phase 6: Employee Management (6.1-6.3 done, 6.4 Leave Calendar remaining)

---

## Phase Index

| Phase | Name | Priority | Status |
|-------|------|----------|--------|
| 1 | [Core Missing Features](Implementation%20Plan/Phase-01-Core-Features.md) | High | ✅ Complete |
| 2 | [Enhanced Scheduling Features](Implementation%20Plan/Phase-02-Scheduling-Features.md) | Medium | 🔵 1/7 |
| 3 | [Time & Attendance](Implementation%20Plan/Phase-03-Time-Attendance.md) | High | ⬜ |
| 4 | [Notifications System](Implementation%20Plan/Phase-04-Notifications.md) | Medium | ⬜ |
| 5 | [Reports & Analytics](Implementation%20Plan/Phase-05-Reports-Analytics.md) | Lower | ⬜ |
| 6 | [Employee Management](Implementation%20Plan/Phase-06-Employee-Management.md) | High | ✅ 3/4 |
| 7 | [AI Scheduling](Implementation%20Plan/Phase-07-AI-Scheduling.md) *(Premium)* | Medium | ⬜ |
| 8 | [Data Import/Export](Implementation%20Plan/Phase-08-Import-Export.md) | Lower | ⬜ |
| 9 | [Mobile Interface](Implementation%20Plan/Phase-09-Mobile-Interface.md) | High | ⬜ |
| 10 | [Premium Add-On Features](Implementation%20Plan/Phase-10-Premium-Features.md) | Medium | ⬜ |
| 11 | [Marketing & Public Pages](Implementation%20Plan/Phase-11-Marketing-Pages.md) | High | ⬜ |
| 12 | [Stripe Payment Integration](Implementation%20Plan/Phase-12-Stripe-Payments.md) | High | ⬜ |
| 13 | [SuperAdmin Analytics](Implementation%20Plan/Phase-13-SuperAdmin-Analytics.md) | Medium | ⬜ |
| 14 | [Security Features](Implementation%20Plan/Phase-14-Security.md) | High | ⬜ |
| 15 | [GDPR Compliance](Implementation%20Plan/Phase-15-GDPR-Compliance.md) | High | ⬜ |
| 16 | [Scheduling Enhancements](Implementation%20Plan/Phase-16-Scheduling-Enhancements.md) | High | ⬜ |
| 17 | [Working Time Compliance](Implementation%20Plan/Phase-17-Working-Time-Compliance.md) | High | ⬜ |
| 18 | [Analytics Enhancements](Implementation%20Plan/Phase-18-Analytics-Enhancements.md) | Medium | ⬜ |
| 19 | [Technical Infrastructure](Implementation%20Plan/Phase-19-Technical-Infrastructure.md) | Medium | ⬜ |
| 20 | [Onboarding & Import](Implementation%20Plan/Phase-20-Onboarding-Import.md) | High | ⬜ |
| 21 | [Business Model Features](Implementation%20Plan/Phase-21-Business-Model.md) | High | ⬜ |
| 22 | [Data Retention & Notifications](Implementation%20Plan/Phase-22-Data-Retention-Notifications.md) | Medium | ⬜ |
| 23 | [Offline & Self-Service](Implementation%20Plan/Phase-23-Offline-Self-Service.md) | High | ⬜ |
| 24 | [Delegation & Messaging](Implementation%20Plan/Phase-24-Delegation-Messaging.md) | High | ⬜ |
| 25 | [Custom Reports](Implementation%20Plan/Phase-25-Custom-Reports.md) | Medium | ⬜ |
| 26 | [Billing & Search](Implementation%20Plan/Phase-26-Billing-Search.md) | High | ⬜ |
| 27 | [Advanced Shift Features](Implementation%20Plan/Phase-27-Advanced-Shifts.md) | High | ⬜ |
| 28 | [Workflows & Visibility](Implementation%20Plan/Phase-28-Workflows-Visibility.md) | Medium | ⬜ |
| 29 | [Employee Lifecycle](Implementation%20Plan/Phase-29-Employee-Lifecycle.md) | High | ⬜ |

---

## Feature Summary

### Phase 1: Core Features ✅

| Feature | Status |
|---------|--------|
| [1.1 Shift Swap Views](Implementation%20Plan/Features/1.1-Shift-Swap-Views.md) | ✅ Done |
| [1.2 Employee Dashboard](Implementation%20Plan/Features/1.2-Employee-Dashboard.md) | ✅ Done |
| [1.3 Leave Types Management](Implementation%20Plan/Features/1.3-Leave-Types-Management.md) | ✅ Done |
| [1.4 Leave Allowances Management](Implementation%20Plan/Features/1.4-Leave-Allowances-Management.md) | ✅ Done |
| [1.5 Tenant Settings Management](Implementation%20Plan/Features/1.5-Tenant-Settings-Management.md) | ✅ Done |
| [1.6 Subscription & Feature Management](Implementation%20Plan/Features/1.6-Subscription-Feature-Management.md) | ✅ Done |

### Phase 2: Enhanced Scheduling 🔵

| Feature | Status |
|---------|--------|
| [2.1 Drag-and-Drop Shifts](Implementation%20Plan/Features/2.1-Drag-Drop-Shift-Management.md) | ✅ Done |
| [2.2 Copy and Paste Shifts](Implementation%20Plan/Features/2.2-Copy-Paste-Shifts.md) | ⬜ Pending |
| [2.3 Recurring Shift Templates](Implementation%20Plan/Features/2.3-Recurring-Shift-Templates.md) | ⬜ Pending |
| [2.4 Schedule History](Implementation%20Plan/Features/2.4-Schedule-History.md) | ⬜ Pending |
| [2.5 Staffing Requirements](Implementation%20Plan/Features/2.5-Staffing-Requirements.md) | ⬜ Pending |
| [2.6 Labor Cost Budgeting](Implementation%20Plan/Features/2.6-Labor-Cost-Budgeting.md) | ⬜ Pending |
| [2.7 Print Schedule](Implementation%20Plan/Features/2.7-Print-Schedule.md) | ⬜ Pending |

### Phase 6: Employee Management ✅

| Feature | Status |
|---------|--------|
| [6.1 Employee Profile](Implementation%20Plan/Features/6.1-Employee-Profile.md) | ✅ Done |
| [6.2 Employee HR Records](Implementation%20Plan/Features/6.2-Employee-HR-Records.md) | ✅ Done |
| [6.3 Employee Availability](Implementation%20Plan/Features/6.3-Employee-Availability-Management.md) | ✅ Done |
| [6.4 Leave Calendar View](Implementation%20Plan/Features/6.4-Leave-Calendar-View.md) | ⬜ Pending |

---

## Recommended Implementation Order

1. ~~Phase 1.1-1.5 - Core missing features~~ ✅ **DONE**
2. ~~Phase 1.6 - Subscription & Feature Management~~ ✅ **DONE**
3. ~~Phase 6.1-6.3 - Employee Management (required for AI scheduling)~~ ✅ **DONE**
4. **Phase 2.5** - Staffing Requirements & Coverage (required for AI scheduling)
5. **Phase 3.1-3.5** - Time & Attendance core features
6. **Phase 3.6-3.8** - Complete Time & Attendance
7. **Phase 7** - AI Scheduling *(Premium)*
8. **Phase 4** - Notifications (including clock-in reminders)
9. ~~Phase 2.1 - Drag-drop~~ ✅ **DONE**
10. **Phase 2.2** - Copy-paste for better UX
11. **Phase 2.7** - Print Schedule
12. **Phase 11** - Marketing & Public Pages
13. **Phase 12** - Stripe Payment Integration
14. **Phase 13** - SuperAdmin Analytics
15. **Phase 5** - Reports when data exists
16. **Phase 8** - Import/export capabilities
17. **Phase 9** - Mobile and accessibility
18. **Phase 10** - Premium Add-On Features
19. **Phase 14** - Security Features
20. **Phase 15** - GDPR Compliance

---

## Quick Links by Feature Area

| Area | HLD Section | LLD Section | Implementation |
|------|-------------|-------------|----------------|
| Scheduling | Key Features | 2.1-2.5 | Phase 2, 16 |
| Time & Attendance | Time Tracking | 2.6-2.10 | Phase 3 |
| Employee Management | User Roles | 2.11-2.15 | Phase 6, 29 |
| AI Scheduling | Premium | 2.16 | Phase 7 |
| Mobile/PWA | Mobile Experience | 2.31 | Phase 9 |
| Payments | Subscription | 2.36 | Phase 12, 26 |
| Security | Security | 3.x | Phase 14, 15 |

---

## Document Versions

| Document | Version | Last Updated |
|----------|---------|--------------|
| HIGH_LEVEL_DESIGN.md | 1.13 | January 2026 |
| LOW_LEVEL_DESIGN.md | 1.15 | January 2026 |

---

*Document Version: 1.18*
*Last Updated: January 2026*
