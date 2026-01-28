# Phase 14: Security Features

## 14.1 Two-Factor Authentication (2FA)
**Effort: Large**

Optional 2FA for enhanced account security.

**Files to create:**
- `app/Services/TwoFactorAuthService.php`
- `app/Http/Controllers/TwoFactorController.php`
- `app/Models/TwoFactorAuthentication.php`
- `app/Models/TrustedDevice.php`
- `resources/views/settings/two-factor.blade.php`
- `resources/views/auth/two-factor-challenge.blade.php`

**Tasks:**
- [ ] Create database migrations for 2FA tables
- [ ] Install TOTP library (e.g., pragmarx/google2fa-laravel)
- [ ] Create TwoFactorAuthService with enable/verify/disable methods
- [ ] Generate QR codes for authenticator apps
- [ ] Generate and encrypt recovery codes
- [ ] Create 2FA setup wizard UI
- [ ] Create 2FA challenge screen during login
- [ ] Implement "Trust this device" functionality
- [ ] Add tenant setting for mandatory 2FA per role
- [ ] Add recovery code verification flow
- [ ] Write comprehensive tests

---

## 14.2 Session Management
**Effort: Medium**

Let users view and manage active sessions.

**Files to create:**
- `app/Models/UserSession.php`
- `app/Http/Controllers/SessionController.php`
- `resources/views/settings/sessions.blade.php`

**Tasks:**
- [ ] Create user_sessions migration
- [ ] Track sessions on login (device, IP, location)
- [ ] Create session list view with device details
- [ ] Implement remote logout functionality
- [ ] Add "Log out all other sessions" button
- [ ] Send email notification on new device login
- [ ] Add session timeout configuration per tenant
- [ ] Highlight current session in list
- [ ] Write tests

---

## 14.3 Audit Log System
**Effort: Large**

Complete audit trail for compliance and security.

**Files to create:**
- `app/Models/AuditLog.php`
- `app/Services/AuditLogService.php`
- `app/Traits/Auditable.php`
- `app/Http/Controllers/AuditLogController.php`
- `resources/views/audit-logs/index.blade.php`

**Tasks:**
- [ ] Create audit_logs migration
- [ ] Create AuditLogService for logging actions
- [ ] Create Auditable trait for models
- [ ] Apply trait to key models (Shift, User, LeaveRequest, etc.)
- [ ] Create audit log viewer with filtering
- [ ] Implement search by user, action, entity, date
- [ ] Add CSV export for compliance
- [ ] Configure retention period per tenant
- [ ] Ensure logs are immutable
- [ ] Write tests
