# Phase 20: Onboarding & Import

## 20.1 Onboarding Wizard
**Effort: Large**

Guided setup for new tenants.

**Files to create:**
- `app/Models/OnboardingProgress.php`
- `app/Services/OnboardingService.php`
- `app/Http/Controllers/OnboardingController.php`
- `resources/views/onboarding/wizard.blade.php`
- `resources/views/onboarding/steps/*.blade.php`

**Tasks:**
- [ ] Create onboarding_progress migration
- [ ] Create OnboardingService
- [ ] Design step-by-step wizard UI
- [ ] Step 1: Organization details
- [ ] Step 2: First location
- [ ] Step 3: Departments (skippable)
- [ ] Step 4: Business roles
- [ ] Step 5: Invite team
- [ ] Step 6: First schedule
- [ ] Add progress indicator
- [ ] Create sample data loader
- [ ] Add "Clear sample data" option
- [ ] Create persistent checklist after wizard
- [ ] Add contextual help and videos
- [ ] Write tests

---

## 20.2 Data Import
**Effort: Medium**

Import employees and data from files.

**Files to create:**
- `app/Services/DataImportService.php`
- `app/Http/Controllers/ImportController.php`
- `resources/views/import/index.blade.php`
- `resources/views/import/mapping.blade.php`
- `resources/views/import/preview.blade.php`

**Tasks:**
- [ ] Create DataImportService
- [ ] Support CSV and Excel file upload
- [ ] Create field mapping interface
- [ ] Validate data before import
- [ ] Show preview with validation errors
- [ ] Import employees with mapped fields
- [ ] Report errors with line numbers
- [ ] Support partial import (skip errors)
- [ ] Write tests

---

## 20.3 Competitor Import
**Effort: Large**

Import data from competitor systems.

**Files to create:**
- `app/Services/Import/DeputyImporter.php`
- `app/Services/Import/WhenIWorkImporter.php`
- `app/Services/Import/SevenShiftsImporter.php`
- `resources/views/import/competitor.blade.php`

**Tasks:**
- [ ] Create CompetitorImporter interface
- [ ] Implement Deputy data import
- [ ] Implement When I Work data import
- [ ] Implement 7shifts data import
- [ ] Auto-detect file format
- [ ] Map competitor fields to Plannrly fields
- [ ] Handle data transformation
- [ ] Create import wizard per competitor
- [ ] Write tests
