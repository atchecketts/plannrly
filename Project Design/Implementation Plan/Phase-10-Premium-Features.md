# Phase 10: Premium Add-On Features

> **Note:** All features in this phase require Phase 1.6 (Subscription & Feature Management) to be completed first. Each feature is gated by its respective feature flag.

## 10.1 Advanced Analytics & Reports *(Premium)*
**Effort: Large**

Custom report builder and advanced analytics dashboards.

**Files to create:**
- `app/Http/Controllers/AdvancedReportController.php`
- `app/Services/ReportBuilderService.php`
- `resources/views/reports/builder.blade.php`
- `resources/views/reports/custom.blade.php`
- `tests/Feature/AdvancedReportsTest.php`

**Features:**
- Drag-and-drop report builder
- Save and share custom reports
- Labor cost trend analysis
- Schedule efficiency metrics
- Scheduled email delivery of reports
- Export to PDF, CSV, Excel

**Tasks:**
- [ ] Create ReportBuilderService
- [ ] Create AdvancedReportController
- [ ] Build drag-and-drop report builder UI
- [ ] Implement labor cost analytics
- [ ] Add scheduled report delivery
- [ ] Create export functionality (PDF, CSV, Excel)
- [ ] Add feature gate middleware to routes
- [ ] Write tests

---

## 10.2 Advanced Geofencing *(Premium)*
**Effort: Large**

Location-based clock-in verification with configurable geofences.

**Database Changes:**
- [ ] Create migration for `location_geofences` table
- [ ] Create migration for `geofence_events` table

**Files to create:**
- `app/Models/LocationGeofence.php`
- `app/Models/GeofenceEvent.php`
- `app/Services/GeofencingService.php`
- `app/Http/Controllers/GeofenceController.php`
- `resources/views/locations/geofence.blade.php`
- `resources/views/components/geofence-map.blade.php`
- `tests/Feature/GeofencingTest.php`

**Features:**
- Configure geofence per location (lat/long, radius)
- Enforce clock-in within geofence
- Auto clock-in/out on geofence entry/exit (optional)
- GPS trail for mobile workers
- Violation alerts for managers
- Geofence event history

**Tasks:**
- [ ] Create LocationGeofence model
- [ ] Create GeofenceEvent model
- [ ] Create GeofencingService with distance calculation
- [ ] Create GeofenceController
- [ ] Build geofence configuration UI with map
- [ ] Integrate with clock-in/out flow
- [ ] Implement auto clock-in/out on geofence events
- [ ] Create violation notification
- [ ] Add geofence events view for managers
- [ ] Add feature gate middleware
- [ ] Write tests

---

## 10.3 Labor Demand Forecasting *(Premium)*
**Effort: Extra Large**

AI-powered staffing predictions based on historical data.

**Database Changes:**
- [ ] Create migration for `labor_forecasts` table

**Files to create:**
- `app/Models/LaborForecast.php`
- `app/Services/LaborForecastingService.php`
- `app/Jobs/GenerateLaborForecasts.php`
- `app/Http/Controllers/ForecastController.php`
- `resources/views/forecasting/index.blade.php`
- `resources/views/components/forecast-chart.blade.php`
- `tests/Feature/LaborForecastingTest.php`

**Features:**
- Analyze historical scheduling patterns
- Generate daily/hourly staffing predictions
- Factor in day of week, seasonality
- Confidence scores for predictions
- Integration with AI Scheduling
- Visual forecast charts

**Tasks:**
- [ ] Create LaborForecast model
- [ ] Create LaborForecastingService with ML algorithm
- [ ] Implement historical pattern analysis
- [ ] Create seasonal adjustment factors
- [ ] Create GenerateLaborForecasts job (scheduled weekly)
- [ ] Create ForecastController
- [ ] Build forecast visualization UI
- [ ] Integrate forecasts with AI Scheduling
- [ ] Add feature gate middleware
- [ ] Write tests

---

## 10.4 Payroll Integrations *(Premium)*
**Effort: Extra Large**

Direct export to major payroll providers.

**Database Changes:**
- [ ] Create migration for `payroll_integrations` table
- [ ] Create migration for `payroll_exports` table

**Files to create:**
- `app/Models/PayrollIntegration.php`
- `app/Models/PayrollExport.php`
- `app/Enums/PayrollProvider.php`
- `app/Services/PayrollIntegrationService.php`
- `app/Services/Payroll/AdpExporter.php`
- `app/Services/Payroll/PaychexExporter.php`
- `app/Services/Payroll/GustoExporter.php`
- `app/Services/Payroll/QuickBooksExporter.php`
- `app/Services/Payroll/XeroExporter.php`
- `app/Http/Controllers/PayrollController.php`
- `resources/views/payroll/index.blade.php`
- `resources/views/payroll/setup.blade.php`
- `resources/views/payroll/export.blade.php`
- `tests/Feature/PayrollIntegrationTest.php`

**Supported Providers:**
| Provider | Export Format |
|----------|---------------|
| ADP | API / CSV |
| Paychex | API / CSV |
| Gusto | API |
| QuickBooks | API |
| Xero | API |

**Tasks:**
- [ ] Create PayrollIntegration model
- [ ] Create PayrollExport model
- [ ] Create PayrollProvider enum
- [ ] Create PayrollIntegrationService
- [ ] Implement provider-specific exporters
- [ ] Create PayrollController
- [ ] Build integration setup UI
- [ ] Build export wizard UI
- [ ] Implement OAuth flows for API providers
- [ ] Add export history view
- [ ] Add feature gate middleware
- [ ] Write tests

---

## 10.5 Team Messaging & Announcements *(Premium)*
**Effort: Large**

Internal communication tools for staff.

**Database Changes:**
- [ ] Create migration for `announcements` table
- [ ] Create migration for `announcement_reads` table
- [ ] Create migration for `direct_messages` table

**Files to create:**
- `app/Models/Announcement.php`
- `app/Models/AnnouncementRead.php`
- `app/Models/DirectMessage.php`
- `app/Services/MessagingService.php`
- `app/Http/Controllers/AnnouncementController.php`
- `app/Http/Controllers/DirectMessageController.php`
- `app/Notifications/NewAnnouncementNotification.php`
- `resources/views/messaging/announcements/index.blade.php`
- `resources/views/messaging/announcements/create.blade.php`
- `resources/views/messaging/messages/index.blade.php`
- `resources/views/components/announcement-card.blade.php`
- `resources/views/components/message-thread.blade.php`
- `tests/Feature/MessagingTest.php`

**Features:**
- Create announcements (org/location/department scope)
- Require acknowledgment for important announcements
- Track read receipts
- Direct messaging between users
- Unread counts in navigation
- Message search

**Tasks:**
- [ ] Create Announcement model with scopes
- [ ] Create AnnouncementRead model
- [ ] Create DirectMessage model
- [ ] Create MessagingService
- [ ] Create AnnouncementController
- [ ] Create DirectMessageController
- [ ] Build announcements list and create UI
- [ ] Build direct messages UI
- [ ] Implement read tracking
- [ ] Implement acknowledgment flow
- [ ] Add unread badge to navigation
- [ ] Create email notification for new announcements
- [ ] Add feature gate middleware
- [ ] Write tests

---

## 10.6 Document & Certification Management *(Premium)*
**Effort: Large**

Manage employee documents and track certifications.

**Database Changes:**
- [ ] Create migration for `employee_documents` table
- [ ] Create migration for `certifications` table
- [ ] Create migration for `user_certifications` table
- [ ] Create migration for `role_required_certifications` table

**Files to create:**
- `app/Models/EmployeeDocument.php`
- `app/Models/Certification.php`
- `app/Models/UserCertification.php`
- `app/Models/RoleRequiredCertification.php`
- `app/Enums/DocumentType.php`
- `app/Services/DocumentManagementService.php`
- `app/Http/Controllers/DocumentController.php`
- `app/Http/Controllers/CertificationController.php`
- `app/Jobs/SendCertificationExpiryReminders.php`
- `app/Notifications/CertificationExpiringNotification.php`
- `resources/views/documents/index.blade.php`
- `resources/views/documents/upload.blade.php`
- `resources/views/certifications/index.blade.php`
- `resources/views/users/certifications.blade.php`
- `tests/Feature/DocumentManagementTest.php`

**Features:**
- Upload and categorize employee documents
- Define certification types with validity periods
- Track employee certifications with expiry dates
- Define required certifications per role
- Automatic expiry reminder emails
- Certification compliance dashboard

**Tasks:**
- [ ] Create all document/certification models
- [ ] Create DocumentType enum
- [ ] Create DocumentManagementService
- [ ] Create DocumentController with upload handling
- [ ] Create CertificationController
- [ ] Build document upload and list UI
- [ ] Build certification management UI
- [ ] Build user certification tracking UI
- [ ] Implement role-required certifications
- [ ] Create expiry reminder job (daily)
- [ ] Create expiring certifications dashboard
- [ ] Add compliance check to scheduling (warn if missing certs)
- [ ] Add feature gate middleware
- [ ] Write tests

---

## 10.7 Multi-Location Analytics *(Enterprise)*
**Effort: Large**

Cross-location comparison and consolidated reporting.

**Files to create:**
- `app/Services/MultiLocationAnalyticsService.php`
- `app/Http/Controllers/MultiLocationAnalyticsController.php`
- `resources/views/analytics/multi-location/index.blade.php`
- `resources/views/analytics/multi-location/comparison.blade.php`
- `resources/views/components/location-comparison-chart.blade.php`
- `tests/Feature/MultiLocationAnalyticsTest.php`

**Features:**
- Location performance comparison dashboard
- Consolidated labor cost reporting
- Benchmark locations against each other
- Identify top/bottom performers
- Executive summary view

**Tasks:**
- [ ] Create MultiLocationAnalyticsService
- [ ] Create MultiLocationAnalyticsController
- [ ] Build location comparison dashboard
- [ ] Implement labor cost aggregation
- [ ] Create performance ranking
- [ ] Build executive summary view
- [ ] Add feature gate middleware (enterprise)
- [ ] Write tests

---

## 10.8 Custom Branding / White Label *(Enterprise)*
**Effort: Medium**

Customize appearance with company branding.

**Database Changes:**
- [ ] Create migration for `tenant_branding` table

**Files to create:**
- `app/Models/TenantBranding.php`
- `app/Services/BrandingService.php`
- `app/Http/Controllers/BrandingController.php`
- `app/Http/Middleware/ApplyBranding.php`
- `resources/views/branding/index.blade.php`
- `tests/Feature/BrandingTest.php`

**Features:**
- Custom primary/secondary colors
- Custom logo upload
- Custom favicon
- Custom login page background
- Hide "Powered by Plannrly" branding
- Custom CSS overrides (advanced)

**Tasks:**
- [ ] Create TenantBranding model
- [ ] Create BrandingService
- [ ] Create BrandingController
- [ ] Create ApplyBranding middleware
- [ ] Build branding customization UI
- [ ] Implement logo/favicon upload
- [ ] Apply branding to all views dynamically
- [ ] Add feature gate middleware (enterprise)
- [ ] Write tests
