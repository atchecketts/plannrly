# User Acceptance Testing (UAT)

This folder contains manual test cases for each feature in Plannrly. These tests are designed to be performed by QA testers or stakeholders to verify features work correctly from an end-user perspective.

## Naming Convention

Files are named using the format: `{Phase}.{Feature}-{Feature-Name}-UAT.md`

Example: `1.1-Shift-Swap-Views-UAT.md` = UAT for Feature 1.1

## How to Use These Tests

### Before Testing

1. **Environment**: Ensure you have access to a test environment with test data
2. **Users**: Have test accounts ready (admin, employee, etc.)
3. **Data**: Create any prerequisite data mentioned in each test file

### During Testing

1. Follow each test case step-by-step
2. Check off the expected results as you verify them
3. Note any failures or unexpected behavior
4. Take screenshots of issues

### After Testing

1. Report any failures with:
   - Test case ID (e.g., TC-1.1.1)
   - Steps to reproduce
   - Expected vs actual result
   - Screenshot if applicable
   - Browser and device used

## Test Case Structure

Each test case includes:

- **Test Case ID**: Unique identifier (TC-X.X.X)
- **Steps**: Numbered actions to perform
- **Expected Results**: Checkboxes for each verification point

---

## Phase 1: Core Features

| Feature | UAT Document | Tests |
|---------|--------------|-------|
| 1.1 Shift Swap Views | [1.1-Shift-Swap-Views-UAT.md](1.1-Shift-Swap-Views-UAT.md) | 11 tests |
| 1.2 Employee Dashboard | [1.2-Employee-Dashboard-UAT.md](1.2-Employee-Dashboard-UAT.md) | 11 tests |
| 1.3 Leave Types Management | [1.3-Leave-Types-Management-UAT.md](1.3-Leave-Types-Management-UAT.md) | 11 tests |
| 1.4 Leave Allowances Management | [1.4-Leave-Allowances-Management-UAT.md](1.4-Leave-Allowances-Management-UAT.md) | 13 tests |
| 1.5 Tenant Settings Management | [1.5-Tenant-Settings-Management-UAT.md](1.5-Tenant-Settings-Management-UAT.md) | 14 tests |
| 1.6 Subscription & Feature Management | [1.6-Subscription-Feature-Management-UAT.md](1.6-Subscription-Feature-Management-UAT.md) | 24 tests |

## Phase 2: Enhanced Scheduling Features

| Feature | UAT Document |
|---------|--------------|
| 2.1 Drag-and-Drop Shift Management | [2.1-Drag-Drop-Shift-Management-UAT.md](2.1-Drag-Drop-Shift-Management-UAT.md) |
| 2.2 Copy and Paste Shifts | [2.2-Copy-Paste-Shifts-UAT.md](2.2-Copy-Paste-Shifts-UAT.md) |
| 2.3 Recurring Shift Templates | [2.3-Recurring-Shift-Templates-UAT.md](2.3-Recurring-Shift-Templates-UAT.md) |
| 2.4 Schedule History | [2.4-Schedule-History-UAT.md](2.4-Schedule-History-UAT.md) |
| 2.5 Staffing Requirements | [2.5-Staffing-Requirements-UAT.md](2.5-Staffing-Requirements-UAT.md) |
| 2.6 Labor Cost Budgeting | [2.6-Labor-Cost-Budgeting-UAT.md](2.6-Labor-Cost-Budgeting-UAT.md) |
| 2.7 Print Schedule | [2.7-Print-Schedule-UAT.md](2.7-Print-Schedule-UAT.md) |

## Phase 3: Time & Attendance

| Feature | UAT Document |
|---------|--------------|
| 3.1 Clock In/Out Core System | [3.1-Clock-In-Out-Core-UAT.md](3.1-Clock-In-Out-Core-UAT.md) |
| 3.2 Clock Widget Component | [3.2-Clock-Widget-UAT.md](3.2-Clock-Widget-UAT.md) |
| 3.3 Time Variance | [3.3-Time-Variance-UAT.md](3.3-Time-Variance-UAT.md) |
| 3.4 Timesheet Views | [3.4-Timesheet-Views-UAT.md](3.4-Timesheet-Views-UAT.md) |
| 3.5 Missed Shift Detection | [3.5-Missed-Shift-Detection-UAT.md](3.5-Missed-Shift-Detection-UAT.md) |
| 3.6 Auto Clock-Out | [3.6-Auto-Clock-Out-UAT.md](3.6-Auto-Clock-Out-UAT.md) |
| 3.7 Attendance Reports | [3.7-Attendance-Reports-UAT.md](3.7-Attendance-Reports-UAT.md) |
| 3.8 Timesheet Export | [3.8-Timesheet-Export-UAT.md](3.8-Timesheet-Export-UAT.md) |
| 3.9 Kiosk Mode | [3.9-Kiosk-Mode-UAT.md](3.9-Kiosk-Mode-UAT.md) |

## Phase 4: Notifications System

| Feature | UAT Document |
|---------|--------------|
| 4.1 Additional Notifications | [4.1-Additional-Notifications-UAT.md](4.1-Additional-Notifications-UAT.md) |
| 4.2 Notification Preferences | [4.2-Notification-Preferences-UAT.md](4.2-Notification-Preferences-UAT.md) |
| 4.3 In-App Notifications | [4.3-In-App-Notifications-UAT.md](4.3-In-App-Notifications-UAT.md) |

## Phase 5: Reports & Analytics

| Feature | UAT Document |
|---------|--------------|
| 5.1 Reports Dashboard | [5.1-Reports-Dashboard-UAT.md](5.1-Reports-Dashboard-UAT.md) |
| 5.2 Employee Statistics | [5.2-Employee-Statistics-UAT.md](5.2-Employee-Statistics-UAT.md) |

## Phase 6: Employee Management & Self-Service

| Feature | UAT Document |
|---------|--------------|
| 6.1 Employee Profile | [6.1-Employee-Profile-UAT.md](6.1-Employee-Profile-UAT.md) |
| 6.2 Employee HR Records | [6.2-Employee-HR-Records-UAT.md](6.2-Employee-HR-Records-UAT.md) |
| 6.3 Employee Availability | [6.3-Employee-Availability-UAT.md](6.3-Employee-Availability-UAT.md) |
| 6.4 Leave Calendar View | [6.4-Leave-Calendar-View-UAT.md](6.4-Leave-Calendar-View-UAT.md) |

## Phase 7: AI Scheduling *(Premium)*

| Feature | UAT Document |
|---------|--------------|
| 7.1 AI Scheduling Service | [7.1-AI-Scheduling-Service-UAT.md](7.1-AI-Scheduling-Service-UAT.md) |
| 7.2 AI Scheduling UI | [7.2-AI-Scheduling-UI-UAT.md](7.2-AI-Scheduling-UI-UAT.md) |
| 7.3 Schedule Analysis | [7.3-Schedule-Analysis-UAT.md](7.3-Schedule-Analysis-UAT.md) |

## Phase 8: Data Import/Export

| Feature | UAT Document |
|---------|--------------|
| 8.1 Employee Import | [8.1-Employee-Import-UAT.md](8.1-Employee-Import-UAT.md) |
| 8.2 Data Export | [8.2-Data-Export-UAT.md](8.2-Data-Export-UAT.md) |

## Phase 9: Mobile Interface & Accessibility

| Feature | UAT Document |
|---------|--------------|
| 9.1 PWA Foundation | [9.1-PWA-Foundation-UAT.md](9.1-PWA-Foundation-UAT.md) |
| 9.2 Offline Storage | [9.2-Offline-Storage-UAT.md](9.2-Offline-Storage-UAT.md) |
| 9.3 Push Notifications | [9.3-Push-Notifications-UAT.md](9.3-Push-Notifications-UAT.md) |
| 9.4 Mobile API | [9.4-Mobile-API-UAT.md](9.4-Mobile-API-UAT.md) |
| 9.5 Mobile Navigation | [9.5-Mobile-Navigation-UAT.md](9.5-Mobile-Navigation-UAT.md) |
| 9.6 Touch Gestures | [9.6-Touch-Gestures-UAT.md](9.6-Touch-Gestures-UAT.md) |
| 9.7 Mobile Schedule Views | [9.7-Mobile-Schedule-Views-UAT.md](9.7-Mobile-Schedule-Views-UAT.md) |
| 9.8 Mobile Approvals | [9.8-Mobile-Approvals-UAT.md](9.8-Mobile-Approvals-UAT.md) |
| 9.9 Mobile CSS | [9.9-Mobile-CSS-UAT.md](9.9-Mobile-CSS-UAT.md) |
| 9.10 Accessibility | [9.10-Accessibility-UAT.md](9.10-Accessibility-UAT.md) |

## Phase 10: Premium Add-On Features

| Feature | UAT Document |
|---------|--------------|
| 10.1 Advanced Analytics | [10.1-Advanced-Analytics-UAT.md](10.1-Advanced-Analytics-UAT.md) |
| 10.2 Geofencing | [10.2-Geofencing-UAT.md](10.2-Geofencing-UAT.md) |
| 10.3 Labor Forecasting | [10.3-Labor-Forecasting-UAT.md](10.3-Labor-Forecasting-UAT.md) |
| 10.4 Payroll Integrations | [10.4-Payroll-Integrations-UAT.md](10.4-Payroll-Integrations-UAT.md) |
| 10.5 Team Messaging | [10.5-Team-Messaging-UAT.md](10.5-Team-Messaging-UAT.md) |
| 10.6 Document Management | [10.6-Document-Management-UAT.md](10.6-Document-Management-UAT.md) |
| 10.7 Multi-Location Analytics | [10.7-Multi-Location-Analytics-UAT.md](10.7-Multi-Location-Analytics-UAT.md) |
| 10.8 Custom Branding | [10.8-Custom-Branding-UAT.md](10.8-Custom-Branding-UAT.md) |

## Phase 11: Marketing & Public Pages

| Feature | UAT Document |
|---------|--------------|
| 11.1 Landing Page | [11.1-Landing-Page-UAT.md](11.1-Landing-Page-UAT.md) |
| 11.2 Pricing Page | [11.2-Pricing-Page-UAT.md](11.2-Pricing-Page-UAT.md) |
| 11.3 Features Page | [11.3-Features-Page-UAT.md](11.3-Features-Page-UAT.md) |
| 11.4 Legal Pages | [11.4-Legal-Pages-UAT.md](11.4-Legal-Pages-UAT.md) |

## Phase 12: Stripe Payment Integration

| Feature | UAT Document |
|---------|--------------|
| 12.1 Stripe Setup | [12.1-Stripe-Setup-UAT.md](12.1-Stripe-Setup-UAT.md) |
| 12.2 Checkout Flow | [12.2-Checkout-Flow-UAT.md](12.2-Checkout-Flow-UAT.md) |
| 12.3 Payment Methods | [12.3-Payment-Methods-UAT.md](12.3-Payment-Methods-UAT.md) |
| 12.4 Invoice History | [12.4-Invoice-History-UAT.md](12.4-Invoice-History-UAT.md) |
| 12.5 Stripe Webhooks | [12.5-Stripe-Webhooks-UAT.md](12.5-Stripe-Webhooks-UAT.md) |
| 12.6 Feature Add-ons | [12.6-Feature-Addons-UAT.md](12.6-Feature-Addons-UAT.md) |
| 12.7 Tenant Billing Details | [12.7-Tenant-Billing-Details-UAT.md](12.7-Tenant-Billing-Details-UAT.md) |
| 12.8 Ad-Hoc Invoices | [12.8-Ad-Hoc-Invoices-UAT.md](12.8-Ad-Hoc-Invoices-UAT.md) |
| 12.9 EU VAT Compliance | [12.9-EU-VAT-Compliance-UAT.md](12.9-EU-VAT-Compliance-UAT.md) |

## Phase 13: SuperAdmin Analytics Dashboard

| Feature | UAT Document |
|---------|--------------|
| 13.1 Revenue Reports | [13.1-Revenue-Reports-UAT.md](13.1-Revenue-Reports-UAT.md) |
| 13.2 Subscription Reports | [13.2-Subscription-Reports-UAT.md](13.2-Subscription-Reports-UAT.md) |
| 13.3 Growth & Churn Reports | [13.3-Growth-Churn-Reports-UAT.md](13.3-Growth-Churn-Reports-UAT.md) |
| 13.4 Tenant Health Dashboard | [13.4-Tenant-Health-UAT.md](13.4-Tenant-Health-UAT.md) |
| 13.5 Analytics Export | [13.5-Analytics-Export-UAT.md](13.5-Analytics-Export-UAT.md) |

## Phase 14: Security Features

| Feature | UAT Document |
|---------|--------------|
| 14.1 Two-Factor Authentication | [14.1-Two-Factor-Auth-UAT.md](14.1-Two-Factor-Auth-UAT.md) |
| 14.2 Session Management | [14.2-Session-Management-UAT.md](14.2-Session-Management-UAT.md) |
| 14.3 Audit Log System | [14.3-Audit-Log-UAT.md](14.3-Audit-Log-UAT.md) |

## Phase 15: GDPR Compliance

| Feature | UAT Document |
|---------|--------------|
| 15.1 Data Export | [15.1-Data-Export-UAT.md](15.1-Data-Export-UAT.md) |
| 15.2 Data Deletion | [15.2-Data-Deletion-UAT.md](15.2-Data-Deletion-UAT.md) |

## Phase 16: Scheduling Enhancements

| Feature | UAT Document |
|---------|--------------|
| 16.1 Shift Preferences | [16.1-Shift-Preferences-UAT.md](16.1-Shift-Preferences-UAT.md) |
| 16.2 Calendar Integration | [16.2-Calendar-Integration-UAT.md](16.2-Calendar-Integration-UAT.md) |
| 16.3 Open Shift Marketplace | [16.3-Open-Shift-Marketplace-UAT.md](16.3-Open-Shift-Marketplace-UAT.md) |
| 16.4 Schedule Templates | [16.4-Schedule-Templates-UAT.md](16.4-Schedule-Templates-UAT.md) |
| 16.5 Smart Fill | [16.5-Smart-Fill-UAT.md](16.5-Smart-Fill-UAT.md) |
| 16.6 Operations Dashboard | [16.6-Operations-Dashboard-UAT.md](16.6-Operations-Dashboard-UAT.md) |
| 16.7 Shift Notes | [16.7-Shift-Notes-UAT.md](16.7-Shift-Notes-UAT.md) |
| 16.8 Conflict Detection | [16.8-Conflict-Detection-UAT.md](16.8-Conflict-Detection-UAT.md) |

## Phase 17: Working Time Compliance

| Feature | UAT Document |
|---------|--------------|
| 17.1 Working Time Compliance | [17.1-Working-Time-Compliance-UAT.md](17.1-Working-Time-Compliance-UAT.md) |

## Phase 18: Analytics Enhancements

| Feature | UAT Document |
|---------|--------------|
| 18.1 Schedule Fairness | [18.1-Schedule-Fairness-UAT.md](18.1-Schedule-Fairness-UAT.md) |
| 18.2 Absence Analytics | [18.2-Absence-Analytics-UAT.md](18.2-Absence-Analytics-UAT.md) |

## Phase 19: Technical Infrastructure

| Feature | UAT Document |
|---------|--------------|
| 19.1 Webhook System | [19.1-Webhook-System-UAT.md](19.1-Webhook-System-UAT.md) |
| 19.2 API Rate Limiting | [19.2-API-Rate-Limiting-UAT.md](19.2-API-Rate-Limiting-UAT.md) |

## Phase 20: Onboarding & Import

| Feature | UAT Document |
|---------|--------------|
| 20.1 Onboarding Wizard | [20.1-Onboarding-Wizard-UAT.md](20.1-Onboarding-Wizard-UAT.md) |
| 20.2 Data Import | [20.2-Data-Import-UAT.md](20.2-Data-Import-UAT.md) |
| 20.3 Competitor Import | [20.3-Competitor-Import-UAT.md](20.3-Competitor-Import-UAT.md) |

## Phase 21: Business Model Features

| Feature | UAT Document |
|---------|--------------|
| 21.1 Freemium Tier | [21.1-Freemium-Tier-UAT.md](21.1-Freemium-Tier-UAT.md) |
| 21.2 Per-Employee Pricing | [21.2-Per-Employee-Pricing-UAT.md](21.2-Per-Employee-Pricing-UAT.md) |
| 21.3 Partner Program | [21.3-Partner-Program-UAT.md](21.3-Partner-Program-UAT.md) |
| 21.4 Professional Services | [21.4-Professional-Services-UAT.md](21.4-Professional-Services-UAT.md) |

## Phase 22: Data Retention & Notifications

| Feature | UAT Document |
|---------|--------------|
| 22.1 Data Retention | [22.1-Data-Retention-UAT.md](22.1-Data-Retention-UAT.md) |
| 22.2 Multi-Channel Notifications | [22.2-Multi-Channel-Notifications-UAT.md](22.2-Multi-Channel-Notifications-UAT.md) |
| 22.3 Internationalization | [22.3-Internationalization-UAT.md](22.3-Internationalization-UAT.md) |

## Phase 23: Offline & Self-Service

| Feature | UAT Document |
|---------|--------------|
| 23.1 Offline Support | [23.1-Offline-Support-UAT.md](23.1-Offline-Support-UAT.md) |
| 23.2 Employee Documents | [23.2-Employee-Documents-UAT.md](23.2-Employee-Documents-UAT.md) |
| 23.3 Employee Stats Dashboard | [23.3-Employee-Stats-Dashboard-UAT.md](23.3-Employee-Stats-Dashboard-UAT.md) |

## Phase 24: Delegation & Messaging

| Feature | UAT Document |
|---------|--------------|
| 24.1 Manager Delegation | [24.1-Manager-Delegation-UAT.md](24.1-Manager-Delegation-UAT.md) |
| 24.2 Team Messaging | [24.2-Team-Messaging-UAT.md](24.2-Team-Messaging-UAT.md) |

## Phase 25: Custom Reports

| Feature | UAT Document |
|---------|--------------|
| 25.1 Custom Report Builder | [25.1-Custom-Report-Builder-UAT.md](25.1-Custom-Report-Builder-UAT.md) |

## Phase 26: Billing & Search Enhancements

| Feature | UAT Document |
|---------|--------------|
| 26.1 Payment Dunning | [26.1-Payment-Dunning-UAT.md](26.1-Payment-Dunning-UAT.md) |
| 26.2 Global Search | [26.2-Global-Search-UAT.md](26.2-Global-Search-UAT.md) |

## Phase 27: Advanced Shift Features

| Feature | UAT Document |
|---------|--------------|
| 27.1 Advanced Shift Patterns | [27.1-Advanced-Shift-Patterns-UAT.md](27.1-Advanced-Shift-Patterns-UAT.md) |
| 27.2 Public Holidays | [27.2-Public-Holidays-UAT.md](27.2-Public-Holidays-UAT.md) |
| 27.3 Urgent Coverage | [27.3-Urgent-Coverage-UAT.md](27.3-Urgent-Coverage-UAT.md) |

## Phase 28: Workflows & Visibility

| Feature | UAT Document |
|---------|--------------|
| 28.1 Approval Workflows | [28.1-Approval-Workflows-UAT.md](28.1-Approval-Workflows-UAT.md) |
| 28.2 Team Availability | [28.2-Team-Availability-UAT.md](28.2-Team-Availability-UAT.md) |
| 28.3 Tenant Branding | [28.3-Tenant-Branding-UAT.md](28.3-Tenant-Branding-UAT.md) |
| 28.4 In-App Help | [28.4-In-App-Help-UAT.md](28.4-In-App-Help-UAT.md) |

## Phase 29: Employee Lifecycle & Bulk Operations

| Feature | UAT Document |
|---------|--------------|
| 29.1 Employee Invitation | [29.1-Employee-Invitation-UAT.md](29.1-Employee-Invitation-UAT.md) |
| 29.2 Employee Offboarding | [29.2-Employee-Offboarding-UAT.md](29.2-Employee-Offboarding-UAT.md) |
| 29.3 Bulk Operations | [29.3-Bulk-Operations-UAT.md](29.3-Bulk-Operations-UAT.md) |
| 29.4 Employee Insights | [29.4-Employee-Insights-UAT.md](29.4-Employee-Insights-UAT.md) |

---

## Test Environment Checklist

Before starting UAT, ensure:

- [ ] Test database is populated with sample data
- [ ] At least 2 tenants exist for isolation testing
- [ ] Admin user account available
- [ ] Employee user accounts available (same role, different roles)
- [ ] Published shifts exist for the next 7 days
- [ ] Leave types are configured
- [ ] Browser developer tools available for edge case testing

## Related Documents

- [Feature Specifications](../Implementation%20Plan/Features/) - Technical details for each feature
- [Automated Tests](/tests/Feature/) - PHPUnit test files
