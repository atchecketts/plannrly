# Phase 28: Workflows & Visibility

## 28.1 Configurable Approval Workflows
**Effort: Large**

Tenant-defined approval chains for various actions.

**Database Changes:**
- [ ] Create migration for `approval_workflows` table
- [ ] Create migration for `approval_requests` table
- [ ] Create migration for `approval_actions` table

**Files to create:**
- `app/Models/ApprovalWorkflow.php`
- `app/Models/ApprovalRequest.php`
- `app/Models/ApprovalAction.php`
- `app/Services/ApprovalWorkflowService.php`
- `app/Http/Controllers/ApprovalWorkflowController.php`
- `resources/views/settings/approval-workflows.blade.php`
- `tests/Feature/ApprovalWorkflowTest.php`

**Tasks:**
- [ ] Create workflow configuration models
- [ ] Build workflow designer UI
- [ ] Implement multi-step approval chains
- [ ] Add condition-based workflow matching
- [ ] Integrate with leave, swaps, overtime, expenses
- [ ] Create approval dashboard
- [ ] Add escalation on timeout
- [ ] Write tests

---

## 28.2 Team Availability Dashboard
**Effort: Medium**

Comprehensive view for planning and gap analysis.

**Files to create:**
- `app/Services/TeamAvailabilityService.php`
- `app/Http/Controllers/TeamAvailabilityController.php`
- `resources/views/team/availability.blade.php`
- `tests/Feature/TeamAvailabilityTest.php`

**Tasks:**
- [ ] Create availability aggregation service
- [ ] Build calendar view of team availability
- [ ] Show leave, shifts, preferences in unified view
- [ ] Implement skill/certification visibility
- [ ] Create gap analysis for understaffed periods
- [ ] Add quick-assign from availability view
- [ ] Export availability for planning
- [ ] Write tests

---

## 28.3 Tenant Branding
**Effort: Medium**

Tiered branding customization (logo, colors, white-label).

**Database Changes:**
- [ ] Create migration for `tenant_branding` table

**Files to create:**
- `app/Models/TenantBranding.php`
- `app/Services/BrandingService.php`
- `app/Http/Controllers/BrandingController.php`
- `resources/views/settings/branding.blade.php`
- `tests/Feature/BrandingTest.php`

**Tasks:**
- [ ] Create branding model with tier restrictions
- [ ] Implement logo upload (all tiers)
- [ ] Add color customization (Professional+)
- [ ] Implement custom domain support (Enterprise)
- [ ] Add white-label option (Enterprise)
- [ ] Create CSS variable injection for colors
- [ ] Write tests for tier restrictions

---

## 28.4 In-App Help & AI Chat
**Effort: Large**

Contextual help and AI assistant for admins.

**Files to create:**
- `app/Services/HelpService.php`
- `app/Services/AIChatService.php`
- `app/Http/Controllers/HelpController.php`
- `app/Http/Controllers/AIChatController.php`
- `resources/views/components/help-tooltip.blade.php`
- `resources/views/components/ai-chat.blade.php`
- `tests/Feature/AIChatTest.php`

**Tasks:**
- [ ] Create contextual help tooltip system
- [ ] Build searchable knowledge base
- [ ] Integrate AI chat (OpenAI/Claude API)
- [ ] Restrict AI chat to business admins
- [ ] Tier-gate AI chat (Professional+)
- [ ] Create help content for all features
- [ ] Write tests
