# Phase 22: Data Retention & Notifications

## 22.1 Data Retention System
**Effort: Large**

Tenant-configurable data retention and archival policies.

**Database Changes:**
- [ ] Create migration for `data_retention_policies` table
- [ ] Create migration for `archived_records` table

**Files to create:**
- `app/Models/DataRetentionPolicy.php`
- `app/Models/ArchivedRecord.php`
- `app/Services/DataRetentionService.php`
- `app/Console/Commands/ProcessDataRetention.php`
- `app/Http/Controllers/DataRetentionController.php`
- `resources/views/settings/data-retention.blade.php`
- `tests/Feature/DataRetentionTest.php`

**Tasks:**
- [ ] Create DataRetentionPolicy model with tenant relationship
- [ ] Create ArchivedRecord model for cold storage
- [ ] Create DataRetentionService with archive/delete methods
- [ ] Create scheduled command for daily retention processing
- [ ] Create admin UI for configuring retention policies
- [ ] Implement GDPR data export functionality
- [ ] Write tests for all retention scenarios

---

## 22.2 Multi-Channel Notifications
**Effort: Large**

Support for Slack, Teams, and WhatsApp notifications.

**Database Changes:**
- [ ] Create migration for `notification_channels` table
- [ ] Create migration for `user_notification_channels` table
- [ ] Create migration for `workspace_integrations` table

**Files to create:**
- `app/Models/NotificationChannel.php`
- `app/Models/UserNotificationChannel.php`
- `app/Models/WorkspaceIntegration.php`
- `app/Notifications/Channels/SlackNotificationChannel.php`
- `app/Notifications/Channels/TeamsNotificationChannel.php`
- `app/Notifications/Channels/WhatsAppNotificationChannel.php`
- `app/Http/Controllers/NotificationChannelController.php`
- `app/Http/Controllers/WorkspaceIntegrationController.php`
- `resources/views/settings/notification-channels.blade.php`
- `tests/Feature/NotificationChannelsTest.php`

**Tasks:**
- [ ] Create notification channel models
- [ ] Implement Slack OAuth and bot integration
- [ ] Implement Microsoft Teams integration via Graph API
- [ ] Implement WhatsApp Business API integration
- [ ] Create user preferences UI for channel selection
- [ ] Add toSlack(), toTeams(), toWhatsApp() methods to notifications
- [ ] Create admin UI for workspace integrations
- [ ] Write tests for each channel

---

## 22.3 Internationalization
**Effort: Medium**

Support for 6 EU languages (EN, ES, FR, DE, IT, PT).

**Database Changes:**
- [ ] Create migration for `tenant_locales` table
- [ ] Add `locale` and `timezone` columns to users table

**Files to create:**
- `app/Models/TenantLocale.php`
- `app/Services/LocalizationService.php`
- `app/Http/Middleware/SetLocale.php`
- `config/locales.php`
- `lang/es/*.php` (Spanish translations)
- `lang/fr/*.php` (French translations)
- `lang/de/*.php` (German translations)
- `lang/it/*.php` (Italian translations)
- `lang/pt/*.php` (Portuguese translations)
- `tests/Feature/LocalizationTest.php`

**Tasks:**
- [ ] Create TenantLocale model
- [ ] Create LocalizationService for date/time/currency formatting
- [ ] Create SetLocale middleware
- [ ] Extract all UI strings to translation files
- [ ] Create translations for all 6 languages
- [ ] Add language selector to user preferences
- [ ] Add default locale to tenant settings
- [ ] Write tests for locale switching and formatting
