# Phase 24: Delegation & Messaging

## 24.1 Manager Delegation System
**Effort: Large**

Configurable delegation of management responsibilities.

**Database Changes:**
- [ ] Create migration for `manager_delegations` table
- [ ] Create migration for `delegation_audit_log` table

**Files to create:**
- `app/Models/ManagerDelegation.php`
- `app/Models/DelegationAuditLog.php`
- `app/Services/DelegationService.php`
- `app/Http/Controllers/DelegationController.php`
- `app/Policies/DelegationPolicy.php`
- `resources/views/delegations/index.blade.php`
- `resources/views/delegations/create.blade.php`
- `tests/Feature/DelegationTest.php`

**Tasks:**
- [ ] Create ManagerDelegation model with scope support
- [ ] Create DelegationService for permission checking
- [ ] Integrate delegation checks into authorization
- [ ] Create delegation management UI
- [ ] Add delegation approval workflow (if tenant requires)
- [ ] Create audit logging for delegated actions
- [ ] Add delegation settings to tenant configuration
- [ ] Write tests for all delegation scenarios

---

## 24.2 Team Messaging System
**Effort: Large**

Built-in messaging with direct, group, and announcements.

**Database Changes:**
- [ ] Create migration for `conversations` table
- [ ] Create migration for `conversation_participants` table
- [ ] Create migration for `messages` table
- [ ] Create migration for `message_reads` table
- [ ] Create migration for `announcements` table
- [ ] Create migration for `announcement_acknowledgements` table

**Files to create:**
- `app/Models/Conversation.php`
- `app/Models/ConversationParticipant.php`
- `app/Models/Message.php`
- `app/Models/MessageRead.php`
- `app/Models/Announcement.php`
- `app/Models/AnnouncementAcknowledgement.php`
- `app/Services/MessagingService.php`
- `app/Http/Controllers/ConversationController.php`
- `app/Http/Controllers/MessageController.php`
- `app/Http/Controllers/AnnouncementController.php`
- `app/Events/MessageSent.php`
- `resources/views/messages/index.blade.php`
- `resources/views/messages/conversation.blade.php`
- `resources/views/announcements/index.blade.php`
- `resources/views/announcements/create.blade.php`
- `tests/Feature/MessagingTest.php`
- `tests/Feature/AnnouncementTest.php`

**Tasks:**
- [ ] Create conversation and message models
- [ ] Create MessagingService for all messaging operations
- [ ] Implement real-time messaging with Laravel Echo
- [ ] Create direct message UI
- [ ] Create group conversation UI
- [ ] Create announcement posting and viewing
- [ ] Implement acknowledgement tracking
- [ ] Add unread counts to navigation
- [ ] Write tests for all messaging features
