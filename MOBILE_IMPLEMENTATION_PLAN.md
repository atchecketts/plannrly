# Mobile Implementation Plan

## Overview

This document outlines the implementation plan for mobile-responsive employee functionality in Plannrly. The goal is to provide employees with full access to all features via mobile devices, following the design established in `resources/views/samples/employee-mobile.blade.php`.

## Design Principles

1. **Mobile-First**: Design for mobile screens first, then enhance for desktop
2. **Touch-Friendly**: Large tap targets (minimum 44x44px), proper spacing
3. **Dark Theme**: Consistent with admin dashboard (gray-950 background)
4. **Bottom Navigation**: Primary navigation at bottom for thumb accessibility
5. **Progressive Enhancement**: Core functionality works without JavaScript

## Architecture

### Responsive Strategy

The app will use a single codebase with responsive layouts:
- **Mobile (<1024px)**: Bottom navigation, stacked layouts, simplified views
- **Desktop (≥1024px)**: Sidebar navigation, multi-column layouts (existing)

### Detection & Routing

```php
// Option A: CSS-only responsive (recommended)
// Use Tailwind's lg: breakpoint to show/hide appropriate layouts

// Option B: Dedicated mobile routes (if significantly different logic needed)
Route::prefix('m')->group(function () {
    Route::get('/', [MobileController::class, 'home']);
    // ...
});
```

**Recommendation**: Use Option A (CSS-only) to avoid code duplication.

## Implementation Phases

### Phase 1: Mobile Layout Foundation

**Files to Create/Modify:**
- `resources/views/components/layouts/app.blade.php` - Add mobile bottom nav
- `resources/views/components/bottom-nav.blade.php` - New component

**Bottom Navigation Items:**
1. **Home** - Dashboard/overview
2. **Shifts** - Schedule view (calendar)
3. **Clock** - Clock in/out (center, prominent)
4. **Swap** - Shift swap requests
5. **Profile** - User settings, leave balance

**Tasks:**
1. Add mobile detection CSS classes to body
2. Create bottom navigation component with active states
3. Hide sidebar on mobile (lg:hidden → lg:flex)
4. Show bottom nav on mobile only (flex lg:hidden)
5. Add safe-area-inset support for iOS notch/home indicator

### Phase 2: Employee Dashboard (Mobile Home)

**Files to Create/Modify:**
- `resources/views/dashboard/employee.blade.php` - Redesign for mobile
- `app/Http/Controllers/DashboardController.php` - Add mobile data

**Components:**
1. **Header**
   - User avatar + greeting
   - Department & role info
   - Notification bell with badge

2. **Today's Shift Card**
   - Shift date and status badge (Active/Upcoming/None)
   - Time range (9:00 AM - 5:00 PM)
   - Duration and break info
   - Clock in/out section (if enabled)
     - Clocked in time
     - Working duration counter
     - Start Break / Clock Out buttons

3. **This Week Summary**
   - Scheduled hours
   - Worked hours
   - Shifts remaining

4. **Upcoming Shifts List**
   - Date pill (day abbreviation + date number)
   - Time range
   - Department + role
   - Duration

5. **Leave Balance Card**
   - Leave type name
   - Days remaining / total
   - Progress bar
   - "Request Leave" link

6. **Pending Requests**
   - Leave requests awaiting approval
   - Shift swap requests pending

**Data Requirements:**
```php
// DashboardController::employeeDashboard()
$todayShift = Shift::where('user_id', $user->id)
    ->whereDate('date', today())
    ->with(['location', 'department', 'businessRole', 'timeEntry'])
    ->first();

$weekSummary = [
    'scheduled_hours' => $this->getScheduledHours($user),
    'worked_hours' => $this->getWorkedHours($user),
    'shifts_remaining' => $this->getShiftsRemaining($user),
];

$leaveBalance = LeaveBalance::where('user_id', $user->id)
    ->with('leaveType')
    ->get();

$pendingRequests = [
    'leave' => LeaveRequest::where('user_id', $user->id)
        ->where('status', 'requested')->count(),
    'swaps' => ShiftSwapRequest::where('requester_id', $user->id)
        ->where('status', 'pending')->count(),
];
```

### Phase 3: Shifts View (Mobile Calendar)

**Files to Create/Modify:**
- `resources/views/schedule/mobile-calendar.blade.php` - New view
- `app/Http/Controllers/ScheduleController.php` - Add employee view

**Features:**
1. **Week Selector**
   - Previous/Next week navigation
   - "Today" quick jump
   - Current week date range display

2. **Day List View**
   - Each day as expandable card
   - Shows shift count badge
   - Tap to expand and see shift details

3. **Shift Detail**
   - Full time range
   - Location, department, role
   - Notes (if any)
   - "Request Swap" button

**Filtering:**
- Employees see only their own shifts
- Show published shifts only (not drafts)

### Phase 4: Clock In/Out Functionality

**Files to Create/Modify:**
- `app/Http/Controllers/TimeEntryController.php` - New or extend
- `resources/views/components/clock-widget.blade.php` - New component
- `app/Models/TimeEntry.php` - Ensure model exists
- Database migration if TimeEntry table doesn't exist

**Features:**
1. **Clock In**
   - Big, prominent button
   - Geolocation capture (optional, configurable)
   - Timestamp recording
   - Confirmation feedback

2. **Clock Out**
   - Similar to clock in
   - Calculate total worked time
   - Prompt for break deduction if not tracked

3. **Break Management**
   - Start Break button
   - End Break button
   - Break duration tracking
   - Multiple breaks support

4. **Time Display**
   - Live counter showing current session duration
   - Today's total worked time
   - Clocked in/out times

**API Endpoints:**
```php
POST /api/time-entries/clock-in
POST /api/time-entries/clock-out
POST /api/time-entries/start-break
POST /api/time-entries/end-break
GET  /api/time-entries/current
```

**Validation:**
- Can only clock in during shift window (configurable buffer)
- Can only clock in for assigned shifts
- Cannot clock in twice without clocking out
- Break must be within clocked-in period

### Phase 5: Shift Swap Functionality

**Files to Create/Modify:**
- `resources/views/shift-swaps/mobile-index.blade.php`
- `resources/views/shift-swaps/mobile-create.blade.php`
- `app/Http/Controllers/ShiftSwapRequestController.php` - Extend

**Features:**
1. **My Swap Requests**
   - List of outgoing requests
   - Status badges (Pending, Approved, Rejected)
   - Cancel pending request option

2. **Incoming Swap Requests**
   - Requests from other employees
   - Accept/Decline buttons
   - Shift comparison view

3. **Create Swap Request**
   - Select shift to swap
   - Choose target employee (optional)
   - Add reason/notes
   - Submit request

4. **Available Shifts**
   - Browse shifts from other employees
   - Filter by date, department, role
   - "Request Swap" button

### Phase 6: Profile & Settings

**Files to Create/Modify:**
- `resources/views/profile/mobile-show.blade.php`
- `app/Http/Controllers/ProfileController.php` - Extend

**Sections:**
1. **Profile Header**
   - Avatar (with upload option)
   - Name, email, phone
   - Department & role

2. **Leave Balance Summary**
   - All leave types with balances
   - "Request Leave" button
   - Leave history link

3. **My Leave Requests**
   - Recent requests with status
   - "View All" link

4. **Availability** (if feature enabled)
   - Set recurring availability
   - Block specific dates

5. **Settings**
   - Notification preferences
   - Change password
   - Logout

### Phase 7: Leave Request (Mobile)

**Files to Create/Modify:**
- `resources/views/leave-requests/mobile-create.blade.php`
- `resources/views/leave-requests/mobile-index.blade.php`

**Features:**
1. **Request Leave Form**
   - Leave type selector
   - Date range picker (mobile-friendly)
   - Reason/notes
   - Balance display for selected type

2. **My Requests List**
   - Status filter tabs (All, Pending, Approved, Rejected)
   - Request cards with key info
   - Cancel option for pending

### Phase 8: Notifications

**Files to Create/Modify:**
- `resources/views/notifications/mobile-index.blade.php`
- `app/Http/Controllers/NotificationController.php`

**Notification Types:**
1. Shift assigned/changed
2. Leave request status update
3. Shift swap request received
4. Shift swap status update
5. Schedule published
6. Upcoming shift reminder

**Features:**
- Notification list with read/unread states
- Mark as read
- Mark all as read
- Link to relevant page

## Database Changes

### TimeEntry Table (if not exists)
```php
Schema::create('time_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained();
    $table->foreignId('shift_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->timestamp('clock_in_at');
    $table->timestamp('clock_out_at')->nullable();
    $table->json('breaks')->nullable(); // [{start: timestamp, end: timestamp}]
    $table->integer('total_break_minutes')->default(0);
    $table->decimal('latitude_in', 10, 8)->nullable();
    $table->decimal('longitude_in', 11, 8)->nullable();
    $table->decimal('latitude_out', 10, 8)->nullable();
    $table->decimal('longitude_out', 11, 8)->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### User Notification Preferences
```php
// Add to users table or create separate table
$table->json('notification_preferences')->nullable();
```

## API Considerations

For better mobile experience, consider:

1. **JSON API Endpoints** for AJAX operations:
   - Clock in/out
   - Swap request actions
   - Notification marking

2. **Response Format**:
```json
{
    "success": true,
    "message": "Clocked in successfully",
    "data": {
        "time_entry_id": 123,
        "clock_in_at": "2024-01-15T09:00:00Z"
    }
}
```

## Testing Plan

### Unit Tests
- TimeEntry model methods
- Clock in/out validation rules
- Break calculation logic

### Feature Tests
- Clock in/out flow
- Shift swap request flow
- Leave request from mobile
- Notification read/unread

### Manual Testing
- iOS Safari
- Android Chrome
- Various screen sizes (375px, 414px, 390px)
- Touch interactions
- Offline behavior (graceful degradation)

## Rollout Strategy

1. **Phase 1-2**: Foundation + Dashboard (MVP for employees to view)
2. **Phase 3**: Shifts view (employees can see their schedule)
3. **Phase 4**: Clock in/out (time tracking enabled)
4. **Phase 5-6**: Swap + Profile (self-service features)
5. **Phase 7-8**: Leave + Notifications (complete feature parity)

## Success Metrics

1. Mobile usage percentage
2. Clock in/out accuracy (vs manual entry)
3. Self-service adoption (swaps, leave requests)
4. Support ticket reduction
5. User satisfaction surveys

## Technical Debt & Future Considerations

1. **PWA Support**: Add service worker for offline capability
2. **Push Notifications**: Web push for real-time alerts
3. **Native App**: Consider React Native/Flutter if needed
4. **Biometric Auth**: Face ID/Touch ID for clock in
5. **QR Code Clock In**: Scan to clock in at location

---

## Implementation Order (Recommended)

| Priority | Phase | Effort | Dependencies |
|----------|-------|--------|--------------|
| 1 | Phase 1: Mobile Layout | Medium | None |
| 2 | Phase 2: Employee Dashboard | Medium | Phase 1 |
| 3 | Phase 3: Shifts View | Low | Phase 1 |
| 4 | Phase 4: Clock In/Out | High | Phase 1, 2 |
| 5 | Phase 6: Profile | Low | Phase 1 |
| 6 | Phase 7: Leave Request | Medium | Phase 1, 6 |
| 7 | Phase 5: Shift Swap | Medium | Phase 1, 3 |
| 8 | Phase 8: Notifications | Medium | All above |

**Total Estimated Effort**: 3-4 weeks for full implementation
