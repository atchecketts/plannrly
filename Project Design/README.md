# Plannrly Project Design

This folder contains all design documentation for the Plannrly workforce scheduling platform including
- Implementation Status tables in this file
- Implementation Plan files separated by phase
- Review the relevant documents when implementing new phases or features.  Update the documents as needed.
- Ask if you need further clarifications.
- Update this file with completed Phases.


---

## Quick Status

| Category     | Count | Status |
|--------------|-------|--------|
| Total Phases | 30    |        |
| Completed    | 5     | ✅      |
| In Progress  | 1     | 🔵     |
| Not Started  | 23    |        |

### Currently Active

✅ **Phase 1: Core Missing Features** - 7/7 complete
✅ **Phase 6: Employee Management** - 4/4 complete
✅ **Phase 3: Time & Attendance** - 7/8 complete (3.6 Auto Clock-Out deferred)
✅ **Phase 4: Notifications System** - 3/3 complete
🔵 **Phase 2: Enhanced Scheduling Features** - 4/7 complete (2.1 Drag-Drop, 2.2 Copy-Paste, 2.3 Recurring Templates, 2.5 Staffing)

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
- Database schema (all 22 tables)
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
- **Copy and Paste Shifts (context menu, keyboard shortcuts, clipboard state)**
- **Recurring Shift Templates (daily/weekly/monthly, edit/delete scope, visual indicators)**
- **Shift resize on day view**
- **Shift Swap Views (index & create forms)**
- **Employee Dashboard (personalized view)**
- **Leave Types Management (full CRUD)**
- **Leave Allowances Management (full CRUD)**
- **Tenant Settings Management (edit form)**
- **Subscription & Feature Management (plans, add-ons, feature gating)**
- **Employee Profile (self-service contact details, avatar, password)**
- **Employee HR Records (employment status, pay rates, per-employee role hourly rates, hours constraints)**
- **Employee Availability Management (recurring weekly, specific dates, preferences)**
- **Sortable & Groupable Table Headers (all 10 list views with query string persistence)**
- **Staffing Requirements & Coverage Warnings (min/max staffing, schedule integration)**
- **Clock In/Out Core System (employee clock, breaks, GPS, manager approval workflow)**
- **Clock Widget Component (AJAX actions, GPS capture, confirmation dialogs, inline messages)**
- **Scheduled vs Actual Variance (late/early detection, overtime, color-coded badges)**
- **Timesheet Views (weekly admin/employee views, batch approval, week navigation)**
- **Missed Shift Detection (auto-detection, notifications, dashboard alerts)**
- **Attendance Reports (punctuality, hours worked, overtime, absence, CSV export)**
- **Timesheet Export (detailed CSV, payroll CSV, employee self-service export)**
- **Notifications System (shift changes, leave status, swap requests, reminders, preferences UI, in-app bell)**

### Partially Implemented
- Phase 2: Enhanced Scheduling (2.1 Drag-Drop, 2.2 Copy-Paste, 2.3 Recurring Templates, 2.5 Staffing done, 3 remaining)
- Phase 3: Time & Attendance (3.1-3.5, 3.7-3.8 done, 3.6 deferred)

---

## Phase Index

| Phase | Name | Priority | Status |
|-------|------|----------|--------|
| 1 | [Core Missing Features](Implementation%20Plan/Phase-01-Core-Features.md) | High | ✅ Complete |
| 2 | [Enhanced Scheduling Features](Implementation%20Plan/Phase-02-Scheduling-Features.md) | Medium | 🔵 4/7 |
| 3 | [Time & Attendance](Implementation%20Plan/Phase-03-Time-Attendance.md) | High | ✅ 7/8 |
| 4 | [Notifications System](Implementation%20Plan/Phase-04-Notifications.md) | Medium | ✅ Complete |
| 5 | [Reports & Analytics](Implementation%20Plan/Phase-05-Reports-Analytics.md) | Lower | ⬜ |
| 6 | [Employee Management](Implementation%20Plan/Phase-06-Employee-Management.md) | High | ✅ Complete |
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
| 30 | [Partner & Professional Services](Implementation%20Plan/Phase-30-Partner-Professional-Services.md) | Low | ⬜ |

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
| [1.7 Sortable Table Headers](Implementation%20Plan/Features/1.7-Sortable-Table-Headers.md) | ✅ Done |

### Phase 2: Enhanced Scheduling 🔵

| Feature | Status |
|---------|--------|
| [2.1 Drag-and-Drop Shifts](Implementation%20Plan/Features/2.1-Drag-Drop-Shift-Management.md) | ✅ Done |
| [2.2 Copy and Paste Shifts](Implementation%20Plan/Features/2.2-Copy-Paste-Shifts.md) | ✅ Done |
| [2.3 Recurring Shift Templates](Implementation%20Plan/Features/2.3-Recurring-Shift-Templates.md) | ✅ Done |
| [2.4 Schedule History](Implementation%20Plan/Features/2.4-Schedule-History.md) | ⬜ Pending |
| [2.5 Staffing Requirements](Implementation%20Plan/Features/2.5-Staffing-Requirements.md) | ✅ Done |
| [2.6 Labor Cost Budgeting](Implementation%20Plan/Features/2.6-Labor-Cost-Budgeting.md) | ⬜ Pending |
| [2.7 Print Schedule](Implementation%20Plan/Features/2.7-Print-Schedule.md) | ⬜ Pending |

### Phase 3: Time & Attendance 🔵

| Feature | Status |
|---------|--------|
| [3.1 Clock In/Out Core System](Implementation%20Plan/Features/3.1-Clock-In-Out-Core-System.md) | ✅ Done |
| [3.2 Clock Widget Component](Implementation%20Plan/Features/3.2-Clock-Widget-Component.md) | ✅ Done |
| [3.3 Scheduled vs Actual Variance](Implementation%20Plan/Features/3.3-Scheduled-Actual-Time-Variance.md) | ✅ Done |
| [3.4 Timesheet Views](Implementation%20Plan/Features/3.4-Timesheet-Views.md) | ✅ Done |
| [3.5 Missed Shift Detection](Implementation%20Plan/Features/3.5-Missed-Shift-Detection.md) | ✅ Done |
| [3.6 Auto Clock Out](Implementation%20Plan/Features/3.6-Auto-Clock-Out.md) | 🔸 Deferred |
| [3.7 Attendance Reports](Implementation%20Plan/Features/3.7-Attendance-Reports.md) | ✅ Done |
| [3.8 Timesheet Export](Implementation%20Plan/Features/3.8-Timesheet-Export.md) | ✅ Done |

### Phase 4: Notifications System ✅

| Feature | Status |
|---------|--------|
| [4.1 Additional Notifications](Implementation%20Plan/Features/4.1-Additional-Notifications.md) | ✅ Done |
| [4.2 Notification Preferences UI](Implementation%20Plan/Features/4.2-Notification-Preferences-UI.md) | ✅ Done |
| [4.3 In-App Notifications UI](Implementation%20Plan/Features/4.3-In-App-Notifications-UI.md) | ✅ Done |

### Phase 6: Employee Management ✅

| Feature | Status |
|---------|--------|
| [6.1 Employee Profile](Implementation%20Plan/Features/6.1-Employee-Profile.md) | ✅ Done |
| [6.2 Employee HR Records](Implementation%20Plan/Features/6.2-Employee-HR-Records.md) | ✅ Done |
| [6.3 Employee Availability](Implementation%20Plan/Features/6.3-Employee-Availability-Management.md) | ✅ Done |
| [6.4 Leave Calendar View](Implementation%20Plan/Features/6.4-Leave-Calendar-View.md) | ✅ Done |

---

## Recommended Implementation Order

### Completed ✅

1. ~~Phase 1.1-1.5 - Core missing features~~ ✅
2. ~~Phase 1.6 - Subscription & Feature Management~~ ✅
3. ~~Phase 1.7 - Sortable Table Headers~~ ✅
4. ~~Phase 6.1-6.4 - Employee Management (Profile, HR Records, Availability, Leave Calendar)~~ ✅
5. ~~Phase 2.1 - Drag-and-Drop Shift Management~~ ✅
6. ~~Phase 2.5 - Staffing Requirements & Coverage~~ ✅
7. ~~Phase 3.1 - Clock In/Out Core System~~ ✅
8. ~~Phase 3.2 - Clock Widget Component~~ ✅
9. ~~Phase 3.3 - Scheduled vs Actual Variance~~ ✅
10. ~~Phase 3.4 - Timesheet Views~~ ✅
11. ~~Phase 3.5 - Missed Shift Detection~~ ✅
12. ~~Phase 3.7 - Attendance Reports~~ ✅
13. ~~Phase 3.8 - Timesheet Export~~ ✅
14. ~~Phase 4 - Notifications System (shift changes, leave status, swap requests, reminders)~~ ✅
15. ~~Phase 2.2 - Copy and Paste Shifts (context menu, keyboard shortcuts)~~ ✅
16. ~~Phase 2.3 - Recurring Shift Templates (daily/weekly/monthly, edit/delete scope)~~ ✅

### Up Next

17. **Phase 2.4** - Schedule History
18. **Phase 2.6** - Labor Cost Budgeting
18. **Phase 2.6** - Labor Cost Budgeting
19. **Phase 2.7** - Print Schedule
20. **Phase 7** - AI Scheduling *(Premium)*

### Core Business Features

21. **Phase 11** - Marketing & Public Pages
22. **Phase 12** - Stripe Payment Integration
23. **Phase 13** - SuperAdmin Analytics
24. **Phase 5** - Reports when data exists
25. **Phase 8** - Import/export capabilities
26. **Phase 9** - Mobile and accessibility
27. **Phase 10** - Premium Add-On Features

### Advanced Features

28. **Phase 16** - Scheduling Enhancements (templates, conflicts, multi-location)
29. **Phase 18** - Analytics Enhancements (advanced reporting)
30. **Phase 19** - Technical Infrastructure (queues, caching, performance)
31. **Phase 20** - Onboarding & Import (guided setup, bulk imports)
32. **Phase 21** - Business Model Features (trial management, upsells)
33. **Phase 22** - Data Retention & Notifications (archival, advanced alerts)
34. **Phase 24** - Delegation & Messaging (admin delegation, internal messaging)
35. **Phase 25** - Custom Reports (report builder, saved reports)
36. **Phase 26** - Billing & Search (invoicing, global search)
37. **Phase 27** - Advanced Shift Features (split shifts, overtime rules)
38. **Phase 28** - Workflows & Visibility (approval chains, schedule visibility)
39. **Phase 29** - Employee Lifecycle (onboarding, offboarding, document management)

### Deferred Features

40. **Phase 3.6** - Auto Clock-Out (needs more consideration)
41. **Phase 14** - Security Features (2FA, session management, audit logs)
42. **Phase 15** - GDPR Compliance (data export, data deletion)
43. **Phase 17** - Working Time Compliance (UK working time regulations)
44. **Phase 23** - Offline & Self-Service (PWA offline, employee self-service)
45. **Phase 30** - Partner & Professional Services (reseller program, consulting)

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
| HIGH_LEVEL_DESIGN.md | 1.14 | January 2026 |
| LOW_LEVEL_DESIGN.md | 1.16 | January 2026 |

---

*Document Version: 1.30*
*Last Updated: January 2026*
