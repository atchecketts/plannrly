# Phase 23: Offline & Self-Service

## 23.1 Enhanced Offline Support
**Effort: Large**

Full offline PWA functionality with sync queue.

**Database Changes:**
- [ ] Create migration for `offline_sync_queue` table

**Files to create:**
- `app/Models/OfflineSyncQueue.php`
- `app/Services/OfflineSyncService.php`
- `app/Http/Controllers/Api/OfflineSyncController.php`
- `resources/js/service-worker.js`
- `resources/js/offline-manager.js`
- `resources/views/offline.blade.php`
- `tests/Feature/OfflineSyncTest.php`

**Tasks:**
- [ ] Create OfflineSyncService for processing queued actions
- [ ] Create service worker with caching strategies
- [ ] Implement IndexedDB storage for offline data
- [ ] Create sync conflict resolution logic
- [ ] Add offline indicators to UI
- [ ] Support offline clock in/out with timestamp preservation
- [ ] Create offline fallback page
- [ ] Write tests for sync scenarios

---

## 23.2 Employee Document Management
**Effort: Medium**

Self-service document uploads with verification workflow.

**Database Changes:**
- [ ] Create migration for `employee_documents` table
- [ ] Create migration for `document_types` table

**Files to create:**
- `app/Models/EmployeeDocument.php`
- `app/Models/DocumentType.php`
- `app/Services/EmployeeSelfServiceService.php`
- `app/Http/Controllers/EmployeeDocumentController.php`
- `app/Http/Controllers/DocumentTypeController.php`
- `app/Notifications/DocumentExpiringNotification.php`
- `resources/views/employee/documents/index.blade.php`
- `resources/views/employee/documents/upload.blade.php`
- `resources/views/admin/document-types/index.blade.php`
- `tests/Feature/EmployeeDocumentTest.php`

**Tasks:**
- [ ] Create EmployeeDocument model with file storage
- [ ] Create DocumentType model for tenant-configurable types
- [ ] Create upload UI for employees
- [ ] Create verification workflow for managers
- [ ] Implement expiry tracking and notifications
- [ ] Add document type configuration to admin
- [ ] Write tests for uploads, verification, and expiry

---

## 23.3 Employee Stats Dashboard
**Effort: Small**

Tenant-configurable employee statistics visibility.

**Tasks:**
- [ ] Add employee stats settings to tenant_settings
- [ ] Create employee personal dashboard component
- [ ] Display hours worked, leave balance, upcoming shifts
- [ ] Conditionally show attendance/punctuality based on settings
- [ ] Write tests for visibility permissions
