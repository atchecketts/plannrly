# Phase 15: GDPR Compliance

## 15.1 Data Export (Right to Access)
**Effort: Medium**

Allow users to export their personal data.

**Files to create:**
- `app/Services/GdprDataExportService.php`
- `app/Http/Controllers/DataExportController.php`
- `app/Jobs/GenerateDataExportJob.php`
- `resources/views/settings/data-export.blade.php`

**Tasks:**
- [ ] Create GdprDataExportService
- [ ] Collect all user data (profile, shifts, time entries, etc.)
- [ ] Generate JSON export file
- [ ] Generate CSV export option
- [ ] Create background job for large exports
- [ ] Email download link when ready
- [ ] Add export request UI for employees
- [ ] Log all export requests in audit trail
- [ ] Write tests

---

## 15.2 Data Deletion (Right to Erasure)
**Effort: Large**

Process data deletion requests with approval workflow.

**Files to create:**
- `app/Models/DataDeletionRequest.php`
- `app/Services/GdprDeletionService.php`
- `app/Http/Controllers/DataDeletionController.php`
- `resources/views/settings/data-deletion.blade.php`
- `resources/views/admin/deletion-requests/index.blade.php`

**Tasks:**
- [ ] Create data_deletion_requests migration
- [ ] Create deletion request submission UI
- [ ] Create admin approval workflow
- [ ] Implement user anonymization (preserve historical data)
- [ ] Delete personal documents and files
- [ ] Handle legal retention requirements
- [ ] Send confirmation emails
- [ ] Log all deletion actions
- [ ] Write tests
