# Plannrly - High Level Design Document

## 1. Executive Summary

Plannrly is a multi-tenant SaaS application for managing staff shifts, leave requests, and workforce scheduling. The system provides role-based access control with hierarchical permissions, AI-assisted scheduling, and comprehensive time tracking capabilities.

---

## 2. System Architecture

### 2.1 Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 (PHP 8.5) |
| Database | MySQL 8.x |
| Frontend | Laravel Blade + Livewire |
| CSS Framework | Tailwind CSS v4 |
| Calendar/Scheduling | FullCalendar.js or similar |
| Mobile | Progressive Web App (PWA) + dedicated mobile views |
| Notifications | Laravel Notifications (Email, Database, Push) |
| AI Integration | OpenAI API / Anthropic API |
| Testing | PHPUnit |
| Code Style | Laravel Pint |

### 2.2 Multi-Tenant Architecture

The application uses a **single database with tenant_id discrimination**:
- All tenant-scoped tables include a `tenant_id` foreign key
- Global scopes automatically filter data by tenant
- The first tenant (ID: 1) is reserved for "Plannrly" (the SaaS provider)

```
┌─────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER                       │
├─────────────────────────────────────────────────────────────┤
│  Tenant Middleware → Resolves tenant from authenticated user │
│  Global Scopes → Auto-filter all queries by tenant_id        │
│  Policies → Enforce role-based permissions                   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    SINGLE MySQL DATABASE                     │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐           │
│  │Tenant 1 │ │Tenant 2 │ │Tenant 3 │ │Tenant N │           │
│  │Plannrly │ │Company A│ │Company B│ │   ...   │           │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘           │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Data Model

### 3.1 Entity Relationship Diagram

```
┌──────────────┐
│   TENANTS    │
│──────────────│
│ id           │
│ name         │
│ slug         │
│ settings     │
│ is_active    │
└──────┬───────┘
       │
       │ 1:N
       ▼
┌──────────────┐       ┌──────────────┐
│  LOCATIONS   │       │    USERS     │
│──────────────│       │──────────────│
│ id           │       │ id           │
│ tenant_id    │◄──────│ tenant_id    │
│ name         │       │ first_name   │
│ address      │       │ last_name    │
│ is_active    │       │ email        │
└──────┬───────┘       │ password     │
       │               │ is_active    │
       │ 1:N           └──────┬───────┘
       ▼                      │
┌──────────────┐              │
│ DEPARTMENTS  │              │
│──────────────│              │
│ id           │              │
│ tenant_id    │              │
│ location_id  │              │
│ name         │              │
│ is_active    │              │
└──────┬───────┘              │
       │                      │
       │ 1:N                  │
       ▼                      │
┌──────────────┐              │
│BUSINESS_ROLES│              │
│──────────────│              │
│ id           │              │
│ tenant_id    │              │
│ department_id│              │
│ name         │              │
│ description  │              │
│ hourly_rate  │              │
│ is_active    │              │
└──────────────┘              │
                              │
       ┌──────────────────────┘
       │
       ▼
┌─────────────────────────────────────────────────────────────┐
│                    USER_ROLE_ASSIGNMENTS                     │
│─────────────────────────────────────────────────────────────│
│ id | user_id | system_role | location_id | department_id    │
│─────────────────────────────────────────────────────────────│
│ Assigns system roles (Admin, Location Admin, Dept Admin)    │
│ to users with optional location/department scope            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    USER_BUSINESS_ROLES                       │
│─────────────────────────────────────────────────────────────│
│ id | user_id | business_role_id | is_primary                │
│─────────────────────────────────────────────────────────────│
│ Assigns job functions to users (can have multiple)          │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 Core Tables

#### Tenants
```
tenants
├── id (PK)
├── name (Company Name)
├── slug (URL-friendly identifier)
├── email (Primary contact)
├── phone
├── address
├── logo_path
├── settings (JSON - timezone, date format, etc.)
├── is_active
├── trial_ends_at
├── created_at
└── updated_at
```

#### Users
```
users
├── id (PK)
├── tenant_id (FK)
├── first_name
├── last_name
├── email (unique per tenant)
├── phone
├── password
├── avatar_path
├── is_active
├── email_verified_at
├── remember_token
├── last_login_at
├── created_at
└── updated_at
```

#### System Roles (Enum/Constants, not a table)
```
SystemRole::SUPER_ADMIN      // Plannrly staff only
SystemRole::ADMIN            // Tenant administrator
SystemRole::LOCATION_ADMIN   // Location-scoped admin
SystemRole::DEPARTMENT_ADMIN // Department-scoped admin
SystemRole::EMPLOYEE         // Regular staff member
```

#### User Role Assignments
```
user_role_assignments
├── id (PK)
├── user_id (FK)
├── system_role (enum)
├── location_id (FK, nullable) - Required for LOCATION_ADMIN
├── department_id (FK, nullable) - Required for DEPARTMENT_ADMIN
├── assigned_by (FK to users)
├── created_at
└── updated_at

UNIQUE: user_id + system_role + location_id + department_id
```

#### Locations
```
locations
├── id (PK)
├── tenant_id (FK)
├── name
├── address_line_1
├── address_line_2
├── city
├── state
├── postal_code
├── country
├── phone
├── timezone
├── is_active
├── created_at
└── updated_at
```

#### Departments
```
departments
├── id (PK)
├── tenant_id (FK)
├── location_id (FK)
├── name
├── description
├── color (for calendar display)
├── is_active
├── created_at
└── updated_at
```

#### Business Roles (Job Functions)
```
business_roles
├── id (PK)
├── tenant_id (FK)
├── department_id (FK)
├── name (e.g., "Cashier", "Supervisor", "Nurse")
├── description
├── color (for calendar display)
├── default_hourly_rate
├── is_active
├── created_at
└── updated_at
```

#### User Business Roles
```
user_business_roles
├── id (PK)
├── user_id (FK)
├── business_role_id (FK)
├── hourly_rate (override, nullable)
├── is_primary
├── created_at
└── updated_at

UNIQUE: user_id + business_role_id
```

### 3.3 Scheduling Tables

#### Rotas (Schedule Containers)
```
rotas
├── id (PK)
├── tenant_id (FK)
├── location_id (FK, nullable - null means all locations)
├── department_id (FK, nullable - null means all departments)
├── name (e.g., "Week 1 - January 2024")
├── start_date
├── end_date
├── status (enum: draft, published, archived)
├── published_at
├── published_by (FK to users)
├── created_by (FK to users)
├── created_at
└── updated_at
```

#### Shifts
```
shifts
├── id (PK)
├── tenant_id (FK)
├── rota_id (FK)
├── location_id (FK)
├── department_id (FK)
├── business_role_id (FK)
├── user_id (FK, nullable - null means unassigned)
├── date
├── start_time
├── end_time
├── break_duration_minutes (nullable)
├── notes
├── status (enum: scheduled, in_progress, completed, missed, cancelled)
├── is_recurring
├── recurrence_rule (JSON, nullable - for recurring shifts)
├── parent_shift_id (FK, nullable - for recurring instances)
├── created_by (FK to users)
├── created_at
└── updated_at
```

#### Shift Recurrence Rule (JSON Structure)
```json
{
  "frequency": "weekly",           // daily, weekly, monthly
  "interval": 1,                   // every N frequency
  "days_of_week": [1, 3, 5],      // Monday, Wednesday, Friday
  "end_date": "2024-12-31",       // or null for indefinite
  "end_after_occurrences": 10     // alternative to end_date
}
```

### 3.4 Time Tracking Tables

#### Time Entries (Clock In/Out)
```
time_entries
├── id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── shift_id (FK)
├── clock_in_at
├── clock_out_at
├── break_start_at
├── break_end_at
├── actual_break_minutes
├── notes
├── clock_in_location (JSON - lat/lng if available)
├── clock_out_location (JSON)
├── status (enum: clocked_in, on_break, clocked_out)
├── created_at
└── updated_at
```

### 3.5 Leave Management Tables

#### Leave Types
```
leave_types
├── id (PK)
├── tenant_id (FK, nullable - null for system defaults)
├── name (Annual, Sick, Unpaid, Maternity/Paternity, Other)
├── color
├── requires_approval
├── affects_allowance
├── is_paid
├── is_active
├── created_at
└── updated_at
```

#### Leave Allowances
```
leave_allowances
├── id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── leave_type_id (FK)
├── year
├── total_days
├── used_days
├── carried_over_days
├── created_at
└── updated_at

UNIQUE: user_id + leave_type_id + year
```

#### Leave Requests
```
leave_requests
├── id (PK)
├── tenant_id (FK)
├── user_id (FK)
├── leave_type_id (FK)
├── start_date
├── end_date
├── start_half_day (boolean - morning/afternoon)
├── end_half_day (boolean)
├── total_days (calculated)
├── reason
├── status (enum: draft, requested, approved, rejected)
├── reviewed_by (FK to users, nullable)
├── reviewed_at
├── review_notes
├── created_at
└── updated_at
```

### 3.6 Shift Swap Tables

#### Shift Swap Requests
```
shift_swap_requests
├── id (PK)
├── tenant_id (FK)
├── requesting_user_id (FK)
├── target_user_id (FK)
├── requesting_shift_id (FK)
├── target_shift_id (FK, nullable - for open swap offers)
├── reason
├── status (enum: pending, accepted, rejected, cancelled)
├── responded_at
├── approved_by (FK to users, nullable - admin approval)
├── approved_at
├── created_at
└── updated_at
```

### 3.7 Notification Tables

#### Notifications (Laravel's built-in)
```
notifications
├── id (UUID)
├── type
├── notifiable_type
├── notifiable_id
├── data (JSON)
├── read_at
├── created_at
└── updated_at
```

#### Notification Preferences
```
notification_preferences
├── id (PK)
├── user_id (FK)
├── notification_type
├── email_enabled
├── push_enabled
├── in_app_enabled
├── created_at
└── updated_at
```

---

## 4. System Roles & Permissions

### 4.1 Permission Matrix

| Permission | SuperAdmin | Admin | Location Admin | Dept Admin | Employee |
|------------|:----------:|:-----:|:--------------:|:----------:|:--------:|
| **Tenant Management** |
| View all tenants | ✓ | - | - | - | - |
| Create/Edit tenants | ✓ | - | - | - | - |
| Impersonate users | ✓ | - | - | - | - |
| **Location Management** |
| View locations | ✓ | All | Assigned | - | - |
| Create locations | ✓ | ✓ | - | - | - |
| Edit locations | ✓ | ✓ | Assigned | - | - |
| **Department Management** |
| View departments | ✓ | All | In Location | Assigned | - |
| Create departments | ✓ | ✓ | In Location | - | - |
| Edit departments | ✓ | ✓ | In Location | Assigned | - |
| **Business Role Management** |
| View business roles | ✓ | All | In Location | In Dept | Own |
| Create business roles | ✓ | ✓ | In Location | In Dept | - |
| Edit business roles | ✓ | ✓ | In Location | In Dept | - |
| **User Management** |
| View users | ✓ | All | In Location | In Dept | - |
| Create users | ✓ | ✓ | In Location | In Dept | - |
| Edit users | ✓ | ✓ | In Location | In Dept | - |
| Assign system roles | ✓ | ✓ | Limited* | Limited* | - |
| **Rota/Shift Management** |
| View rotas | ✓ | All | In Location | In Dept | Own |
| Create/Edit rotas | ✓ | ✓ | In Location | In Dept | - |
| Publish rotas | ✓ | ✓ | In Location | In Dept | - |
| Assign shifts | ✓ | ✓ | In Location | In Dept | - |
| **Leave Management** |
| Request leave | - | ✓ | ✓ | ✓ | ✓ |
| View leave requests | ✓ | All | In Location | In Dept | Own |
| Approve leave | ✓ | All | In Location | In Dept | - |
| **Time Tracking** |
| Clock in/out | - | ✓ | ✓ | ✓ | ✓ |
| View time entries | ✓ | All | In Location | In Dept | Own |
| Edit time entries | ✓ | All | In Location | In Dept | - |
| **Shift Swaps** |
| Request swap | - | ✓ | ✓ | ✓ | ✓ |
| Approve swap | ✓ | All | In Location | In Dept | - |
| **Reports** |
| View reports | ✓ | All | In Location | In Dept | Own |

*Limited: Location Admin can assign Dept Admin within their location. Dept Admin cannot assign system roles.

### 4.2 Leave Approval Escalation

```
┌─────────────────────────────────────────────────────────────┐
│                  LEAVE APPROVAL FLOW                         │
└─────────────────────────────────────────────────────────────┘

Employee submits leave request
            │
            ▼
    ┌───────────────┐
    │ Dept Admin    │ ──── Exists? ──── YES ──→ Notified
    │ for user's    │                              │
    │ department    │ ◄──── NO                     │
    └───────────────┘       │                      │
            │               │                      │
            │               ▼                      │
            │      ┌───────────────┐               │
            │      │Location Admin │ ── Exists? ── YES ──→ Notified
            │      │ for user's    │                         │
            │      │ location      │ ◄── NO                  │
            │      └───────────────┘     │                   │
            │               │            │                   │
            │               │            ▼                   │
            │               │    ┌───────────────┐           │
            │               │    │    Admin      │ ◄─────────┤
            │               │    │ (always       │           │
            │               │    │  exists)      │           │
            │               │    └───────────────┘           │
            │               │            │                   │
            ▼               ▼            ▼                   ▼
    ┌─────────────────────────────────────────────────────────┐
    │              ANY OF THESE CAN APPROVE                   │
    └─────────────────────────────────────────────────────────┘
```

---

## 5. Feature Specifications

### 5.1 Registration Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    REGISTRATION FORM                         │
├─────────────────────────────────────────────────────────────┤
│  Company Name:     [________________________]               │
│  First Name:       [________________________]               │
│  Last Name:        [________________________]               │
│  Email Address:    [________________________]               │
│  Password:         [________________________]               │
│  Confirm Password: [________________________]               │
│                                                             │
│                    [  Register  ]                           │
└─────────────────────────────────────────────────────────────┘

On Submit:
1. Create Tenant record
2. Create User record (linked to tenant)
3. Create UserRoleAssignment (system_role: ADMIN)
4. Create default Leave Types for tenant
5. Send verification email
6. Redirect to Admin Dashboard
```

### 5.2 Shift Calendar Interface

```
┌─────────────────────────────────────────────────────────────┐
│  ◄ Previous Week     Week of Jan 15-21, 2024    Next Week ►│
├─────────────────────────────────────────────────────────────┤
│ Filters: [Location ▼] [Department ▼] [Role ▼] [User ▼]     │
│ View:    [Day] [Week] [Month] [Timeline]                    │
│ Status:  [Draft] ○ Published                                │
├─────────────────────────────────────────────────────────────┤
│         │ Mon 15 │ Tue 16 │ Wed 17 │ Thu 18 │ Fri 19 │ ... │
├─────────┼────────┼────────┼────────┼────────┼────────┼─────┤
│ John D. │████████│        │████████│████████│        │     │
│ Cashier │ 9-5    │        │ 9-5    │ 12-8   │        │     │
├─────────┼────────┼────────┼────────┼────────┼────────┼─────┤
│ Jane S. │        │████████│████████│        │████████│     │
│ Cashier │        │ 9-5    │ 9-5    │  LEAVE │ 9-5    │     │
├─────────┼────────┼────────┼────────┼────────┼────────┼─────┤
│ ⚠ UNAS- │████████│████████│        │████████│████████│     │
│ SIGNED  │ 6-2    │ 6-2    │        │ 6-2    │ 6-2    │     │
└─────────────────────────────────────────────────────────────┘
│ [+ Add Shift] [🤖 AI Assist] [📋 Copy Week] [📤 Publish]   │
└─────────────────────────────────────────────────────────────┘
```

**Drag & Drop Functionality:**
- Drag shifts between days for same user
- Drag shifts between users (same role)
- Drag unassigned shifts to assign them
- Drag to extend/shorten shift duration
- Copy shifts with Ctrl+Drag

### 5.3 AI-Assisted Scheduling

```
┌─────────────────────────────────────────────────────────────┐
│             🤖 AI SCHEDULING ASSISTANT                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  What would you like help with?                             │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  ○ Fill unassigned shifts                           │   │
│  │  ○ Suggest optimal schedule for selected period     │   │
│  │  ○ Balance hours across team                        │   │
│  │  ○ Find coverage for [specific date/shift]         │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  Constraints to consider:                                   │
│  ☑ Approved leave                                          │
│  ☑ User availability preferences                           │
│  ☑ Maximum hours per user                                  │
│  ☑ Minimum rest between shifts                             │
│  ☐ Cost optimization                                       │
│                                                             │
│                    [Generate Suggestions]                   │
└─────────────────────────────────────────────────────────────┘

                          │
                          ▼

┌─────────────────────────────────────────────────────────────┐
│             SUGGESTED ASSIGNMENTS                            │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Mon Jan 15, 6:00 AM - 2:00 PM (Cashier)                   │
│  ├─ Currently: Unassigned                                  │
│  └─ Suggestion: John D. (8 hrs this week, no conflicts)   │
│      [Accept] [Reject] [See Alternatives]                  │
│                                                             │
│  Tue Jan 16, 9:00 AM - 5:00 PM (Supervisor)                │
│  ├─ Currently: Unassigned                                  │
│  └─ Suggestion: Sarah M. (16 hrs this week, available)    │
│      [Accept] [Reject] [See Alternatives]                  │
│                                                             │
│  ───────────────────────────────────────────────────────   │
│  3 unassigned shifts remain - no suitable staff available  │
│                                                             │
│           [Accept All] [Review Individually]                │
└─────────────────────────────────────────────────────────────┘
```

**AI Considerations:**
- Staff on approved leave are excluded
- Prefer even distribution of hours
- Respect user-defined availability (future feature)
- Consider skill matching (business roles)
- Flag potential issues (overtime, insufficient rest)

### 5.4 Leave Request Flow

```
EMPLOYEE VIEW:
┌─────────────────────────────────────────────────────────────┐
│                  REQUEST LEAVE                               │
├─────────────────────────────────────────────────────────────┤
│  Leave Type:    [Annual Leave        ▼]                     │
│                                                             │
│  Available:     15 days remaining                           │
│                                                             │
│  Start Date:    [📅 2024-01-20] □ Half day (AM/PM)         │
│  End Date:      [📅 2024-01-22] □ Half day (AM/PM)         │
│                                                             │
│  Total Days:    3 days                                      │
│                                                             │
│  Reason:        [________________________________]          │
│                 [________________________________]          │
│                                                             │
│  ⚠ You have shifts scheduled during this period:           │
│    - Jan 20: 9:00 AM - 5:00 PM                             │
│    - Jan 21: 9:00 AM - 5:00 PM                             │
│                                                             │
│         [Save as Draft]  [Submit Request]                   │
└─────────────────────────────────────────────────────────────┘

APPROVER VIEW:
┌─────────────────────────────────────────────────────────────┐
│               PENDING LEAVE REQUESTS                         │
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────────────────┐│
│ │ John Doe - Annual Leave                                 ││
│ │ Jan 20-22, 2024 (3 days)                               ││
│ │ Reason: Family vacation                                 ││
│ │ Submitted: Jan 10, 2024                                 ││
│ │                                                         ││
│ │ ⚠ Coverage needed for 2 shifts                         ││
│ │                                                         ││
│ │ [View Calendar] [Approve ✓] [Reject ✗]                 ││
│ └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### 5.5 Shift Swap Flow

```
┌─────────────────────────────────────────────────────────────┐
│                  REQUEST SHIFT SWAP                          │
├─────────────────────────────────────────────────────────────┤
│  Your Shift:                                                │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Mon Jan 15, 2024 • 9:00 AM - 5:00 PM               │   │
│  │ Department: Front Desk • Role: Cashier             │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  Swap With:     [Select Colleague    ▼]                     │
│                 (Showing Cashiers in Front Desk only)       │
│                                                             │
│  Available to swap:                                         │
│  ○ Jane Smith - Tue Jan 16 (9:00 AM - 5:00 PM)            │
│  ○ Jane Smith - Wed Jan 17 (9:00 AM - 5:00 PM)            │
│  ○ Mike Johnson - Thu Jan 18 (12:00 PM - 8:00 PM)         │
│                                                             │
│  Reason:        [________________________________]          │
│                                                             │
│                    [Request Swap]                           │
└─────────────────────────────────────────────────────────────┘
```

### 5.6 Time Tracking Interface

```
EMPLOYEE MOBILE VIEW:
┌───────────────────────────┐
│      Today's Shift        │
│                           │
│   Mon, Jan 15, 2024       │
│   9:00 AM - 5:00 PM       │
│   Front Desk - Cashier    │
│                           │
│   ┌───────────────────┐   │
│   │                   │   │
│   │   ⏱ CLOCK IN     │   │
│   │                   │   │
│   └───────────────────┘   │
│                           │
│   Or scan QR code         │
│                           │
└───────────────────────────┘

AFTER CLOCK IN:
┌───────────────────────────┐
│      Currently Working    │
│                           │
│   Started: 8:58 AM        │
│   Duration: 2h 34m        │
│                           │
│   ┌─────────┐ ┌─────────┐ │
│   │  START  │ │ CLOCK   │ │
│   │  BREAK  │ │  OUT    │ │
│   └─────────┘ └─────────┘ │
│                           │
│   Scheduled end: 5:00 PM  │
│                           │
└───────────────────────────┘

ADMIN VIEW - ACTUAL VS SCHEDULED:
┌─────────────────────────────────────────────────────────────┐
│  Employee    │ Scheduled     │ Actual        │ Variance     │
├──────────────┼───────────────┼───────────────┼──────────────┤
│ John Doe     │ 9:00 - 17:00  │ 8:58 - 17:15  │ +17 min      │
│ Jane Smith   │ 9:00 - 17:00  │ 9:12 - 17:05  │ -7 min       │
│ Mike Johnson │ 12:00 - 20:00 │ NOT CLOCKED   │ ⚠ Missing    │
└─────────────────────────────────────────────────────────────┘
```

---

## 6. Dashboard Specifications

### 6.1 SuperAdmin Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│  PLANNRLY SUPERADMIN DASHBOARD                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐        │
│  │   TENANTS    │ │    USERS     │ │   REVENUE    │        │
│  │     247      │ │    3,842     │ │   £45,230    │        │
│  │   +12 MTD    │ │   +156 MTD   │ │   +8% MoM    │        │
│  └──────────────┘ └──────────────┘ └──────────────┘        │
│                                                             │
│  RECENT ACTIVITY                   SYSTEM HEALTH            │
│  ├─ New tenant: ABC Corp          ├─ API: ✓ Healthy        │
│  ├─ New tenant: XYZ Ltd           ├─ Database: ✓ Healthy   │
│  └─ 15 new users today            └─ Queue: ✓ 0 failed     │
│                                                             │
│  TENANT LIST                                                │
│  [Search...] [+ Add Tenant] [Export]                       │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Tenant        │ Users │ Locations │ Status │ Actions│   │
│  ├───────────────┼───────┼───────────┼────────┼────────┤   │
│  │ ABC Corp      │  45   │    3      │ Active │ [👁][✏]│   │
│  │ XYZ Ltd       │  12   │    1      │ Trial  │ [👁][✏]│   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**SuperAdmin Reports:**
- Tenant growth & churn
- Revenue by tenant
- System usage statistics
- Feature adoption rates
- Error/exception logs

### 6.2 Admin Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│  [Company Logo] COMPANY ADMIN DASHBOARD          [Profile ▼]│
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  TODAY                              THIS WEEK               │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐        │
│  │   ON DUTY    │ │  ON LEAVE    │ │ TOTAL HOURS  │        │
│  │     24       │ │      3       │ │    842 hrs   │        │
│  │  of 35 staff │ │              │ │  vs 800 plan │        │
│  └──────────────┘ └──────────────┘ └──────────────┘        │
│                                                             │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐        │
│  │  PENDING     │ │  UNASSIGNED  │ │ SWAP         │        │
│  │  LEAVE       │ │  SHIFTS      │ │ REQUESTS     │        │
│  │     5        │ │     12       │ │     2        │        │
│  │ [Review →]   │ │ [Assign →]   │ │ [Review →]   │        │
│  └──────────────┘ └──────────────┘ └──────────────┘        │
│                                                             │
│  QUICK ACTIONS                                              │
│  [📅 View Schedule] [👤 Add User] [🏢 Add Location]        │
│                                                             │
│  TODAY'S SCHEDULE OVERVIEW                                  │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ [Mini calendar view showing today's shifts]         │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ATTENDANCE ALERTS                                          │
│  ├─ ⚠ Mike Johnson has not clocked in (shift started 9AM) │
│  └─ ⚠ 2 employees approaching overtime threshold          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Admin Reports:**
- Staff hours summary (scheduled vs actual)
- Leave balance overview
- Attendance report (late, absent, early leave)
- Overtime report
- Labor cost report
- Shift coverage analysis
- Department/Location comparison

### 6.3 Location Admin Dashboard

Similar to Admin Dashboard but filtered to assigned location(s):
- Only shows data for their location(s)
- Can manage departments within their location(s)
- Can approve leave for staff in their location(s)
- Reports scoped to their location(s)

### 6.4 Department Admin Dashboard

Similar to Location Admin but further filtered to assigned department(s):
- Only shows data for their department(s)
- Can manage business roles within their department(s)
- Can approve leave for staff in their department(s)
- Reports scoped to their department(s)

### 6.5 Employee Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│  Hello, John!                                    [Profile ▼]│
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  YOUR NEXT SHIFT                                            │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Tomorrow, Jan 16                                   │   │
│  │  9:00 AM - 5:00 PM                                  │   │
│  │  Front Desk • Cashier                               │   │
│  │                                  [View All Shifts]  │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  THIS WEEK          │  LEAVE BALANCE                        │
│  ┌─────────────────┐│  ┌─────────────────────────────────┐ │
│  │ 32 hrs scheduled││  │ Annual:     15 days remaining   │ │
│  │ 24 hrs worked   ││  │ Sick:       Unlimited            │ │
│  │ 1 shift remaining││  │ [Request Leave]                │ │
│  └─────────────────┘│  └─────────────────────────────────┘ │
│                                                             │
│  MY SCHEDULE                                                │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Mon 15  │  Tue 16  │  Wed 17  │  Thu 18  │  Fri 19 │   │
│  │  9-5     │  9-5     │   OFF    │  12-8    │  9-5    │   │
│  │ ████████ │ ████████ │          │ ████████ │ ████████│   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  PENDING REQUESTS                                           │
│  ├─ Leave request (Jan 20-22): Awaiting approval           │
│  └─ Shift swap with Jane S.: Awaiting response             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Employee Reports/Views:**
- Personal schedule (calendar view)
- Hours worked (weekly/monthly)
- Leave history and balances
- Time entries history
- Pay period summary (if applicable)

---

## 7. Notification System

### 7.1 Notification Types

| Event | Recipients | Channels |
|-------|------------|----------|
| Shift assigned | Employee | Email, Push, In-app |
| Shift updated | Employee | Email, Push, In-app |
| Rota published | All affected employees | Email, Push, In-app |
| Leave requested | Approvers (escalation) | Email, Push, In-app |
| Leave approved/rejected | Employee | Email, Push, In-app |
| Shift swap requested | Target employee | Email, Push, In-app |
| Shift swap response | Requesting employee | Email, Push, In-app |
| Shift swap approved | Both employees | Email, Push, In-app |
| Clock-in reminder | Employee | Push |
| Missed clock-in | Employee, Manager | Email, Push, In-app |
| Overtime warning | Employee, Manager | Email, In-app |

### 7.2 Notification Preferences

Users can configure per-notification-type:
- Email notifications (on/off)
- Push notifications (on/off)
- In-app notifications (always on)

---

## 8. Mobile Experience

### 8.1 Progressive Web App (PWA)

The application will be built as a PWA with:
- Installable on mobile devices
- Offline capability for viewing schedules
- Push notification support
- Responsive design optimized for mobile

### 8.2 Mobile-First Features

- Clock in/out with one tap
- QR code scanning for clock in (optional)
- View upcoming shifts
- Request leave
- Request shift swaps
- View notifications
- Quick contact team members

### 8.3 Mobile Navigation

```
┌───────────────────────────┐
│                           │
│                           │
│      [Main Content]       │
│                           │
│                           │
├───────────────────────────┤
│ 🏠    📅    ⏱    👤    ≡  │
│ Home  Shifts Clock Profile More│
└───────────────────────────┘
```

---

## 9. API Structure

### 9.1 API Versioning

All API routes prefixed with `/api/v1/`

### 9.2 Core API Endpoints

```
Authentication:
POST   /api/v1/register
POST   /api/v1/login
POST   /api/v1/logout
POST   /api/v1/forgot-password
POST   /api/v1/reset-password

Locations:
GET    /api/v1/locations
POST   /api/v1/locations
GET    /api/v1/locations/{id}
PUT    /api/v1/locations/{id}
DELETE /api/v1/locations/{id}

Departments:
GET    /api/v1/locations/{locationId}/departments
POST   /api/v1/locations/{locationId}/departments
GET    /api/v1/departments/{id}
PUT    /api/v1/departments/{id}
DELETE /api/v1/departments/{id}

Business Roles:
GET    /api/v1/departments/{departmentId}/business-roles
POST   /api/v1/departments/{departmentId}/business-roles
GET    /api/v1/business-roles/{id}
PUT    /api/v1/business-roles/{id}
DELETE /api/v1/business-roles/{id}

Users:
GET    /api/v1/users
POST   /api/v1/users
GET    /api/v1/users/{id}
PUT    /api/v1/users/{id}
DELETE /api/v1/users/{id}
POST   /api/v1/users/{id}/roles
DELETE /api/v1/users/{id}/roles/{roleId}

Rotas:
GET    /api/v1/rotas
POST   /api/v1/rotas
GET    /api/v1/rotas/{id}
PUT    /api/v1/rotas/{id}
DELETE /api/v1/rotas/{id}
POST   /api/v1/rotas/{id}/publish

Shifts:
GET    /api/v1/rotas/{rotaId}/shifts
POST   /api/v1/rotas/{rotaId}/shifts
GET    /api/v1/shifts/{id}
PUT    /api/v1/shifts/{id}
DELETE /api/v1/shifts/{id}
POST   /api/v1/shifts/{id}/assign
POST   /api/v1/shifts/bulk-create
POST   /api/v1/shifts/ai-suggest

Time Entries:
POST   /api/v1/time-entries/clock-in
POST   /api/v1/time-entries/clock-out
POST   /api/v1/time-entries/start-break
POST   /api/v1/time-entries/end-break
GET    /api/v1/time-entries

Leave:
GET    /api/v1/leave-types
GET    /api/v1/leave-allowances
GET    /api/v1/leave-requests
POST   /api/v1/leave-requests
GET    /api/v1/leave-requests/{id}
PUT    /api/v1/leave-requests/{id}
POST   /api/v1/leave-requests/{id}/approve
POST   /api/v1/leave-requests/{id}/reject

Shift Swaps:
GET    /api/v1/shift-swaps
POST   /api/v1/shift-swaps
POST   /api/v1/shift-swaps/{id}/accept
POST   /api/v1/shift-swaps/{id}/reject
POST   /api/v1/shift-swaps/{id}/approve
```

---

## 10. Security Considerations

### 10.1 Authentication & Authorization

- Laravel Sanctum for API authentication
- Session-based authentication for web
- Role-based access control (RBAC) via policies
- Tenant isolation via global scopes
- Impersonation audit logging

### 10.2 Data Protection

- All passwords hashed with bcrypt
- HTTPS enforced
- CSRF protection on all forms
- XSS prevention via Blade escaping
- SQL injection prevention via Eloquent
- Rate limiting on authentication endpoints

### 10.3 Audit Logging

Track all sensitive operations:
- User login/logout
- Role assignments
- Shift changes
- Leave approvals
- Impersonation sessions
- Data exports

---

## 11. Performance Considerations

### 11.1 Database Optimization

- Indexes on tenant_id, foreign keys, and commonly filtered columns
- Eager loading to prevent N+1 queries
- Database-level constraints for data integrity
- Consider read replicas for reporting queries

### 11.2 Caching Strategy

- Cache tenant settings
- Cache user permissions
- Cache calendar data with appropriate invalidation
- Queue heavy operations (notifications, reports)

### 11.3 Scalability

- Stateless application design
- Queue workers for background jobs
- Horizontal scaling capability
- CDN for static assets

---

## 12. Implementation Phases

### Phase 1: Foundation (MVP)
1. Multi-tenant architecture setup
2. User authentication & registration
3. Tenant, Location, Department, Business Role CRUD
4. User management with role assignments
5. Basic shift creation and assignment
6. Basic leave request workflow

### Phase 2: Core Features
1. Shift calendar with drag-and-drop
2. Rota publishing workflow
3. Leave management with balances
4. Notification system
5. Dashboard implementations

### Phase 3: Advanced Features
1. Time tracking (clock in/out)
2. Shift swap requests
3. Recurring shifts
4. Reports generation
5. Mobile PWA optimization

### Phase 4: AI & Polish
1. AI-assisted scheduling
2. Advanced reports
3. Performance optimization
4. Comprehensive testing
5. Documentation

---

## 13. File Structure (Proposed)

```
app/
├── Console/
│   └── Commands/
├── Enums/
│   ├── SystemRole.php
│   ├── ShiftStatus.php
│   ├── LeaveRequestStatus.php
│   └── RotaStatus.php
├── Events/
│   ├── ShiftAssigned.php
│   ├── RotaPublished.php
│   └── LeaveRequestStatusChanged.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── V1/
│   │   ├── Auth/
│   │   ├── SuperAdmin/
│   │   ├── Admin/
│   │   ├── LocationAdmin/
│   │   └── DepartmentAdmin/
│   ├── Middleware/
│   │   ├── EnsureTenantAccess.php
│   │   └── CheckSystemRole.php
│   └── Requests/
├── Listeners/
├── Models/
│   ├── Tenant.php
│   ├── User.php
│   ├── Location.php
│   ├── Department.php
│   ├── BusinessRole.php
│   ├── UserRoleAssignment.php
│   ├── UserBusinessRole.php
│   ├── Rota.php
│   ├── Shift.php
│   ├── TimeEntry.php
│   ├── LeaveType.php
│   ├── LeaveAllowance.php
│   ├── LeaveRequest.php
│   ├── ShiftSwapRequest.php
│   └── NotificationPreference.php
├── Notifications/
├── Policies/
├── Providers/
├── Scopes/
│   └── TenantScope.php
└── Services/
    ├── ShiftSchedulingService.php
    ├── LeaveCalculationService.php
    └── AISchedulingService.php

database/
├── factories/
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php
    ├── TenantSeeder.php
    ├── SystemRoleSeeder.php
    └── LeaveTypeSeeder.php

resources/
├── views/
│   ├── layouts/
│   ├── components/
│   ├── superadmin/
│   ├── admin/
│   ├── location-admin/
│   ├── department-admin/
│   ├── employee/
│   └── auth/
├── css/
└── js/

routes/
├── web.php
├── api.php
├── auth.php
└── console.php

tests/
├── Feature/
└── Unit/
```

---

## 14. Design Decisions (Confirmed)

1. **Timezone Handling**: Per-location timezone with tenant default ✓

2. **Delete Strategy**: Soft delete for all core entities (users, shifts, etc.) ✓

3. **User Deactivation**: Shifts become unassigned, admin notified ✓

4. **Leave Conflicts**: Allow with warning, shifts must be manually reassigned ✓

5. **Billing/Subscription**: Defer to future phase, add trial_ends_at for now ✓

6. **Data Retention**: Configurable per tenant, default 2 years ✓

---

## 15. Approval

Please review this High Level Design document and confirm:

- [ ] Data model meets requirements
- [ ] Permission matrix is correct
- [ ] Feature specifications are complete
- [ ] Dashboard requirements are met
- [ ] Mobile approach is acceptable
- [ ] Implementation phases are appropriate
- [ ] Any questions in Section 14 need different answers

Once approved, development can begin with Phase 1.
