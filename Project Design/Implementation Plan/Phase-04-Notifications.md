# Phase 4: Notifications System (Medium Priority)

## 4.1 Additional Notifications
**Effort: Medium**

**Files to create:**
- `app/Notifications/ShiftChangedNotification.php`
- `app/Notifications/LeaveRequestStatusNotification.php`
- `app/Notifications/SwapRequestNotification.php`
- `app/Notifications/SwapRequestResponseNotification.php`
- `app/Notifications/ShiftReminderNotification.php`

**Tasks:**
- [ ] Create ShiftChangedNotification (when shift is edited/deleted)
- [ ] Create LeaveRequestStatusNotification (approved/rejected)
- [ ] Create SwapRequestNotification (when swap is requested)
- [ ] Create SwapRequestResponseNotification (accepted/rejected)
- [ ] Create ShiftReminderNotification (upcoming shift reminder)
- [ ] Register notifications in appropriate events
- [ ] Write tests

---

## 4.2 Notification Preferences UI
**Effort: Small**

**Files to create:**
- `app/Http/Controllers/NotificationPreferenceController.php`
- `resources/views/profile/notifications.blade.php`

**Tasks:**
- [ ] Create NotificationPreferenceController
- [ ] Create preferences UI
- [ ] Allow toggling email/push/in-app per notification type
- [ ] Respect preferences when sending notifications
- [ ] Write tests

---

## 4.3 In-App Notifications UI
**Effort: Medium**

**Files to modify:**
- `resources/views/components/layouts/app.blade.php`

**Files to create:**
- `app/Http/Controllers/NotificationController.php`
- `resources/views/notifications/index.blade.php`

**Tasks:**
- [ ] Add notification bell to header with unread count
- [ ] Create dropdown showing recent notifications
- [ ] Create full notifications index page
- [ ] Implement mark as read functionality
- [ ] Add real-time updates (optional, using Pusher/Websockets)
- [ ] Write tests
