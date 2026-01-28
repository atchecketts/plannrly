# Phase 26: Billing & Search Enhancements

## 26.1 Payment Dunning System
**Effort: Medium**

Automatic payment retry and grace period handling.

**Database Changes:**
- [ ] Create migration for `payment_attempts` table
- [ ] Create migration for `dunning_states` table

**Files to create:**
- `app/Models/PaymentAttempt.php`
- `app/Models/DunningState.php`
- `app/Services/DunningService.php`
- `app/Console/Commands/ProcessDunning.php`
- `app/Notifications/PaymentFailedNotification.php`
- `app/Notifications/GracePeriodWarningNotification.php`
- `tests/Feature/DunningTest.php`

**Tasks:**
- [ ] Create dunning state tracking models
- [ ] Implement retry schedule (days 1, 3, 7)
- [ ] Create 14-day grace period logic
- [ ] Implement tenant suspension on grace period end
- [ ] Create payment failure notification emails
- [ ] Create payment method update flow
- [ ] Write tests for all dunning scenarios

---

## 26.2 Global Search
**Effort: Large**

Full-text search across all content types.

**Database Changes:**
- [ ] Create migration for `search_index` table with FULLTEXT
- [ ] Create migration for `recent_searches` table

**Files to create:**
- `app/Models/SearchIndex.php`
- `app/Models/RecentSearch.php`
- `app/Services/GlobalSearchService.php`
- `app/Traits/Searchable.php`
- `app/Http/Controllers/SearchController.php`
- `app/Console/Commands/ReindexSearch.php`
- `resources/views/components/global-search.blade.php`
- `tests/Feature/GlobalSearchTest.php`

**Tasks:**
- [ ] Create search index model and service
- [ ] Implement Searchable trait for models
- [ ] Add trait to User, Shift, Message, Document models
- [ ] Create search API endpoint
- [ ] Build global search UI component (Cmd+K)
- [ ] Implement recent searches
- [ ] Create reindex command
- [ ] Write tests for search functionality
