# Phase 19: Technical Infrastructure

## 19.1 Webhook System
**Effort: Large**

Real-time event notifications for integrations.

**Files to create:**
- `app/Models/Webhook.php`
- `app/Models/WebhookDelivery.php`
- `app/Services/WebhookService.php`
- `app/Jobs/WebhookDeliveryJob.php`
- `app/Http/Controllers/WebhookController.php`
- `resources/views/settings/webhooks/index.blade.php`
- `resources/views/settings/webhooks/create.blade.php`

**Supported Events:**
- shift.created, shift.updated, shift.deleted
- schedule.published
- employee.clocked_in, employee.clocked_out
- leave.requested, leave.approved, leave.rejected
- swap.requested, swap.approved, swap.rejected

**Tasks:**
- [ ] Create webhook migrations
- [ ] Create WebhookService for dispatching events
- [ ] Implement HMAC signature verification
- [ ] Create webhook delivery job with retries
- [ ] Implement exponential backoff for failures
- [ ] Create webhook management UI
- [ ] Add "Test webhook" button
- [ ] Create delivery log viewer
- [ ] Disable webhook after repeated failures
- [ ] Write tests

---

## 19.2 API Rate Limiting
**Effort: Small**

Fair usage enforcement with visibility.

**Tasks:**
- [ ] Configure rate limits per subscription tier
- [ ] Add rate limit headers to API responses
- [ ] Create API usage dashboard in settings
- [ ] Add email alerts for approaching limits
- [ ] Allow burst requests for occasional spikes
- [ ] Document rate limits in API docs
- [ ] Write tests
