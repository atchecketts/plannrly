# Phase 25: Custom Reports

## 25.1 Custom Report Builder
**Effort: Large**

Drag-and-drop report creation for managers.

**Database Changes:**
- [ ] Create migration for `custom_reports` table
- [ ] Create migration for `report_executions` table

**Files to create:**
- `app/Models/CustomReport.php`
- `app/Models/ReportExecution.php`
- `app/Services/ReportBuilderService.php`
- `app/Http/Controllers/CustomReportController.php`
- `app/Http/Controllers/ReportExecutionController.php`
- `app/Console/Commands/SendScheduledReports.php`
- `resources/views/reports/builder/index.blade.php`
- `resources/views/reports/builder/create.blade.php`
- `resources/views/reports/builder/view.blade.php`
- `resources/js/components/ReportBuilder.vue` (or Alpine component)
- `tests/Feature/CustomReportTest.php`

**Tasks:**
- [ ] Create CustomReport model with JSON config storage
- [ ] Create ReportBuilderService for query building
- [ ] Build drag-and-drop column selector UI
- [ ] Implement filter builder with conditions
- [ ] Add grouping and aggregation options
- [ ] Create chart visualization (table, bar, line, pie)
- [ ] Implement PDF export using DomPDF
- [ ] Implement Excel export using Laravel Excel
- [ ] Add scheduled report delivery via email
- [ ] Write tests for report building and execution
