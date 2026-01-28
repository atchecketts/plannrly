# Phase 9: Mobile Interface & Accessibility

This phase transforms Plannrly into a seamless mobile experience with Progressive Web App capabilities, offline support, and push notifications.

## 9.1 Progressive Web App (PWA) Foundation
**Effort: Large**

Install-to-home-screen capability with native app-like experience.

**Files to create:**
- `public/manifest.json`
- `public/sw.js` (service worker)
- `public/offline.blade.php`
- `public/icons/` (all PWA icon sizes)
- `resources/js/pwa-register.js`

**Tasks:**
- [ ] Create web app manifest with app metadata
- [ ] Generate PWA icons (72, 96, 128, 144, 152, 192, 384, 512px)
- [ ] Create service worker with caching strategies
- [ ] Implement offline fallback page
- [ ] Add manifest link to app layout
- [ ] Register service worker on page load
- [ ] Add Apple-specific meta tags for iOS support
- [ ] Create app shortcuts (Clock In, Schedule, Leave Request)
- [ ] Test installation on Android and iOS
- [ ] Write tests for PWA functionality

---

## 9.2 Offline Data Storage
**Effort: Large**

Enable viewing schedules and queuing actions when offline.

**Files to create:**
- `resources/js/offline-storage.js`
- `app/Http/Controllers/Api/SyncController.php`
- `database/migrations/xxxx_create_offline_sync_queue_table.php`

**Tasks:**
- [ ] Implement IndexedDB wrapper for client-side storage
- [ ] Create shifts store for offline schedule viewing
- [ ] Create pending actions queue for clock events
- [ ] Build sync service to reconcile offline actions
- [ ] Add online/offline status detection
- [ ] Display sync status indicator in UI
- [ ] Implement conflict resolution for simultaneous edits
- [ ] Cache last 14 days of shifts for offline access
- [ ] Store user profile and preferences offline
- [ ] Write tests for offline scenarios

---

## 9.3 Push Notifications
**Effort: Large**

Real-time notifications for schedule changes, reminders, and approvals.

**Database Changes:**
- [ ] Create migration for `push_subscriptions` table
- [ ] Create migration for `notification_preferences` table

**Files to create:**
- `app/Models/PushSubscription.php`
- `app/Models/NotificationPreference.php`
- `app/Services/PushNotificationService.php`
- `app/Http/Controllers/Api/PushSubscriptionController.php`
- `app/Jobs/SendPushNotification.php`
- `resources/views/settings/notifications.blade.php`

**Tasks:**
- [ ] Generate VAPID keys for web push
- [ ] Create PushSubscription model and migration
- [ ] Implement subscription endpoint (subscribe/unsubscribe)
- [ ] Create PushNotificationService with WebPush library
- [ ] Integrate notifications with shift published event
- [ ] Add shift reminder notifications (configurable timing)
- [ ] Add leave request status notifications
- [ ] Add swap request notifications
- [ ] Create notification preferences UI
- [ ] Implement quiet hours functionality
- [ ] Handle notification clicks in service worker
- [ ] Write tests for push notifications

---

## 9.4 Mobile API Endpoints
**Effort: Medium**

Optimized API endpoints for mobile data consumption.

**Files to create:**
- `app/Http/Controllers/Api/MobileScheduleController.php`
- `app/Http/Controllers/Api/MobileClockController.php`
- `app/Http/Controllers/Api/MobileNotificationController.php`
- `app/Http/Resources/MobileShiftResource.php`

**Tasks:**
- [ ] Create MobileScheduleController with today/week endpoints
- [ ] Create MobileClockController with offline sync support
- [ ] Create lightweight MobileShiftResource
- [ ] Add initial sync endpoint for offline data
- [ ] Implement batch sync endpoint for offline actions
- [ ] Add unread notifications count endpoint
- [ ] Optimize response payloads for mobile
- [ ] Add response caching headers
- [ ] Write API tests

---

## 9.5 Mobile Bottom Navigation
**Effort: Medium**

Touch-optimized navigation for mobile users.

**Files to create:**
- `resources/views/components/mobile-nav.blade.php`
- `resources/views/components/mobile-clock-widget.blade.php`
- `resources/views/components/mobile-shift-card.blade.php`

**Tasks:**
- [ ] Create bottom navigation component (hidden on desktop)
- [ ] Implement clock button as prominent center action
- [ ] Add navigation items: Home, Schedule, Clock, Requests, Profile
- [ ] Create mobile clock widget with large tap targets
- [ ] Build mobile shift card for list views
- [ ] Handle safe area insets for notched devices
- [ ] Add haptic feedback for clock actions (where supported)
- [ ] Test touch targets meet 44x44px minimum
- [ ] Write component tests

---

## 9.6 Pull-to-Refresh & Swipe Actions
**Effort: Medium**

Touch gestures for natural mobile interactions.

**Files to create:**
- `resources/views/components/pull-to-refresh.blade.php`
- `resources/views/components/swipe-action.blade.php`
- `resources/js/touch-gestures.js`

**Tasks:**
- [ ] Create pull-to-refresh component
- [ ] Add pull-to-refresh to schedule and dashboard views
- [ ] Create swipe-to-action component
- [ ] Implement swipe to approve/deny for managers
- [ ] Add swipe to view shift details
- [ ] Create touch gesture utility functions
- [ ] Add visual feedback during gestures
- [ ] Test on various touch devices

---

## 9.7 Mobile Schedule Views
**Effort: Large**

Schedule views optimized for vertical mobile screens.

**Tasks:**
- [ ] Create mobile-optimized week view (vertical list)
- [ ] Add swipe navigation between weeks
- [ ] Create day view optimized for mobile
- [ ] Add expandable shift details
- [ ] Implement schedule PDF export for offline viewing
- [ ] Create "My Shifts" focused view for employees
- [ ] Add calendar month picker for date navigation
- [ ] Ensure smooth scrolling performance
- [ ] Test on various screen sizes

---

## 9.8 Manager Mobile Approvals
**Effort: Medium**

Enable managers to handle approvals from mobile devices.

**Tasks:**
- [ ] Create mobile-friendly pending approvals list
- [ ] Add quick approve/deny actions
- [ ] Implement bulk approval functionality
- [ ] Add employee contact quick actions (call, message)
- [ ] Create coverage at-a-glance for today
- [ ] Show who's currently clocked in
- [ ] Add assign open shift functionality
- [ ] Test approval workflows on mobile

---

## 9.9 Mobile-Specific CSS & Performance
**Effort: Medium**

CSS utilities and optimizations for mobile performance.

**Files to create:**
- `resources/css/mobile.css`

**Tasks:**
- [ ] Add safe area inset utilities
- [ ] Create touch manipulation utilities
- [ ] Implement minimum touch target sizing
- [ ] Add momentum scrolling for iOS
- [ ] Create bottom sheet animation styles
- [ ] Optimize images with lazy loading
- [ ] Minimize JavaScript bundle for mobile
- [ ] Add viewport meta tag optimizations
- [ ] Test performance on low-end devices
- [ ] Run Lighthouse mobile audit

---

## 9.10 Accessibility Improvements
**Effort: Medium**

Ensure the application is accessible to all users.

**Tasks:**
- [ ] Add ARIA labels to all interactive elements
- [ ] Ensure keyboard navigation works throughout
- [ ] Add skip navigation links
- [ ] Test with screen readers (VoiceOver, TalkBack)
- [ ] Ensure color contrast meets WCAG AA
- [ ] Add focus indicators for keyboard users
- [ ] Implement reduced motion preferences
- [ ] Add proper heading hierarchy
- [ ] Create accessibility documentation
- [ ] Run automated accessibility audit
