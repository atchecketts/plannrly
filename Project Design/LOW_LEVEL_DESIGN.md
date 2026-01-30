# Plannrly - Low Level Design Document

## 1. Database Schema Details

### 1.1 Entity Relationship Diagram

```
                                    ┌─────────────────────┐
                                    │      TENANTS        │
                                    │─────────────────────│
                                    │ id (PK)             │
                                    │ name                │
                                    │ slug (unique)       │
                                    │ email               │
                                    │ phone               │
                                    │ address             │
                                    │ logo_path           │
                                    │ settings (JSON)     │
                                    │ is_active           │
                                    │ trial_ends_at       │
                                    │ timestamps          │
                                    └──────────┬──────────┘
                                               │
                    ┌──────────────────────────┼──────────────────────────┐
                    │                          │                          │
                    ▼                          ▼                          ▼
        ┌───────────────────┐      ┌───────────────────┐      ┌───────────────────┐
        │     LOCATIONS     │      │      USERS        │      │   LEAVE_TYPES     │
        │───────────────────│      │───────────────────│      │───────────────────│
        │ id (PK)           │      │ id (PK)           │      │ id (PK)           │
        │ tenant_id (FK)    │◄─────│ tenant_id (FK)    │      │ tenant_id (FK)    │
        │ name              │      │ first_name        │      │ name              │
        │ address_line_1    │      │ last_name         │      │ color             │
        │ address_line_2    │      │ email (unique)    │      │ requires_approval │
        │ city              │      │ phone             │      │ affects_allowance │
        │ state             │      │ password          │      │ is_paid           │
        │ postal_code       │      │ avatar_path       │      │ is_active         │
        │ country           │      │ is_active         │      │ timestamps        │
        │ phone             │      │ email_verified_at │      │ soft_deletes      │
        │ timezone          │      │ remember_token    │      └───────────────────┘
        │ is_active         │      │ last_login_at     │
        │ timestamps        │      │ timestamps        │
        │ soft_deletes      │      │ soft_deletes      │
        └────────┬──────────┘      └─────────┬─────────┘
                 │                           │
                 ▼                           │
        ┌───────────────────┐                │
        │   DEPARTMENTS     │                │
        │───────────────────│                │
        │ id (PK)           │                │
        │ tenant_id (FK)    │                │
        │ location_id (FK)  │                │
        │ name              │                │
        │ description       │                │
        │ color             │                │
        │ is_active         │                │
        │ timestamps        │                │
        │ soft_deletes      │                │
        └────────┬──────────┘                │
                 │                           │
                 ▼                           │
        ┌───────────────────┐                │
        │  BUSINESS_ROLES   │                │
        │───────────────────│                │
        │ id (PK)           │                │
        │ tenant_id (FK)    │                │
        │ department_id (FK)│                │
        │ name              │                │
        │ description       │                │
        │ color             │                │
        │ default_hourly_rate│               │
        │ is_active         │                │
        │ timestamps        │                │
        │ soft_deletes      │                │
        └───────────────────┘                │
                                             │
        ┌────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                        USER_ROLE_ASSIGNMENTS                                 │
│─────────────────────────────────────────────────────────────────────────────│
│ id (PK) | user_id (FK) | system_role (enum) | location_id (FK) | dept_id   │
│─────────────────────────────────────────────────────────────────────────────│
│ UNIQUE: user_id + system_role + location_id + department_id                 │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        USER_BUSINESS_ROLES                                   │
│─────────────────────────────────────────────────────────────────────────────│
│ id (PK) | user_id (FK) | business_role_id (FK) | hourly_rate | is_primary  │
│─────────────────────────────────────────────────────────────────────────────│
│ UNIQUE: user_id + business_role_id                                          │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Table Specifications

#### tenants
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Company name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly identifier |
| email | VARCHAR(255) | NULLABLE | Primary contact email |
| phone | VARCHAR(255) | NULLABLE | Contact phone |
| address | TEXT | NULLABLE | Company address |
| logo_path | VARCHAR(255) | NULLABLE | Path to logo file |
| settings | JSON | NULLABLE | Tenant-specific settings |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| trial_ends_at | TIMESTAMP | NULLABLE | Trial expiration date |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- INDEX (is_active)

---

#### tenant_subscriptions
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, UNIQUE | Tenant reference |
| plan | VARCHAR(255) | DEFAULT 'basic' | Subscription plan |
| status | VARCHAR(255) | DEFAULT 'active' | Subscription status |
| billing_cycle | VARCHAR(255) | DEFAULT 'monthly' | 'monthly' or 'yearly' |
| current_period_start | TIMESTAMP | NULLABLE | Current billing period start |
| current_period_end | TIMESTAMP | NULLABLE | Current billing period end |
| cancelled_at | TIMESTAMP | NULLABLE | When subscription was cancelled |
| stripe_subscription_id | VARCHAR(255) | NULLABLE | External payment reference |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (tenant_id)
- INDEX (plan)
- INDEX (status)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

**SubscriptionPlan Enum Values:**
| Value | Description |
|-------|-------------|
| basic | Base subscription (all core features) |
| professional | Professional plan (includes some add-ons) |
| enterprise | Enterprise plan (all features included) |

**SubscriptionStatus Enum Values:**
| Value | Description |
|-------|-------------|
| active | Subscription is active |
| trialing | In trial period |
| past_due | Payment failed, grace period |
| cancelled | Subscription cancelled |
| expired | Subscription expired |

---

#### tenant_feature_addons
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| feature | VARCHAR(255) | NOT NULL | Feature identifier |
| enabled_at | TIMESTAMP | NOT NULL | When feature was enabled |
| expires_at | TIMESTAMP | NULLABLE | When feature expires (null = never) |
| stripe_subscription_item_id | VARCHAR(255) | NULLABLE | External payment reference |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (tenant_id, feature)
- INDEX (tenant_id)
- INDEX (feature)
- INDEX (expires_at)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

**Feature Enum Values:**
| Value | Description | Tier |
|-------|-------------|------|
| ai_scheduling | AI-Powered Scheduling | Premium |
| advanced_analytics | Advanced Analytics & Reports | Premium |
| advanced_geofencing | Advanced Geofencing & Location Verification | Premium |
| labor_forecasting | Labor Demand Forecasting | Premium |
| payroll_integrations | Payroll System Integrations | Premium |
| team_messaging | Team Messaging & Announcements | Premium |
| document_management | Document & Certification Management | Premium |
| multi_location_analytics | Multi-Location Analytics Dashboard | Enterprise |
| custom_branding | Custom Branding / White Label | Enterprise |
| api_access | API Access for Integrations | Enterprise |
| priority_support | Priority Support | Enterprise |

---

#### users
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, NULLABLE | Tenant reference |
| first_name | VARCHAR(255) | NOT NULL | User's first name |
| last_name | VARCHAR(255) | NOT NULL | User's last name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email address |
| phone | VARCHAR(255) | NULLABLE | Phone number |
| email_verified_at | TIMESTAMP | NULLABLE | Email verification time |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| avatar_path | VARCHAR(255) | NULLABLE | Profile image path |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| last_login_at | TIMESTAMP | NULLABLE | Last login timestamp |
| remember_token | VARCHAR(100) | NULLABLE | Session token |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (tenant_id)
- INDEX (is_active)
- INDEX (deleted_at)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE SET NULL

---

#### locations
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| name | VARCHAR(255) | NOT NULL | Location name |
| address_line_1 | VARCHAR(255) | NULLABLE | Street address |
| address_line_2 | VARCHAR(255) | NULLABLE | Suite/Unit |
| city | VARCHAR(255) | NULLABLE | City |
| state | VARCHAR(255) | NULLABLE | State/Province |
| postal_code | VARCHAR(255) | NULLABLE | Postal code |
| country | VARCHAR(255) | NULLABLE | Country |
| phone | VARCHAR(255) | NULLABLE | Phone number |
| timezone | VARCHAR(255) | DEFAULT 'UTC' | Timezone |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (is_active)
- INDEX (deleted_at)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

---

#### departments
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| location_id | BIGINT UNSIGNED | FK → locations.id | Parent location |
| name | VARCHAR(255) | NOT NULL | Department name |
| description | TEXT | NULLABLE | Description |
| color | VARCHAR(7) | DEFAULT '#6B7280' | Calendar color |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (location_id)
- INDEX (is_active)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE

---

#### business_roles
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| department_id | BIGINT UNSIGNED | FK → departments.id | Parent department |
| name | VARCHAR(255) | NOT NULL | Role name |
| description | TEXT | NULLABLE | Description |
| color | VARCHAR(7) | DEFAULT '#6B7280' | Calendar color |
| default_hourly_rate | DECIMAL(10,2) | NULLABLE | Default pay rate |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (department_id)
- INDEX (is_active)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- department_id → departments(id) ON DELETE CASCADE

---

#### user_role_assignments
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| system_role | VARCHAR(255) | NOT NULL | Enum value |
| location_id | BIGINT UNSIGNED | FK → locations.id, NULLABLE | Location scope |
| department_id | BIGINT UNSIGNED | FK → departments.id, NULLABLE | Department scope |
| assigned_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Assigner reference |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (system_role)
- INDEX (location_id)
- INDEX (department_id)
- UNIQUE (user_id, system_role, location_id, department_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE
- department_id → departments(id) ON DELETE CASCADE
- assigned_by → users(id) ON DELETE SET NULL

---

#### user_business_roles
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| business_role_id | BIGINT UNSIGNED | FK → business_roles.id | Role reference |
| hourly_rate | DECIMAL(10,2) | NULLABLE | Override hourly rate |
| is_primary | BOOLEAN | DEFAULT FALSE | Primary role flag |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, business_role_id)
- INDEX (user_id)
- INDEX (business_role_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- business_role_id → business_roles(id) ON DELETE CASCADE

---

#### user_employment_details
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id, UNIQUE | User reference |
| employment_start_date | DATE | NULLABLE | First day of employment |
| employment_end_date | DATE | NULLABLE | Contract end date (if fixed-term) |
| final_working_date | DATE | NULLABLE | Last working day (leaving date) |
| probation_end_date | DATE | NULLABLE | End of probation period |
| employment_status | VARCHAR(255) | DEFAULT 'active' | Status enum |
| pay_type | VARCHAR(255) | DEFAULT 'hourly' | 'hourly' or 'salaried' |
| base_hourly_rate | DECIMAL(10,2) | NULLABLE | Base hourly rate (if hourly) |
| annual_salary | DECIMAL(12,2) | NULLABLE | Annual salary (if salaried) |
| currency | VARCHAR(3) | DEFAULT 'GBP' | Currency code |
| target_hours_per_week | DECIMAL(5,2) | NULLABLE | Planned/target weekly hours |
| min_hours_per_week | DECIMAL(5,2) | NULLABLE | Minimum weekly hours |
| max_hours_per_week | DECIMAL(5,2) | NULLABLE | Maximum weekly hours |
| overtime_eligible | BOOLEAN | DEFAULT TRUE | Can earn overtime |
| notes | TEXT | NULLABLE | Private HR notes |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id)
- INDEX (employment_status)
- INDEX (employment_start_date)
- INDEX (final_working_date)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

**EmploymentStatus Enum Values:**
| Value | Description |
|-------|-------------|
| active | Currently employed and working |
| on_leave | On extended leave (maternity, etc.) |
| suspended | Temporarily suspended |
| notice_period | Working notice period |
| terminated | Employment ended |

**PayType Enum Values:**
| Value | Description |
|-------|-------------|
| hourly | Paid per hour worked |
| salaried | Fixed annual salary |

---

#### user_availability
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| type | VARCHAR(255) | NOT NULL | 'recurring' or 'specific_date' |
| day_of_week | TINYINT | NULLABLE | 0=Sunday, 1=Monday, etc. (for recurring) |
| specific_date | DATE | NULLABLE | Specific date (for one-off) |
| start_time | TIME | NULLABLE | Available from (null = all day) |
| end_time | TIME | NULLABLE | Available until (null = all day) |
| is_available | BOOLEAN | DEFAULT TRUE | Available or unavailable |
| preference_level | VARCHAR(255) | DEFAULT 'available' | Preference enum |
| notes | TEXT | NULLABLE | Notes/reason |
| effective_from | DATE | NULLABLE | When this availability starts |
| effective_until | DATE | NULLABLE | When this availability ends |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (type)
- INDEX (day_of_week)
- INDEX (specific_date)
- INDEX (user_id, type, day_of_week) - For recurring availability queries
- INDEX (user_id, specific_date) - For specific date queries

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

**AvailabilityType Enum Values:**
| Value | Description |
|-------|-------------|
| recurring | Repeats every week on day_of_week |
| specific_date | One-time availability/unavailability |

**PreferenceLevel Enum Values:**
| Value | Description |
|-------|-------------|
| preferred | Strongly prefers to work this time |
| available | Can work this time |
| if_needed | Would prefer not to, but can if necessary |
| unavailable | Cannot work this time |

**Example Availability Records:**
```
# Employee available Mon-Fri 9am-5pm
{ type: 'recurring', day_of_week: 1, start_time: '09:00', end_time: '17:00', is_available: true }
{ type: 'recurring', day_of_week: 2, start_time: '09:00', end_time: '17:00', is_available: true }
...

# Employee unavailable on specific date
{ type: 'specific_date', specific_date: '2026-02-14', is_available: false, notes: 'Doctors appointment' }

# Employee prefers not to work weekends
{ type: 'recurring', day_of_week: 0, is_available: true, preference_level: 'if_needed' }
{ type: 'recurring', day_of_week: 6, is_available: true, preference_level: 'if_needed' }
```

---

#### shifts
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| location_id | BIGINT UNSIGNED | FK → locations.id | Location |
| department_id | BIGINT UNSIGNED | FK → departments.id | Department |
| business_role_id | BIGINT UNSIGNED | FK → business_roles.id | Required role |
| user_id | BIGINT UNSIGNED | FK → users.id, NULLABLE | Assigned user |
| date | DATE | NOT NULL | Shift date |
| start_time | TIME | NOT NULL | Start time |
| end_time | TIME | NOT NULL | End time |
| break_duration_minutes | INTEGER | NULLABLE | Break duration |
| notes | TEXT | NULLABLE | Shift notes |
| status | VARCHAR(255) | DEFAULT 'draft' | Shift status (draft, published, etc.) |
| is_recurring | BOOLEAN | DEFAULT FALSE | Recurring flag |
| recurrence_rule | JSON | NULLABLE | Recurrence config |
| parent_shift_id | BIGINT UNSIGNED | FK → shifts.id, NULLABLE | Parent for recurring |
| created_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Creator |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (user_id)
- INDEX (date)
- INDEX (status)
- INDEX (location_id, department_id)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE
- department_id → departments(id) ON DELETE CASCADE
- business_role_id → business_roles(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE SET NULL
- parent_shift_id → shifts(id) ON DELETE SET NULL
- created_by → users(id) ON DELETE SET NULL

---

#### schedule_history
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| shift_id | BIGINT UNSIGNED | FK → shifts.id, NULLABLE | Related shift (nullable for deleted shifts) |
| user_id | BIGINT UNSIGNED | FK → users.id | User who made the change |
| action | VARCHAR(255) | NOT NULL | Action type (created, updated, deleted) |
| old_values | JSON | NULLABLE | Previous field values |
| new_values | JSON | NULLABLE | New field values |
| created_at | TIMESTAMP | NULLABLE | When the change occurred |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (shift_id)
- INDEX (user_id)
- INDEX (tenant_id, created_at)
- INDEX (tenant_id, shift_id)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- shift_id → shifts(id) ON DELETE SET NULL
- user_id → users(id) ON DELETE CASCADE

---

#### time_entries
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| shift_id | BIGINT UNSIGNED | FK → shifts.id, NULLABLE | Related shift |
| clock_in_at | TIMESTAMP | NOT NULL | Clock in time |
| clock_out_at | TIMESTAMP | NULLABLE | Clock out time |
| break_start_at | TIMESTAMP | NULLABLE | Break start |
| break_end_at | TIMESTAMP | NULLABLE | Break end |
| actual_break_minutes | SMALLINT | NULLABLE | Total break minutes taken |
| notes | TEXT | NULLABLE | Entry notes |
| clock_in_location | JSON | NULLABLE | GPS coordinates {lat, lng, accuracy} |
| clock_out_location | JSON | NULLABLE | GPS coordinates {lat, lng, accuracy} |
| status | VARCHAR(255) | DEFAULT 'clocked_in' | Entry status (see TimeEntryStatus enum) |
| approved_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Manager who approved/adjusted |
| approved_at | TIMESTAMP | NULLABLE | Approval timestamp |
| adjustment_reason | TEXT | NULLABLE | Reason for manual adjustment |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (user_id)
- INDEX (shift_id)
- INDEX (status)
- INDEX (clock_in_at)
- INDEX (tenant_id, user_id, clock_in_at) - For timesheet queries
- INDEX (tenant_id, status) - For finding active clock-ins

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE
- shift_id → shifts(id) ON DELETE SET NULL
- approved_by → users(id) ON DELETE SET NULL

**TimeEntryStatus Enum Values:**
| Value | Description |
|-------|-------------|
| clocked_in | Employee is currently clocked in |
| on_break | Employee is on break |
| clocked_out | Completed normally |
| auto_clocked_out | System auto-clocked out (e.g., end of day) |
| missed | No clock-in recorded for scheduled shift |
| adjusted | Manually adjusted by manager |
| pending_approval | Requires manager approval |
| approved | Approved by manager |

**Computed/Derived Fields (Application Layer):**

The following values are calculated at runtime or via database queries, not stored:

| Field | Calculation | Description |
|-------|-------------|-------------|
| scheduled_start | shift.date + shift.start_time | When the shift was scheduled to start |
| scheduled_end | shift.date + shift.end_time | When the shift was scheduled to end |
| scheduled_duration_minutes | shift.end_time - shift.start_time - shift.break_duration_minutes | Total scheduled work minutes |
| actual_duration_minutes | clock_out_at - clock_in_at - actual_break_minutes | Total actual work minutes |
| variance_minutes | actual_duration_minutes - scheduled_duration_minutes | Difference (positive = overtime) |
| clock_in_variance_minutes | clock_in_at - scheduled_start | Early (negative) or late (positive) arrival |
| clock_out_variance_minutes | clock_out_at - scheduled_end | Early (negative) or late (positive) departure |
| is_late | clock_in_variance_minutes > grace_period | Employee arrived late |
| is_early_departure | clock_out_variance_minutes < -grace_period | Employee left early |
| is_overtime | variance_minutes > 0 | Employee worked overtime |
| is_no_show | No time_entry for a published shift | Employee didn't clock in |

---

#### leave_types
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, NULLABLE | Tenant (null = system) |
| name | VARCHAR(255) | NOT NULL | Type name |
| color | VARCHAR(7) | DEFAULT '#6B7280' | Calendar color |
| requires_approval | BOOLEAN | DEFAULT TRUE | Needs approval |
| affects_allowance | BOOLEAN | DEFAULT TRUE | Deducts from balance |
| is_paid | BOOLEAN | DEFAULT TRUE | Paid leave |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (is_active)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

---

#### leave_allowances
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| leave_type_id | BIGINT UNSIGNED | FK → leave_types.id | Leave type |
| year | INTEGER | NOT NULL | Calendar year |
| total_days | DECIMAL(5,2) | DEFAULT 0 | Total allowance |
| used_days | DECIMAL(5,2) | DEFAULT 0 | Days used |
| carried_over_days | DECIMAL(5,2) | DEFAULT 0 | Carried from previous |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, leave_type_id, year)
- INDEX (tenant_id)
- INDEX (user_id)
- INDEX (year)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE
- leave_type_id → leave_types(id) ON DELETE CASCADE

---

#### leave_requests
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| user_id | BIGINT UNSIGNED | FK → users.id | Requester |
| leave_type_id | BIGINT UNSIGNED | FK → leave_types.id | Leave type |
| start_date | DATE | NOT NULL | Start date |
| end_date | DATE | NOT NULL | End date |
| start_half_day | BOOLEAN | DEFAULT FALSE | Half day start |
| end_half_day | BOOLEAN | DEFAULT FALSE | Half day end |
| total_days | DECIMAL(5,2) | NOT NULL | Calculated days |
| reason | TEXT | NULLABLE | Request reason |
| status | VARCHAR(255) | DEFAULT 'draft' | Request status |
| reviewed_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Reviewer |
| reviewed_at | TIMESTAMP | NULLABLE | Review time |
| review_notes | TEXT | NULLABLE | Review comments |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (user_id)
- INDEX (leave_type_id)
- INDEX (status)
- INDEX (start_date, end_date)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE
- leave_type_id → leave_types(id) ON DELETE CASCADE
- reviewed_by → users(id) ON DELETE SET NULL

---

#### shift_swap_requests
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| requesting_user_id | BIGINT UNSIGNED | FK → users.id | Requester |
| target_user_id | BIGINT UNSIGNED | FK → users.id | Target user |
| requesting_shift_id | BIGINT UNSIGNED | FK → shifts.id | Requester's shift |
| target_shift_id | BIGINT UNSIGNED | FK → shifts.id, NULLABLE | Target's shift |
| reason | TEXT | NULLABLE | Swap reason |
| status | VARCHAR(255) | DEFAULT 'pending' | Swap status |
| responded_at | TIMESTAMP | NULLABLE | Response time |
| approved_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Admin approver |
| approved_at | TIMESTAMP | NULLABLE | Approval time |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (requesting_user_id)
- INDEX (target_user_id)
- INDEX (status)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- requesting_user_id → users(id) ON DELETE CASCADE
- target_user_id → users(id) ON DELETE CASCADE
- requesting_shift_id → shifts(id) ON DELETE CASCADE
- target_shift_id → shifts(id) ON DELETE SET NULL
- approved_by → users(id) ON DELETE SET NULL

---

#### notification_preferences
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| notification_type | VARCHAR(255) | NOT NULL | Notification category |
| email_enabled | BOOLEAN | DEFAULT TRUE | Email notifications |
| push_enabled | BOOLEAN | DEFAULT TRUE | Push notifications |
| in_app_enabled | BOOLEAN | DEFAULT TRUE | In-app notifications |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, notification_type)
- INDEX (user_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

---

#### user_filter_defaults
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| filter_context | VARCHAR(255) | NOT NULL | Context identifier (e.g., 'schedule', 'users') |
| location_id | BIGINT UNSIGNED | FK → locations.id, NULLABLE | Default location filter |
| department_id | BIGINT UNSIGNED | FK → departments.id, NULLABLE | Default department filter |
| business_role_id | BIGINT UNSIGNED | FK → business_roles.id, NULLABLE | Default business role filter |
| additional_filters | JSON | NULLABLE | Additional filter settings |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, filter_context)
- INDEX (user_id)
- INDEX (location_id)
- INDEX (department_id)
- INDEX (business_role_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE SET NULL
- department_id → departments(id) ON DELETE SET NULL
- business_role_id → business_roles(id) ON DELETE SET NULL

---

#### tenant_settings
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, UNIQUE | Tenant reference |
| enable_clock_in_out | BOOLEAN | DEFAULT TRUE | Enable time tracking |
| enable_shift_acknowledgement | BOOLEAN | DEFAULT FALSE | Require shift acknowledgement |
| day_starts_at | TIME | DEFAULT '06:00:00' | Day view timeline start |
| day_ends_at | TIME | DEFAULT '22:00:00' | Day view timeline end |
| week_starts_on | TINYINT | DEFAULT 1 | Week start day (0=Sun, 1=Mon) |
| timezone | VARCHAR(255) | DEFAULT 'UTC' | Tenant timezone |
| date_format | VARCHAR(255) | DEFAULT 'Y-m-d' | Date display format |
| time_format | VARCHAR(255) | DEFAULT 'H:i' | Time display format |
| missed_grace_minutes | INTEGER | DEFAULT 15 | Grace period for missed shifts |
| notify_on_publish | BOOLEAN | DEFAULT TRUE | Send notifications on publish |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (tenant_id)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

---

#### staffing_requirements
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| location_id | BIGINT UNSIGNED | FK → locations.id, NULLABLE | Location scope (null = all) |
| department_id | BIGINT UNSIGNED | FK → departments.id, NULLABLE | Department scope (null = all) |
| business_role_id | BIGINT UNSIGNED | FK → business_roles.id | Role this requirement applies to |
| day_of_week | TINYINT | NOT NULL | 0=Sunday, 1=Monday, etc. |
| start_time | TIME | NOT NULL | Time window start |
| end_time | TIME | NOT NULL | Time window end |
| min_employees | INTEGER | DEFAULT 0 | Minimum required employees |
| max_employees | INTEGER | NULLABLE | Maximum allowed employees (null = no limit) |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| notes | TEXT | NULLABLE | Notes/description |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (location_id)
- INDEX (department_id)
- INDEX (business_role_id)
- INDEX (day_of_week)
- INDEX (is_active)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE
- department_id → departments(id) ON DELETE CASCADE
- business_role_id → business_roles(id) ON DELETE CASCADE

---

### 1.3 Premium Feature Tables

#### location_geofences *(Premium: advanced_geofencing)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| location_id | BIGINT UNSIGNED | FK → locations.id, UNIQUE | Location reference |
| latitude | DECIMAL(10,8) | NOT NULL | Center latitude |
| longitude | DECIMAL(11,8) | NOT NULL | Center longitude |
| radius_meters | INTEGER | DEFAULT 200 | Geofence radius (100-1000) |
| enforce_clock_in | BOOLEAN | DEFAULT FALSE | Block clock-in outside fence |
| auto_clock_in | BOOLEAN | DEFAULT FALSE | Auto clock-in on entry |
| auto_clock_out | BOOLEAN | DEFAULT FALSE | Auto clock-out on exit |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (location_id)

**Foreign Keys:**
- location_id → locations(id) ON DELETE CASCADE

---

#### geofence_events *(Premium: advanced_geofencing)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| location_id | BIGINT UNSIGNED | FK → locations.id | Location reference |
| event_type | VARCHAR(255) | NOT NULL | 'enter', 'exit', 'violation' |
| latitude | DECIMAL(10,8) | NOT NULL | Event latitude |
| longitude | DECIMAL(11,8) | NOT NULL | Event longitude |
| distance_meters | INTEGER | NULLABLE | Distance from geofence center |
| created_at | TIMESTAMP | NULLABLE | Event timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (user_id)
- INDEX (location_id)
- INDEX (event_type)
- INDEX (created_at)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE

---

#### labor_forecasts *(Premium: labor_forecasting)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| location_id | BIGINT UNSIGNED | FK → locations.id | Location reference |
| department_id | BIGINT UNSIGNED | FK → departments.id, NULLABLE | Department scope |
| business_role_id | BIGINT UNSIGNED | FK → business_roles.id | Role reference |
| forecast_date | DATE | NOT NULL | Date of forecast |
| hour | TINYINT | NOT NULL | Hour (0-23) |
| predicted_demand | DECIMAL(5,2) | NOT NULL | Predicted employees needed |
| confidence_score | DECIMAL(3,2) | NULLABLE | Prediction confidence (0-1) |
| factors | JSON | NULLABLE | Factors used in prediction |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (tenant_id, location_id, department_id, business_role_id, forecast_date, hour)
- INDEX (tenant_id)
- INDEX (location_id)
- INDEX (forecast_date)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE
- department_id → departments(id) ON DELETE CASCADE
- business_role_id → business_roles(id) ON DELETE CASCADE

---

#### payroll_integrations *(Premium: payroll_integrations)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, UNIQUE | Tenant reference |
| provider | VARCHAR(255) | NOT NULL | 'adp', 'paychex', 'gusto', 'quickbooks', 'xero' |
| is_active | BOOLEAN | DEFAULT TRUE | Integration active |
| credentials | TEXT | NULLABLE | Encrypted credentials/tokens |
| settings | JSON | NULLABLE | Provider-specific settings |
| last_sync_at | TIMESTAMP | NULLABLE | Last successful sync |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (tenant_id)
- INDEX (provider)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

---

#### payroll_exports *(Premium: payroll_integrations)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| payroll_integration_id | BIGINT UNSIGNED | FK → payroll_integrations.id | Integration reference |
| period_start | DATE | NOT NULL | Pay period start |
| period_end | DATE | NOT NULL | Pay period end |
| status | VARCHAR(255) | DEFAULT 'pending' | 'pending', 'processing', 'completed', 'failed' |
| employee_count | INTEGER | NULLABLE | Number of employees exported |
| total_hours | DECIMAL(10,2) | NULLABLE | Total hours in export |
| export_data | JSON | NULLABLE | Exported data snapshot |
| error_message | TEXT | NULLABLE | Error details if failed |
| exported_by | BIGINT UNSIGNED | FK → users.id | User who initiated |
| exported_at | TIMESTAMP | NULLABLE | Export completion time |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (payroll_integration_id)
- INDEX (status)
- INDEX (period_start, period_end)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- payroll_integration_id → payroll_integrations(id) ON DELETE CASCADE
- exported_by → users(id) ON DELETE SET NULL

---

#### announcements *(Premium: team_messaging)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| author_id | BIGINT UNSIGNED | FK → users.id | Author reference |
| title | VARCHAR(255) | NOT NULL | Announcement title |
| content | TEXT | NOT NULL | Announcement body |
| priority | VARCHAR(255) | DEFAULT 'normal' | 'low', 'normal', 'high', 'urgent' |
| scope_type | VARCHAR(255) | DEFAULT 'organization' | 'organization', 'location', 'department' |
| scope_id | BIGINT UNSIGNED | NULLABLE | Location/department ID if scoped |
| requires_acknowledgment | BOOLEAN | DEFAULT FALSE | Require read confirmation |
| published_at | TIMESTAMP | NULLABLE | When published |
| expires_at | TIMESTAMP | NULLABLE | When to hide |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (author_id)
- INDEX (scope_type, scope_id)
- INDEX (published_at)
- INDEX (expires_at)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- author_id → users(id) ON DELETE CASCADE

---

#### announcement_reads *(Premium: team_messaging)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| announcement_id | BIGINT UNSIGNED | FK → announcements.id | Announcement reference |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| read_at | TIMESTAMP | NOT NULL | When read |
| acknowledged_at | TIMESTAMP | NULLABLE | When acknowledged (if required) |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (announcement_id, user_id)
- INDEX (announcement_id)
- INDEX (user_id)

**Foreign Keys:**
- announcement_id → announcements(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE

---

#### direct_messages *(Premium: team_messaging)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| sender_id | BIGINT UNSIGNED | FK → users.id | Sender reference |
| recipient_id | BIGINT UNSIGNED | FK → users.id | Recipient reference |
| content | TEXT | NOT NULL | Message content |
| read_at | TIMESTAMP | NULLABLE | When read by recipient |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (sender_id)
- INDEX (recipient_id)
- INDEX (created_at)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- sender_id → users(id) ON DELETE CASCADE
- recipient_id → users(id) ON DELETE CASCADE

---

#### employee_documents *(Premium: document_management)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| user_id | BIGINT UNSIGNED | FK → users.id | Employee reference |
| uploaded_by | BIGINT UNSIGNED | FK → users.id | Uploader reference |
| document_type | VARCHAR(255) | NOT NULL | Type of document |
| title | VARCHAR(255) | NOT NULL | Document title |
| file_path | VARCHAR(255) | NOT NULL | Storage path |
| file_name | VARCHAR(255) | NOT NULL | Original filename |
| file_size | INTEGER | NOT NULL | Size in bytes |
| mime_type | VARCHAR(255) | NOT NULL | MIME type |
| notes | TEXT | NULLABLE | Additional notes |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (user_id)
- INDEX (document_type)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE
- uploaded_by → users(id) ON DELETE SET NULL

**DocumentType Enum Values:**
| Value | Description |
|-------|-------------|
| contract | Employment contract |
| id_document | ID or passport |
| right_to_work | Right to work documentation |
| certification | Professional certification |
| training | Training certificate |
| other | Other document |

---

#### certifications *(Premium: document_management)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| name | VARCHAR(255) | NOT NULL | Certification name |
| description | TEXT | NULLABLE | Description |
| validity_period_months | INTEGER | NULLABLE | How long cert is valid |
| is_required | BOOLEAN | DEFAULT FALSE | Required for employment |
| reminder_days_before | INTEGER | DEFAULT 30 | Days before expiry to alert |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (is_required)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

---

#### user_certifications *(Premium: document_management)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id | User reference |
| certification_id | BIGINT UNSIGNED | FK → certifications.id | Certification reference |
| document_id | BIGINT UNSIGNED | FK → employee_documents.id, NULLABLE | Supporting document |
| issued_date | DATE | NULLABLE | When issued |
| expiry_date | DATE | NULLABLE | When expires |
| certificate_number | VARCHAR(255) | NULLABLE | Certificate/license number |
| status | VARCHAR(255) | DEFAULT 'active' | 'active', 'expired', 'revoked' |
| notes | TEXT | NULLABLE | Additional notes |
| verified_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Admin who verified |
| verified_at | TIMESTAMP | NULLABLE | Verification timestamp |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, certification_id)
- INDEX (user_id)
- INDEX (certification_id)
- INDEX (expiry_date)
- INDEX (status)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- certification_id → certifications(id) ON DELETE CASCADE
- document_id → employee_documents(id) ON DELETE SET NULL
- verified_by → users(id) ON DELETE SET NULL

---

#### role_required_certifications *(Premium: document_management)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| business_role_id | BIGINT UNSIGNED | FK → business_roles.id | Role reference |
| certification_id | BIGINT UNSIGNED | FK → certifications.id | Certification reference |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (business_role_id, certification_id)

**Foreign Keys:**
- business_role_id → business_roles(id) ON DELETE CASCADE
- certification_id → certifications(id) ON DELETE CASCADE

---

#### tenant_branding *(Enterprise: custom_branding)*
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, UNIQUE | Tenant reference |
| primary_color | VARCHAR(7) | DEFAULT '#6366F1' | Primary brand color |
| secondary_color | VARCHAR(7) | DEFAULT '#8B5CF6' | Secondary brand color |
| logo_path | VARCHAR(255) | NULLABLE | Custom logo path |
| favicon_path | VARCHAR(255) | NULLABLE | Custom favicon path |
| login_background_path | VARCHAR(255) | NULLABLE | Login page background |
| hide_plannrly_branding | BOOLEAN | DEFAULT FALSE | Remove "Powered by" |
| custom_css | TEXT | NULLABLE | Custom CSS overrides |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (tenant_id)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

---

#### labor_budgets
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| location_id | BIGINT UNSIGNED | FK → locations.id | Location reference |
| department_id | BIGINT UNSIGNED | FK → departments.id, NULLABLE | Optional department scope |
| period_type | VARCHAR(255) | NOT NULL | 'weekly', 'bi_weekly', 'monthly' |
| budget_amount | DECIMAL(10,2) | NOT NULL | Budget in currency |
| currency | VARCHAR(3) | DEFAULT 'USD' | Currency code |
| warning_threshold | INTEGER | DEFAULT 80 | % threshold for warning (e.g., 80) |
| critical_threshold | INTEGER | DEFAULT 95 | % threshold for critical (e.g., 95) |
| effective_from | DATE | NOT NULL | Budget start date |
| effective_to | DATE | NULLABLE | Budget end date (null = ongoing) |
| is_active | BOOLEAN | DEFAULT TRUE | Budget is active |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (location_id)
- INDEX (department_id)
- INDEX (effective_from, effective_to)
- INDEX (is_active)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE
- department_id → departments(id) ON DELETE CASCADE

---

#### labor_budget_snapshots
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| labor_budget_id | BIGINT UNSIGNED | FK → labor_budgets.id | Budget reference |
| period_start | DATE | NOT NULL | Period start date |
| period_end | DATE | NOT NULL | Period end date |
| budget_amount | DECIMAL(10,2) | NOT NULL | Budget for this period |
| scheduled_amount | DECIMAL(10,2) | DEFAULT 0 | Total scheduled labor cost |
| actual_amount | DECIMAL(10,2) | DEFAULT 0 | Total actual labor cost |
| scheduled_hours | DECIMAL(8,2) | DEFAULT 0 | Total scheduled hours |
| actual_hours | DECIMAL(8,2) | DEFAULT 0 | Total actual hours |
| status | VARCHAR(255) | DEFAULT 'on_track' | 'on_track', 'warning', 'critical', 'over_budget' |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (labor_budget_id, period_start)
- INDEX (labor_budget_id)
- INDEX (period_start, period_end)
- INDEX (status)

**Foreign Keys:**
- labor_budget_id → labor_budgets(id) ON DELETE CASCADE

---

#### kiosks
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| location_id | BIGINT UNSIGNED | FK → locations.id | Location reference |
| name | VARCHAR(255) | NOT NULL | Kiosk name (e.g., "Front Entrance") |
| device_identifier | VARCHAR(255) | NULLABLE | Device ID/serial |
| auth_methods | JSON | NOT NULL | ['pin', 'badge', 'qr', 'photo'] |
| require_photo | BOOLEAN | DEFAULT FALSE | Require photo on clock |
| session_timeout_seconds | INTEGER | DEFAULT 30 | Auto-logout time |
| is_active | BOOLEAN | DEFAULT TRUE | Kiosk is active |
| is_locked | BOOLEAN | DEFAULT FALSE | Remotely locked |
| last_activity_at | TIMESTAMP | NULLABLE | Last usage timestamp |
| access_token | VARCHAR(255) | NULLABLE | Kiosk session token |
| access_token_expires_at | TIMESTAMP | NULLABLE | Token expiry |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (location_id)
- INDEX (is_active)
- UNIQUE (access_token)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE CASCADE

---

#### employee_pins
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id, UNIQUE | User reference |
| pin_hash | VARCHAR(255) | NOT NULL | Hashed PIN (bcrypt) |
| failed_attempts | INTEGER | DEFAULT 0 | Consecutive failures |
| locked_until | TIMESTAMP | NULLABLE | Lock expiry after failures |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

---

#### kiosk_events
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| kiosk_id | BIGINT UNSIGNED | FK → kiosks.id | Kiosk reference |
| user_id | BIGINT UNSIGNED | FK → users.id, NULLABLE | User (null if auth failed) |
| event_type | VARCHAR(255) | NOT NULL | 'clock_in', 'clock_out', 'break_start', 'break_end', 'auth_failed' |
| auth_method | VARCHAR(255) | NULLABLE | 'pin', 'badge', 'qr', 'manager_override' |
| time_entry_id | BIGINT UNSIGNED | FK → time_entries.id, NULLABLE | Related time entry |
| photo_path | VARCHAR(255) | NULLABLE | Photo capture path |
| metadata | JSON | NULLABLE | Additional event data |
| created_at | TIMESTAMP | NULLABLE | Event timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (kiosk_id)
- INDEX (user_id)
- INDEX (event_type)
- INDEX (created_at)

**Foreign Keys:**
- kiosk_id → kiosks(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE SET NULL
- time_entry_id → time_entries(id) ON DELETE SET NULL

---

## 2. Model Relationships

### 2.1 Tenant Model

```php
class Tenant extends Model
{
    // Has One
    public function tenantSettings(): HasOne
    public function subscription(): HasOne  // → tenant_subscriptions

    // Has Many
    public function users(): HasMany
    public function locations(): HasMany
    public function departments(): HasMany
    public function businessRoles(): HasMany
    public function shifts(): HasMany
    public function leaveTypes(): HasMany
    public function leaveRequests(): HasMany
    public function featureAddons(): HasMany  // → tenant_feature_addons

    // Feature Check Methods
    public function hasFeature(string $feature): bool
    {
        // Check if feature is included in plan
        if ($this->subscription?->planIncludesFeature($feature)) {
            return true;
        }

        // Check if feature is enabled as add-on
        return $this->featureAddons()
            ->where('feature', $feature)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function hasAIScheduling(): bool
    {
        return $this->hasFeature('ai_scheduling');
    }

    public function isSubscriptionActive(): bool
    {
        return $this->subscription?->isActive() ?? false;
    }
}
```

### 2.1.1 TenantSubscription Model

```php
class TenantSubscription extends Model
{
    // Casts
    protected function casts(): array
    {
        return [
            'plan' => SubscriptionPlan::class,
            'status' => SubscriptionStatus::class,
            'billing_cycle' => BillingCycle::class,
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // Belongs To
    public function tenant(): BelongsTo

    // Methods
    public function isActive(): bool
    {
        return in_array($this->status, [
            SubscriptionStatus::Active,
            SubscriptionStatus::Trialing,
        ]);
    }

    public function isInGracePeriod(): bool
    {
        return $this->status === SubscriptionStatus::PastDue;
    }

    public function planIncludesFeature(string $feature): bool
    {
        return match($this->plan) {
            SubscriptionPlan::Enterprise => true, // All features
            SubscriptionPlan::Professional => in_array($feature, [
                'ai_scheduling',
                'advanced_analytics',
            ]),
            SubscriptionPlan::Basic => false, // No premium features
            default => false,
        };
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }
}
```

### 2.1.2 TenantFeatureAddon Model

```php
class TenantFeatureAddon extends Model
{
    // Casts
    protected function casts(): array
    {
        return [
            'feature' => FeatureAddon::class,
            'enabled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // Belongs To
    public function tenant(): BelongsTo

    // Methods
    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForFeature(Builder $query, string $feature): Builder
    {
        return $query->where('feature', $feature);
    }
}
```

**Subscription Enums:**

```php
enum SubscriptionPlan: string
{
    case Basic = 'basic';
    case Professional = 'professional';
    case Enterprise = 'enterprise';
}

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
}

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}

enum FeatureAddon: string
{
    case AIScheduling = 'ai_scheduling';
    case AdvancedAnalytics = 'advanced_analytics';
    case ApiAccess = 'api_access';
    case PrioritySupport = 'priority_support';
    case TimeAttendance = 'time_attendance';  // $12/mo addon for Professional, included in Enterprise
}
```

### 2.2 User Model

```php
class User extends Authenticatable
{
    use BelongsToTenant, SoftDeletes;

    // Belongs To
    public function tenant(): BelongsTo

    // Has One
    public function employmentDetails(): HasOne  // → user_employment_details

    // Has Many
    public function roleAssignments(): HasMany
    public function businessRoles(): BelongsToMany
    public function userBusinessRoles(): HasMany
    public function shifts(): HasMany
    public function timeEntries(): HasMany
    public function leaveRequests(): HasMany
    public function leaveAllowances(): HasMany
    public function notificationPreferences(): HasMany
    public function filterDefaults(): HasMany
    public function availability(): HasMany  // → user_availability

    // Accessors
    public function getFullNameAttribute(): string
    public function getInitialsAttribute(): string
    public function getTargetHoursAttribute(): ?float  // From employmentDetails
    public function getHourlyRateAttribute(): ?float   // Effective hourly rate

    // Role Checking Methods
    public function isSuperAdmin(): bool
    public function isAdmin(): bool
    public function isLocationAdmin(?int $locationId = null): bool
    public function isDepartmentAdmin(?int $departmentId = null): bool
    public function isEmployee(): bool
    public function getHighestRole(): ?SystemRole
    public function canManageLocation(Location $location): bool
    public function canManageDepartment(Department $department): bool

    // Availability Methods
    public function isAvailableOn(Carbon $date): bool
    public function isAvailableForShift(Shift $shift): bool
    public function getAvailabilityForWeek(Carbon $weekStart): Collection

    // Scopes
    public function scopeActive(Builder $query): Builder
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    public function scopeAvailableForShift(Builder $query, Shift $shift): Builder
    public function scopeWithRole(Builder $query, int $businessRoleId): Builder
}
```

**Important Note on Departments:**
Users do NOT have a direct `departments` relationship. To access a user's departments, use the business roles relationship:

```php
// Eager load departments through business roles
$user->load(['businessRoles.department']);

// Get departments collection
$departments = $user->businessRoles->map(fn($role) => $role->department)->filter()->unique('id');

// Get primary role's department
$primaryRole = $user->businessRoles->firstWhere('pivot.is_primary', true);
$department = $primaryRole?->department;
```

### 2.3 Location Model

```php
class Location extends Model
{
    use BelongsToTenant, SoftDeletes;

    // Belongs To
    public function tenant(): BelongsTo

    // Has Many
    public function departments(): HasMany
    public function shifts(): HasMany

    // Accessors
    public function getFullAddressAttribute(): string
}
```

### 2.4 Department Model

```php
class Department extends Model
{
    use BelongsToTenant, SoftDeletes;

    // Belongs To
    public function tenant(): BelongsTo
    public function location(): BelongsTo

    // Has Many
    public function businessRoles(): HasMany
    public function shifts(): HasMany
}
```

### 2.5 BusinessRole Model

```php
class BusinessRole extends Model
{
    use BelongsToTenant, SoftDeletes;

    // Belongs To
    public function tenant(): BelongsTo
    public function department(): BelongsTo

    // Has Many
    public function userBusinessRoles(): HasMany
    public function shifts(): HasMany

    // Belongs To Many
    public function users(): BelongsToMany
}
```

### 2.6 Shift Model

```php
class Shift extends Model
{
    use BelongsToTenant, SoftDeletes;

    // Casts
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'status' => ShiftStatus::class,
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'array',
        ];
    }

    // Belongs To
    public function tenant(): BelongsTo
    public function location(): BelongsTo
    public function department(): BelongsTo
    public function businessRole(): BelongsTo
    public function user(): BelongsTo
    public function parentShift(): BelongsTo
    public function createdBy(): BelongsTo

    // Has Many
    public function childShifts(): HasMany
    public function timeEntries(): HasMany
    public function swapRequests(): HasMany

    // Accessors
    public function getDurationHoursAttribute(): float
    public function getIsAssignedAttribute(): bool

    // Scopes
    public function scopeDraft(Builder $query): Builder      // Where status = draft
    public function scopePublished(Builder $query): Builder  // Where status = published
    public function scopeVisibleToUser(Builder $query, User $user): Builder
    // Employees see only published+ shifts; admins see all
}
```

### 2.7 TenantSettings Model

```php
class TenantSettings extends Model
{
    use HasFactory;

    // Casts
    protected function casts(): array
    {
        return [
            'enable_clock_in_out' => 'boolean',
            'enable_shift_acknowledgement' => 'boolean',
            'day_starts_at' => 'datetime:H:i:s',
            'day_ends_at' => 'datetime:H:i:s',
            'week_starts_on' => 'integer',
            'missed_grace_minutes' => 'integer',
            'notify_on_publish' => 'boolean',
        ];
    }

    // Belongs To
    public function tenant(): BelongsTo
}
```

**TenantSettings Fields:**
| Field | Type | Description |
|-------|------|-------------|
| enable_clock_in_out | boolean | Enable time tracking features |
| enable_shift_acknowledgement | boolean | Require employees to acknowledge shifts |
| day_starts_at | time | Day view timeline start (default: 06:00) |
| day_ends_at | time | Day view timeline end (default: 22:00) |
| week_starts_on | integer | Day of week (0=Sunday, 1=Monday) |
| timezone | string | Default timezone for tenant |
| date_format | string | Date display format |
| time_format | string | Time display format |
| missed_grace_minutes | integer | Minutes after shift start before marking missed |
| notify_on_publish | boolean | Send notifications when shifts are published |
| clock_in_grace_minutes | integer | Minutes before/after shift start allowed for clock-in (default: 15) |
| require_gps_clock_in | boolean | Require GPS location when clocking in (default: false) |
| auto_clock_out_enabled | boolean | Automatically clock out at end of day (default: false) |
| auto_clock_out_time | time | Time to auto-clock out if enabled (default: 23:59) |
| overtime_threshold_minutes | integer | Minutes beyond scheduled before overtime (default: 0) |
| require_manager_approval | boolean | Require manager approval for time entries (default: false) |

### 2.8 TimeEntry Model

```php
class TimeEntry extends Model
{
    use BelongsToTenant;

    // Casts
    protected function casts(): array
    {
        return [
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'break_start_at' => 'datetime',
            'break_end_at' => 'datetime',
            'actual_break_minutes' => 'integer',
            'clock_in_location' => 'array',
            'clock_out_location' => 'array',
            'status' => TimeEntryStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    // Belongs To
    public function tenant(): BelongsTo
    public function user(): BelongsTo
    public function shift(): BelongsTo
    public function approvedBy(): BelongsTo

    // Accessors (Computed Fields)
    public function getActualDurationMinutesAttribute(): ?int
    public function getScheduledDurationMinutesAttribute(): ?int
    public function getVarianceMinutesAttribute(): ?int
    public function getClockInVarianceMinutesAttribute(): ?int
    public function getClockOutVarianceMinutesAttribute(): ?int
    public function getIsLateAttribute(): bool
    public function getIsEarlyDepartureAttribute(): bool
    public function getIsOvertimeAttribute(): bool

    // Methods
    public function clockIn(?array $location = null): void
    public function clockOut(?array $location = null): void
    public function startBreak(): void
    public function endBreak(): void
    public function approve(User $manager): void
    public function adjust(User $manager, array $data, string $reason): void

    // Scopes
    public function scopeActive(Builder $query): Builder  // Currently clocked in
    public function scopeOnBreak(Builder $query): Builder
    public function scopeCompleted(Builder $query): Builder
    public function scopeForDate(Builder $query, Carbon $date): Builder
    public function scopeForDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    public function scopePendingApproval(Builder $query): Builder
}
```

**Clock In/Out Business Logic:**

```php
// Clock In Validation
public function clockIn(?array $location = null): void
{
    // Validate not already clocked in
    if ($this->status === TimeEntryStatus::ClockedIn) {
        throw new ClockInException('Already clocked in');
    }

    // Validate within grace period if shift assigned
    if ($this->shift) {
        $shiftStart = $this->shift->getStartDateTime();
        $gracePeriod = $this->tenant->tenantSettings->clock_in_grace_minutes ?? 15;
        $earliestClockIn = $shiftStart->subMinutes($gracePeriod);

        if (now()->lt($earliestClockIn)) {
            throw new ClockInException('Too early to clock in');
        }
    }

    // Validate GPS if required
    if ($this->tenant->tenantSettings->require_gps_clock_in && !$location) {
        throw new ClockInException('GPS location required');
    }

    $this->update([
        'clock_in_at' => now(),
        'clock_in_location' => $location,
        'status' => TimeEntryStatus::ClockedIn,
    ]);
}

// Clock Out Logic
public function clockOut(?array $location = null): void
{
    if ($this->status !== TimeEntryStatus::ClockedIn) {
        throw new ClockOutException('Not currently clocked in');
    }

    $this->update([
        'clock_out_at' => now(),
        'clock_out_location' => $location,
        'status' => TimeEntryStatus::ClockedOut,
    ]);
}
```

**Scheduled vs Actual Variance Calculation:**

```php
// Get variance in minutes (positive = overtime, negative = undertime)
public function getVarianceMinutesAttribute(): ?int
{
    if (!$this->clock_out_at || !$this->shift) {
        return null;
    }

    return $this->actual_duration_minutes - $this->scheduled_duration_minutes;
}

// Get actual worked minutes (excluding breaks)
public function getActualDurationMinutesAttribute(): ?int
{
    if (!$this->clock_out_at) {
        return null;
    }

    $totalMinutes = $this->clock_in_at->diffInMinutes($this->clock_out_at);
    $breakMinutes = $this->actual_break_minutes ?? 0;

    return $totalMinutes - $breakMinutes;
}

// Get scheduled work minutes from shift
public function getScheduledDurationMinutesAttribute(): ?int
{
    if (!$this->shift) {
        return null;
    }

    $startTime = Carbon::parse($this->shift->start_time);
    $endTime = Carbon::parse($this->shift->end_time);
    $scheduledMinutes = $startTime->diffInMinutes($endTime);
    $breakMinutes = $this->shift->break_duration_minutes ?? 0;

    return $scheduledMinutes - $breakMinutes;
}

// Check if employee was late
public function getIsLateAttribute(): bool
{
    if (!$this->shift || !$this->clock_in_at) {
        return false;
    }

    $gracePeriod = $this->tenant->tenantSettings->missed_grace_minutes ?? 15;
    $shiftStart = $this->shift->getStartDateTime();

    return $this->clock_in_at->gt($shiftStart->addMinutes($gracePeriod));
}
```

### 2.8.1 Missed Shift Detection Command

Automatically detects shifts where employees failed to clock in and creates missed time entries.

```php
// app/Console/Commands/DetectMissedShiftsCommand.php
class DetectMissedShiftsCommand extends Command
{
    protected $signature = 'attendance:detect-missed-shifts';
    protected $description = 'Detect shifts where employees failed to clock in';

    public function handle(): int
    {
        // Process each tenant with clock-in enabled
        Tenant::whereHas('tenantSettings', function ($q) {
            $q->where('enable_clock_in_out', true);
        })->each(function ($tenant) {
            $this->processTenant($tenant);
        });

        return Command::SUCCESS;
    }

    private function processTenant(Tenant $tenant): void
    {
        $gracePeriod = $tenant->tenantSettings->missed_grace_minutes ?? 15;
        $cutoffTime = now()->subMinutes($gracePeriod);

        // Find published shifts that have started, assigned to users, with no time entry
        $missedShifts = Shift::where('tenant_id', $tenant->id)
            ->where('status', ShiftStatus::Published)
            ->whereNotNull('user_id')
            ->where('date', '<=', today())
            ->whereRaw("CONCAT(date, ' ', start_time) <= ?", [$cutoffTime])
            ->whereDoesntHave('timeEntries')
            ->get();

        foreach ($missedShifts as $shift) {
            $this->createMissedEntry($shift);
            $this->notifyManagers($shift);
        }
    }
}
```

**Scheduler Registration (routes/console.php):**
```php
Schedule::command('attendance:detect-missed-shifts')->everyFifteenMinutes();
```

**MissedShiftNotification:**
```php
// app/Notifications/MissedShiftNotification.php
class MissedShiftNotification extends Notification
{
    use Queueable;

    public function __construct(public Shift $shift) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Missed Shift Alert: ' . $this->shift->user->full_name)
            ->line("{$this->shift->user->full_name} did not clock in for their shift.")
            ->line("Date: {$this->shift->date->format('D, M j, Y')}")
            ->line("Time: {$this->shift->formatted_time}")
            ->action('View Timesheets', route('timesheets.index'));
    }
}
```

### 2.9 LeaveRequest Model

```php
class LeaveRequest extends Model
{
    use BelongsToTenant;

    // Casts
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'start_half_day' => 'boolean',
            'end_half_day' => 'boolean',
            'total_days' => 'decimal:2',
            'status' => LeaveRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    // Belongs To
    public function tenant(): BelongsTo
    public function user(): BelongsTo
    public function leaveType(): BelongsTo
    public function reviewer(): BelongsTo

    // Methods
    public function calculateTotalDays(): float
    public function approve(User $reviewer, ?string $notes = null): void
    public function reject(User $reviewer, ?string $notes = null): void
}
```

### 2.9 UserFilterDefault Model

```php
class UserFilterDefault extends Model
{
    // Casts
    protected function casts(): array
    {
        return [
            'additional_filters' => 'array',
        ];
    }

    // Belongs To
    public function user(): BelongsTo
    public function location(): BelongsTo
    public function department(): BelongsTo
    public function businessRole(): BelongsTo

    // Helper method for retrieving values from additional_filters
    public function getFilter(string $key, $default = null): mixed
    {
        return $this->additional_filters[$key] ?? $default;
    }
}
```

**Additional Filters JSON Structure:**
```json
{
  "group_by": "department"  // or "role"
}
```

### 2.10 UserEmploymentDetails Model

```php
class UserEmploymentDetails extends Model
{
    // Casts
    protected function casts(): array
    {
        return [
            'employment_start_date' => 'date',
            'employment_end_date' => 'date',
            'final_working_date' => 'date',
            'probation_end_date' => 'date',
            'employment_status' => EmploymentStatus::class,
            'pay_type' => PayType::class,
            'base_hourly_rate' => 'decimal:2',
            'annual_salary' => 'decimal:2',
            'target_hours_per_week' => 'decimal:2',
            'min_hours_per_week' => 'decimal:2',
            'max_hours_per_week' => 'decimal:2',
            'overtime_eligible' => 'boolean',
        ];
    }

    // Belongs To
    public function user(): BelongsTo

    // Accessors
    public function getEffectiveHourlyRateAttribute(): ?float
    {
        if ($this->pay_type === PayType::Hourly) {
            return $this->base_hourly_rate;
        }
        // Convert salary to hourly (assuming 52 weeks, target hours)
        if ($this->annual_salary && $this->target_hours_per_week) {
            return $this->annual_salary / 52 / $this->target_hours_per_week;
        }
        return null;
    }

    public function getIsOnProbationAttribute(): bool
    {
        return $this->probation_end_date && $this->probation_end_date->isFuture();
    }

    public function getIsLeavingAttribute(): bool
    {
        return $this->final_working_date !== null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->employment_status === EmploymentStatus::Active;
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    public function scopeOnNotice(Builder $query): Builder
    public function scopeLeavingBefore(Builder $query, Carbon $date): Builder
}
```

**Pay Calculation for Shifts:**
```php
// Get hourly rate for a specific role
public function getHourlyRateForRole(BusinessRole $role): float
{
    // Check if user has a custom rate for this role
    $userBusinessRole = $this->user->userBusinessRoles()
        ->where('business_role_id', $role->id)
        ->first();

    if ($userBusinessRole?->hourly_rate) {
        return $userBusinessRole->hourly_rate;
    }

    // Fall back to user's base hourly rate
    if ($this->base_hourly_rate) {
        return $this->base_hourly_rate;
    }

    // Fall back to role's default rate
    return $role->default_hourly_rate ?? 0;
}
```

### 2.11 UserAvailability Model

```php
class UserAvailability extends Model
{
    // Casts
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'specific_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_available' => 'boolean',
            'type' => AvailabilityType::class,
            'preference_level' => PreferenceLevel::class,
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    // Belongs To
    public function user(): BelongsTo

    // Scopes
    public function scopeRecurring(Builder $query): Builder
    public function scopeSpecificDate(Builder $query): Builder
    public function scopeForDate(Builder $query, Carbon $date): Builder
    public function scopeAvailable(Builder $query): Builder
    public function scopeUnavailable(Builder $query): Builder
    public function scopeEffectiveOn(Builder $query, Carbon $date): Builder

    // Methods
    public function isEffectiveOn(Carbon $date): bool
    {
        if ($this->effective_from && $date->lt($this->effective_from)) {
            return false;
        }
        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }
        return true;
    }

    public function coversTime(Carbon $time): bool
    {
        if (!$this->start_time && !$this->end_time) {
            return true; // All day
        }
        $timeOfDay = $time->format('H:i:s');
        $start = $this->start_time?->format('H:i:s') ?? '00:00:00';
        $end = $this->end_time?->format('H:i:s') ?? '23:59:59';
        return $timeOfDay >= $start && $timeOfDay <= $end;
    }
}
```

**Availability Checking Service:**
```php
class AvailabilityService
{
    /**
     * Check if user is available for a shift
     */
    public function isUserAvailableForShift(User $user, Shift $shift): bool
    {
        $date = $shift->date;
        $dayOfWeek = $date->dayOfWeek;

        // Check specific date unavailability first
        $specificUnavailable = $user->availability()
            ->specificDate()
            ->where('specific_date', $date)
            ->where('is_available', false)
            ->effectiveOn($date)
            ->exists();

        if ($specificUnavailable) {
            return false;
        }

        // Check recurring availability for this day
        $recurringAvailability = $user->availability()
            ->recurring()
            ->where('day_of_week', $dayOfWeek)
            ->effectiveOn($date)
            ->get();

        // If no recurring rules, assume available
        if ($recurringAvailability->isEmpty()) {
            return true;
        }

        // Check if any availability window covers the shift time
        foreach ($recurringAvailability as $availability) {
            if (!$availability->is_available) {
                continue;
            }
            if ($availability->coversTime($shift->getStartDateTime())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user's availability for a date range
     */
    public function getAvailabilityForDateRange(User $user, Carbon $start, Carbon $end): array
    {
        // Returns array of dates with availability status
    }
}
```

---

## 2.12 AI Scheduling Service

The AI Scheduling Service automatically generates optimal schedules based on business requirements, employee availability, and target hours.

### Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    AI Scheduling Service                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐      │
│  │   Inputs     │    │  Constraint  │    │   Output     │      │
│  │              │───▶│   Solver     │───▶│              │      │
│  │ - Shifts     │    │              │    │ - Assigned   │      │
│  │ - Users      │    │ - Hard       │    │   Shifts     │      │
│  │ - Availab.   │    │ - Soft       │    │ - Warnings   │      │
│  │ - Targets    │    │ - Optimize   │    │ - Score      │      │
│  └──────────────┘    └──────────────┘    └──────────────┘      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Service Interface

```php
class AISchedulingService
{
    /**
     * Generate schedule suggestions for a date range
     */
    public function generateSchedule(
        Tenant $tenant,
        Carbon $startDate,
        Carbon $endDate,
        array $options = []
    ): ScheduleSuggestion;

    /**
     * Fill unassigned shifts with best available employees
     */
    public function fillUnassignedShifts(
        Collection $shifts,
        array $options = []
    ): ScheduleSuggestion;

    /**
     * Find replacement for a specific shift
     */
    public function findReplacementForShift(
        Shift $shift,
        array $options = []
    ): Collection; // Returns ranked list of suitable employees

    /**
     * Analyze schedule for issues
     */
    public function analyzeSchedule(
        Collection $shifts,
        Carbon $startDate,
        Carbon $endDate
    ): ScheduleAnalysis;
}
```

### Constraint Types

**Hard Constraints (Must be satisfied):**
| Constraint | Description |
|------------|-------------|
| Role Qualification | Employee must have the required business role |
| Availability | Employee must be available during shift time |
| Not On Leave | Employee must not have approved leave |
| Not Already Scheduled | Employee can't work overlapping shifts |
| Employment Active | Employee must be actively employed |
| Max Hours | Cannot exceed maximum weekly hours |
| Staffing Max | Cannot exceed max employees per staffing requirement |

**Soft Constraints (Optimized):**
| Constraint | Weight | Description |
|------------|--------|-------------|
| Target Hours | High | Try to meet employee's target hours |
| Preference Level | Medium | Prefer 'preferred' times over 'if_needed' |
| Even Distribution | Medium | Spread hours fairly across employees |
| Minimize Overtime | Medium | Avoid exceeding target hours |
| Consecutive Shifts | Low | Prefer grouping shifts on same day |
| Rest Between Shifts | Low | Ensure adequate rest time |

### Scheduling Algorithm

```php
class ScheduleOptimizer
{
    public function optimize(ScheduleContext $context): ScheduleSuggestion
    {
        $unassignedShifts = $context->getUnassignedShifts();
        $assignments = [];
        $warnings = [];

        foreach ($unassignedShifts as $shift) {
            // 1. Get all eligible employees for this role
            $eligible = $this->getEligibleEmployees($shift, $context);

            if ($eligible->isEmpty()) {
                $warnings[] = new Warning($shift, 'No eligible employees');
                continue;
            }

            // 2. Filter by hard constraints
            $available = $eligible->filter(fn($emp) =>
                $this->satisfiesHardConstraints($emp, $shift, $context)
            );

            if ($available->isEmpty()) {
                $warnings[] = new Warning($shift, 'No available employees');
                continue;
            }

            // 3. Score by soft constraints
            $scored = $available->map(fn($emp) => [
                'employee' => $emp,
                'score' => $this->calculateScore($emp, $shift, $context),
            ])->sortByDesc('score');

            // 4. Assign best candidate
            $best = $scored->first();
            $assignments[] = new Assignment($shift, $best['employee'], $best['score']);

            // 5. Update context (track assigned hours)
            $context->recordAssignment($best['employee'], $shift);
        }

        return new ScheduleSuggestion($assignments, $warnings);
    }

    private function calculateScore(User $employee, Shift $shift, ScheduleContext $context): float
    {
        $score = 100;

        // Target hours factor
        $currentHours = $context->getAssignedHours($employee);
        $targetHours = $employee->employmentDetails->target_hours_per_week ?? 40;
        $shiftHours = $shift->duration_hours;

        if ($currentHours + $shiftHours > $targetHours) {
            $score -= 20; // Overtime penalty
        } elseif ($currentHours + $shiftHours <= $targetHours) {
            $score += 10; // Closer to target bonus
        }

        // Preference level factor
        $preference = $this->getPreferenceLevel($employee, $shift);
        $score += match($preference) {
            PreferenceLevel::Preferred => 15,
            PreferenceLevel::Available => 0,
            PreferenceLevel::IfNeeded => -10,
            default => 0,
        };

        // Fair distribution factor
        $avgHours = $context->getAverageAssignedHours();
        if ($currentHours < $avgHours) {
            $score += 5; // Bonus for under-scheduled employees
        }

        return $score;
    }
}
```

### Schedule Suggestion Response

```php
class ScheduleSuggestion
{
    public array $assignments;      // Proposed shift assignments
    public array $warnings;         // Issues that couldn't be resolved
    public float $score;            // Overall schedule quality score
    public array $metrics;          // Statistics about the schedule

    public function getMetrics(): array
    {
        return [
            'total_shifts' => count($this->assignments),
            'unassigned_shifts' => count($this->warnings),
            'total_hours' => $this->calculateTotalHours(),
            'employees_scheduled' => $this->countUniqueEmployees(),
            'avg_hours_per_employee' => $this->calculateAvgHours(),
            'overtime_hours' => $this->calculateOvertimeHours(),
            'preference_satisfaction' => $this->calculatePreferenceSatisfaction(),
        ];
    }
}
```

### API Endpoints

```php
// Routes
Route::prefix('schedule/ai')->group(function () {
    Route::post('generate', [AIScheduleController::class, 'generate']);
    Route::post('fill-unassigned', [AIScheduleController::class, 'fillUnassigned']);
    Route::post('find-replacement/{shift}', [AIScheduleController::class, 'findReplacement']);
    Route::post('analyze', [AIScheduleController::class, 'analyze']);
});
```

### Feature Gate Middleware

AI Scheduling is a premium add-on requiring subscription verification:

```php
// app/Http/Middleware/RequiresFeature.php
class RequiresFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = auth()->user()?->tenant;

        if (!$tenant) {
            abort(403, 'No tenant context');
        }

        if (!$tenant->hasFeature($feature)) {
            abort(403, 'This feature requires a premium subscription. Please upgrade to access AI Scheduling.');
        }

        return $next($request);
    }
}

// Register in bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'feature' => RequiresFeature::class,
    ]);
})
```

### Routes with Feature Gate

```php
// AI Scheduling routes require 'ai_scheduling' feature
Route::prefix('schedule/ai')
    ->middleware(['auth', 'feature:ai_scheduling'])
    ->group(function () {
        Route::post('generate', [AIScheduleController::class, 'generate']);
        Route::post('fill-unassigned', [AIScheduleController::class, 'fillUnassigned']);
        Route::post('find-replacement/{shift}', [AIScheduleController::class, 'findReplacement']);
        Route::post('analyze', [AIScheduleController::class, 'analyze']);
    });

// Feature status check endpoint (no gate - used to show/hide UI)
Route::get('features/status', [FeatureController::class, 'status']);
```

### Controller Implementation

```php
class AIScheduleController extends Controller
{
    public function __construct()
    {
        // Feature gate applied via route middleware
    }

    public function generate(Request $request, AISchedulingService $scheduler)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $suggestion = $scheduler->generateSchedule(
            auth()->user()->tenant,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $request->only(['location_id', 'department_id'])
        );

        return response()->json([
            'success' => true,
            'assignments' => $suggestion->assignments,
            'warnings' => $suggestion->warnings,
            'metrics' => $suggestion->getMetrics(),
        ]);
    }

    public function fillUnassigned(Request $request, AISchedulingService $scheduler)
    {
        $request->validate([
            'shift_ids' => 'required|array',
            'shift_ids.*' => 'exists:shifts,id',
        ]);

        $shifts = Shift::whereIn('id', $request->shift_ids)
            ->whereNull('user_id')
            ->get();

        $suggestion = $scheduler->fillUnassignedShifts($shifts);

        return response()->json([
            'success' => true,
            'assignments' => $suggestion->assignments,
            'warnings' => $suggestion->warnings,
        ]);
    }
}

class FeatureController extends Controller
{
    public function status(): JsonResponse
    {
        $tenant = auth()->user()->tenant;

        return response()->json([
            'features' => [
                'ai_scheduling' => $tenant->hasAIScheduling(),
                'advanced_analytics' => $tenant->hasFeature('advanced_analytics'),
                'api_access' => $tenant->hasFeature('api_access'),
            ],
            'subscription' => [
                'plan' => $tenant->subscription?->plan->value ?? 'basic',
                'status' => $tenant->subscription?->status->value ?? 'active',
            ],
        ]);
    }
}
```

### Blade Directive for Feature Checks

```php
// AppServiceProvider::boot()
Blade::if('feature', function (string $feature) {
    return auth()->user()?->tenant?->hasFeature($feature) ?? false;
});

// Usage in Blade templates
@feature('ai_scheduling')
    <button>Auto-Fill Shifts</button>
    <button>Generate Schedule</button>
@else
    <div class="upgrade-prompt">
        <p>AI Scheduling is a premium feature.</p>
        <a href="{{ route('subscription.upgrade') }}">Upgrade Now</a>
    </div>
@endfeature
```

---

## 2.13 StaffingRequirement Model

Define minimum and maximum staffing levels for each role by day and time window.

```php
class StaffingRequirement extends Model
{
    use BelongsToTenant, HasFactory;

    // Casts
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'min_employees' => 'integer',
            'max_employees' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // Belongs To
    public function tenant(): BelongsTo
    public function location(): BelongsTo
    public function department(): BelongsTo
    public function businessRole(): BelongsTo

    // Scopes
    public function scopeActive(Builder $query): Builder
    public function scopeForDay(Builder $query, int $dayOfWeek): Builder
    public function scopeForLocation(Builder $query, ?int $locationId): Builder
    public function scopeForDepartment(Builder $query, ?int $departmentId): Builder
    public function scopeForRole(Builder $query, int $roleId): Builder

    // Methods
    public function coversTime(Carbon $time): bool
    {
        $timeOfDay = $time->format('H:i:s');
        $start = $this->start_time->format('H:i:s');
        $end = $this->end_time->format('H:i:s');
        return $timeOfDay >= $start && $timeOfDay < $end;
    }

    public function overlapsTimeWindow(Carbon $startTime, Carbon $endTime): bool
    {
        $reqStart = $this->start_time->format('H:i:s');
        $reqEnd = $this->end_time->format('H:i:s');
        $shiftStart = $startTime->format('H:i:s');
        $shiftEnd = $endTime->format('H:i:s');

        // Check if time windows overlap
        return $shiftStart < $reqEnd && $shiftEnd > $reqStart;
    }
}
```

---

## 2.14 Coverage Analysis Service

Analyze schedule coverage against staffing requirements and generate warnings.

```php
class CoverageAnalysisService
{
    /**
     * Analyze coverage for a date range
     */
    public function analyzeCoverage(
        Tenant $tenant,
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId = null,
        ?int $departmentId = null
    ): CoverageAnalysis;

    /**
     * Get coverage status for a specific day
     */
    public function getDayCoverage(
        Tenant $tenant,
        Carbon $date,
        ?int $locationId = null,
        ?int $departmentId = null
    ): DayCoverage;

    /**
     * Check coverage for a specific time slot
     */
    public function getTimeSlotCoverage(
        Tenant $tenant,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        int $businessRoleId,
        ?int $locationId = null,
        ?int $departmentId = null
    ): TimeSlotCoverage;
}
```

### Coverage Analysis Implementation

```php
class CoverageAnalysisService
{
    /**
     * Get coverage for a specific time slot
     */
    public function getTimeSlotCoverage(
        Tenant $tenant,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        int $businessRoleId,
        ?int $locationId = null,
        ?int $departmentId = null
    ): TimeSlotCoverage {
        // Get staffing requirement for this slot
        $requirement = StaffingRequirement::query()
            ->where('tenant_id', $tenant->id)
            ->where('business_role_id', $businessRoleId)
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->get()
            ->first(fn($req) => $req->overlapsTimeWindow($startTime, $endTime));

        if (!$requirement) {
            return new TimeSlotCoverage(
                startTime: $startTime,
                endTime: $endTime,
                businessRoleId: $businessRoleId,
                minRequired: 0,
                maxAllowed: null,
                scheduled: $this->countScheduledEmployees(...),
                status: CoverageStatus::NoRequirement
            );
        }

        $scheduledCount = $this->countScheduledEmployees(
            $tenant, $date, $startTime, $endTime, $businessRoleId, $locationId, $departmentId
        );

        $status = $this->determineCoverageStatus(
            $scheduledCount,
            $requirement->min_employees,
            $requirement->max_employees
        );

        return new TimeSlotCoverage(
            startTime: $startTime,
            endTime: $endTime,
            businessRoleId: $businessRoleId,
            minRequired: $requirement->min_employees,
            maxAllowed: $requirement->max_employees,
            scheduled: $scheduledCount,
            status: $status
        );
    }

    private function countScheduledEmployees(
        Tenant $tenant,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        int $businessRoleId,
        ?int $locationId,
        ?int $departmentId
    ): int {
        return Shift::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('date', $date)
            ->where('business_role_id', $businessRoleId)
            ->whereNotNull('user_id')
            ->published()
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->where(function($query) use ($startTime, $endTime) {
                // Shift overlaps with time window
                $query->whereTime('start_time', '<', $endTime->format('H:i:s'))
                      ->whereTime('end_time', '>', $startTime->format('H:i:s'));
            })
            ->count();
    }

    private function determineCoverageStatus(
        int $scheduled,
        int $minRequired,
        ?int $maxAllowed
    ): CoverageStatus {
        if ($scheduled < $minRequired) {
            return CoverageStatus::Understaffed;
        }
        if ($maxAllowed !== null && $scheduled > $maxAllowed) {
            return CoverageStatus::Overstaffed;
        }
        return CoverageStatus::Adequate;
    }
}
```

### Coverage Status Enum

```php
enum CoverageStatus: string
{
    case Adequate = 'adequate';         // Within min/max range
    case Understaffed = 'understaffed'; // Below minimum
    case Overstaffed = 'overstaffed';   // Above maximum
    case NoRequirement = 'no_requirement'; // No rule defined
}
```

### Coverage Response Classes

```php
class TimeSlotCoverage
{
    public function __construct(
        public Carbon $startTime,
        public Carbon $endTime,
        public int $businessRoleId,
        public int $minRequired,
        public ?int $maxAllowed,
        public int $scheduled,
        public CoverageStatus $status,
    ) {}

    public function getVariance(): int
    {
        return match($this->status) {
            CoverageStatus::Understaffed => $this->scheduled - $this->minRequired, // Negative
            CoverageStatus::Overstaffed => $this->scheduled - $this->maxAllowed,   // Positive
            default => 0,
        };
    }

    public function getMessage(): string
    {
        return match($this->status) {
            CoverageStatus::Understaffed => sprintf(
                'Need %d more (have %d, need %d)',
                $this->minRequired - $this->scheduled,
                $this->scheduled,
                $this->minRequired
            ),
            CoverageStatus::Overstaffed => sprintf(
                '%d too many (have %d, max %d)',
                $this->scheduled - $this->maxAllowed,
                $this->scheduled,
                $this->maxAllowed
            ),
            CoverageStatus::Adequate => 'Staffing OK',
            CoverageStatus::NoRequirement => 'No requirement set',
        };
    }
}

class DayCoverage
{
    public function __construct(
        public Carbon $date,
        public Collection $timeSlots, // Collection of TimeSlotCoverage
        public int $totalUnderstaffedSlots,
        public int $totalOverstaffedSlots,
    ) {}

    public function hasIssues(): bool
    {
        return $this->totalUnderstaffedSlots > 0 || $this->totalOverstaffedSlots > 0;
    }
}

class CoverageAnalysis
{
    public function __construct(
        public Carbon $startDate,
        public Carbon $endDate,
        public Collection $days, // Collection of DayCoverage
        public array $summary,
    ) {}

    public function getSummary(): array
    {
        return [
            'total_days' => $this->days->count(),
            'days_with_issues' => $this->days->filter(fn($d) => $d->hasIssues())->count(),
            'total_understaffed_slots' => $this->days->sum('totalUnderstaffedSlots'),
            'total_overstaffed_slots' => $this->days->sum('totalOverstaffedSlots'),
        ];
    }
}
```

### Integration with AI Scheduling

The AI Scheduling Service uses staffing requirements as a hard constraint:

```php
class AISchedulingService
{
    public function __construct(
        private CoverageAnalysisService $coverageService
    ) {}

    /**
     * Add staffing requirements as hard constraint
     */
    private function satisfiesStaffingRequirement(
        User $employee,
        Shift $shift,
        ScheduleContext $context
    ): bool {
        $coverage = $this->coverageService->getTimeSlotCoverage(
            $context->tenant,
            $shift->date,
            $shift->getStartDateTime(),
            $shift->getEndDateTime(),
            $shift->business_role_id,
            $shift->location_id,
            $shift->department_id
        );

        // If already at or above max, don't assign more
        if ($coverage->maxAllowed !== null &&
            $coverage->scheduled >= $coverage->maxAllowed) {
            return false;
        }

        return true;
    }
}
```

### API Endpoints for Staffing Requirements

```php
Route::prefix('staffing-requirements')->group(function () {
    Route::get('/', [StaffingRequirementController::class, 'index']);
    Route::post('/', [StaffingRequirementController::class, 'store']);
    Route::put('/{requirement}', [StaffingRequirementController::class, 'update']);
    Route::delete('/{requirement}', [StaffingRequirementController::class, 'destroy']);
});

Route::prefix('coverage')->group(function () {
    Route::get('analyze', [CoverageController::class, 'analyze']);
    Route::get('day/{date}', [CoverageController::class, 'day']);
});
```

---

## 2.14.1 Attendance Report Service

Comprehensive attendance reporting and analytics for managers.

```php
class AttendanceReportService
{
    /**
     * Get attendance rate: (shifts worked / shifts scheduled) x 100
     * @return array{rate: float, worked: int, scheduled: int}
     */
    public function getAttendanceRate(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array;

    /**
     * Get punctuality rate: (on-time arrivals / total arrivals) x 100
     * @return array{rate: float, on_time: int, late: int, early: int, total: int}
     */
    public function getPunctualityRate(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array;

    /**
     * Get overtime hours: sum of positive variances
     * @return array{hours: float, minutes: int, entries: int}
     */
    public function getOvertimeHours(
        Carbon $startDate,
        Carbon $endDate,
        ?int $departmentId = null,
        ?int $locationId = null,
        ?int $userId = null
    ): array;

    /**
     * Get undertime hours: sum of negative variances
     */
    public function getUndertimeHours(...): array;

    /**
     * Get missed shifts (no-shows)
     * @return array{count: int, entries: Collection}
     */
    public function getMissedShifts(...): array;

    /**
     * Get employee summary for the given period
     */
    public function getEmployeeSummary(
        int $employeeId,
        Carbon $startDate,
        Carbon $endDate
    ): array;

    /**
     * Get department summary for the given period
     */
    public function getDepartmentSummary(
        int $departmentId,
        Carbon $startDate,
        Carbon $endDate
    ): array;

    /**
     * Generate detailed report data
     */
    public function generatePunctualityReport(...): array;
    public function generateHoursWorkedReport(...): array;
    public function generateOvertimeReport(...): array;
    public function generateAbsenceReport(...): array;
    public function generateAttendanceSummary(...): array;

    /**
     * Export report data to CSV format
     */
    public function exportToCsv(string $reportType, array $reportData): string;
}
```

### Report Calculations

**Attendance Rate:**
```
Attendance Rate = (TimeEntries with clock_in_at / Scheduled Shifts with user_id) × 100
```

**Punctuality Rate:**
```
On-time = clock_in_variance_minutes <= grace_period (default 15 min)
Late = clock_in_variance_minutes > grace_period
Early = clock_in_variance_minutes < -5 minutes
Punctuality Rate = (On-time / Total Arrivals) × 100
```

**Variance Calculations:**
- Uses TimeEntry model accessors: `variance_minutes`, `clock_in_variance_minutes`, `clock_out_variance_minutes`
- Positive variance = overtime/late
- Negative variance = undertime/early

### Authorization

Reports require `viewReports` policy permission on User model:
- SuperAdmin: Can view all
- Admin: Can view all in tenant
- LocationAdmin: Can view within managed locations
- DepartmentAdmin: Can view within managed departments
- Employee: No access (403 Forbidden)

---

## 2.14.2 Recurring Shift Service

Manages creation, modification, and extension of recurring shift patterns.

```php
class RecurringShiftService
{
    /**
     * Default generation window in weeks
     */
    protected int $generationWeeks = 12;

    /**
     * Generate child shift instances from a recurring parent shift.
     * Called immediately when creating a new recurring shift.
     * @return Collection<int, Shift>
     */
    public function generateInstances(Shift $parentShift): Collection;

    /**
     * Calculate all occurrence dates based on recurrence rule.
     * Handles daily, weekly (with days of week), and monthly frequencies.
     * @return Collection<int, Carbon>
     */
    public function calculateOccurrenceDates(Shift $parentShift): Collection;

    /**
     * Update all future child instances of a recurring shift.
     * Used when edit_scope is 'future'.
     */
    public function updateFutureInstances(Shift $parentShift, array $data): int;

    /**
     * Delete all future child instances of a recurring shift.
     * Used when delete_scope is 'future'.
     */
    public function deleteFutureInstances(Shift $parentShift): int;

    /**
     * Detach a child shift from its parent for individual editing.
     * Sets parent_shift_id to null.
     */
    public function detachFromParent(Shift $childShift): Shift;

    /**
     * Extend recurring shifts approaching their generation window end.
     * Called by daily scheduled command: shifts:extend-recurring
     */
    public function extendRecurringShifts(): int;

    /**
     * Set the generation window in weeks.
     */
    public function setGenerationWeeks(int $weeks): self;
}
```

### Recurrence Rule Schema

```json
{
    "frequency": "weekly",     // daily, weekly, monthly
    "interval": 1,             // Every N days/weeks/months
    "days_of_week": [1, 3, 5], // 0=Sun through 6=Sat (weekly only)
    "end_date": "2026-06-30",  // Optional: hard end date
    "end_after_occurrences": 52 // Optional: limit number of instances
}
```

### API Endpoints

**Create Recurring Shift:**
```
POST /shifts
{
    "location_id": 1,
    "department_id": 1,
    "business_role_id": 1,
    "user_id": null,
    "date": "2026-02-03",
    "start_time": "09:00",
    "end_time": "17:00",
    "is_recurring": true,
    "recurrence_rule": {
        "frequency": "weekly",
        "interval": 1,
        "days_of_week": [1, 3, 5],
        "end_after_occurrences": 12
    }
}
```

**Update with Scope:**
```
PUT /shifts/{id}
{
    "start_time": "08:00",
    "end_time": "16:00",
    "edit_scope": "future"  // 'single' or 'future'
}
```

**Delete with Scope:**
```
DELETE /shifts/{id}?delete_scope=future  // 'single' or 'future'
```

### Shift Model Helpers

```php
// Check if shift is recurring parent (template)
$shift->isRecurringParent(): bool

// Check if shift is child of recurring parent
$shift->isRecurringChild(): bool

// Check if shift has children
$shift->hasChildren(): bool

// Get future children from today onwards
$shift->getFutureChildren(): Collection

// Scope: only recurring parents
Shift::recurringParents()->get()

// Scope: only recurring children
Shift::recurringChildren()->get()

// Get human-readable recurrence label
$shift->recurrence_frequency_label  // "Weekly on Mon, Wed, Fri"
```

### Scheduled Command

```php
// routes/console.php
Schedule::command('shifts:extend-recurring')->daily();
```

---

## 2.15 Premium Feature Services

### Advanced Geofencing Service

```php
class GeofencingService
{
    /**
     * Check if user is within location geofence
     */
    public function isWithinGeofence(
        Location $location,
        float $latitude,
        float $longitude
    ): GeofenceCheckResult {
        $geofence = $location->geofence;

        if (!$geofence) {
            return new GeofenceCheckResult(
                withinFence: true,
                distance: null,
                enforced: false
            );
        }

        $distance = $this->calculateDistance(
            $geofence->latitude,
            $geofence->longitude,
            $latitude,
            $longitude
        );

        return new GeofenceCheckResult(
            withinFence: $distance <= $geofence->radius_meters,
            distance: $distance,
            enforced: $geofence->enforce_clock_in
        );
    }

    /**
     * Record geofence event
     */
    public function recordEvent(
        User $user,
        Location $location,
        string $eventType,
        float $latitude,
        float $longitude
    ): GeofenceEvent;

    /**
     * Calculate distance using Haversine formula
     */
    private function calculateDistance(
        float $lat1, float $lon1,
        float $lat2, float $lon2
    ): float;
}
```

### Labor Forecasting Service

```php
class LaborForecastingService
{
    /**
     * Generate forecasts for a date range
     */
    public function generateForecasts(
        Tenant $tenant,
        Location $location,
        Carbon $startDate,
        Carbon $endDate
    ): Collection;

    /**
     * Get forecasts for scheduling
     */
    public function getForecasts(
        Tenant $tenant,
        Location $location,
        Carbon $date
    ): Collection;

    /**
     * Analyze historical data for patterns
     */
    private function analyzeHistoricalPatterns(
        Tenant $tenant,
        Location $location,
        int $weeksBack = 12
    ): array;

    /**
     * Apply seasonal adjustments
     */
    private function applySeasonalFactors(
        array $baseForecasts,
        Carbon $date
    ): array;
}
```

### Payroll Integration Service

```php
class PayrollIntegrationService
{
    /**
     * Export timesheet data to payroll provider
     */
    public function exportTimesheets(
        Tenant $tenant,
        Carbon $periodStart,
        Carbon $periodEnd
    ): PayrollExport;

    /**
     * Get supported providers
     */
    public function getSupportedProviders(): array
    {
        return [
            'adp' => 'ADP',
            'paychex' => 'Paychex',
            'gusto' => 'Gusto',
            'quickbooks' => 'QuickBooks',
            'xero' => 'Xero',
        ];
    }

    /**
     * Test connection to provider
     */
    public function testConnection(PayrollIntegration $integration): bool;

    /**
     * Format export for specific provider
     */
    private function formatForProvider(
        string $provider,
        Collection $timeEntries
    ): array;
}
```

### Team Messaging Service

```php
class MessagingService
{
    /**
     * Create and publish announcement
     */
    public function createAnnouncement(
        User $author,
        array $data
    ): Announcement;

    /**
     * Send direct message
     */
    public function sendDirectMessage(
        User $sender,
        User $recipient,
        string $content
    ): DirectMessage;

    /**
     * Mark announcement as read
     */
    public function markAnnouncementRead(
        Announcement $announcement,
        User $user
    ): void;

    /**
     * Acknowledge announcement
     */
    public function acknowledgeAnnouncement(
        Announcement $announcement,
        User $user
    ): void;

    /**
     * Get unread counts for user
     */
    public function getUnreadCounts(User $user): array;
}
```

### Document Management Service

```php
class DocumentManagementService
{
    /**
     * Upload employee document
     */
    public function uploadDocument(
        User $employee,
        User $uploader,
        UploadedFile $file,
        array $metadata
    ): EmployeeDocument;

    /**
     * Get expiring certifications
     */
    public function getExpiringCertifications(
        Tenant $tenant,
        int $daysAhead = 30
    ): Collection;

    /**
     * Check if user has required certifications for role
     */
    public function hasRequiredCertifications(
        User $user,
        BusinessRole $role
    ): CertificationCheckResult;

    /**
     * Send expiry reminders
     */
    public function sendExpiryReminders(Tenant $tenant): int;
}

class CertificationCheckResult
{
    public function __construct(
        public bool $compliant,
        public Collection $missingCertifications,
        public Collection $expiringCertifications,
    ) {}
}
```

### Labor Cost Budgeting Service

```php
class LaborBudgetService
{
    /**
     * Create or update a budget for a location/department
     */
    public function setBudget(
        Location $location,
        ?Department $department,
        string $periodType,
        float $amount,
        string $currency = 'USD',
        ?Carbon $effectiveFrom = null
    ): LaborBudget;

    /**
     * Calculate scheduled labor cost for a period
     */
    public function calculateScheduledCost(
        LaborBudget $budget,
        Carbon $periodStart,
        Carbon $periodEnd
    ): LaborCostBreakdown;

    /**
     * Calculate actual labor cost from time entries
     */
    public function calculateActualCost(
        LaborBudget $budget,
        Carbon $periodStart,
        Carbon $periodEnd
    ): LaborCostBreakdown;

    /**
     * Get budget status for display
     */
    public function getBudgetStatus(LaborBudget $budget): BudgetStatus;

    /**
     * Check if adding a shift would exceed budget
     */
    public function wouldExceedBudget(
        LaborBudget $budget,
        Shift $shift
    ): BudgetImpact;

    /**
     * Get labor cost per employee for a period
     */
    public function getLaborCostByEmployee(
        Tenant $tenant,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?Location $location = null
    ): Collection;

    /**
     * Send budget alerts to managers
     */
    public function sendBudgetAlerts(): int;

    /**
     * Create snapshot for completed period
     */
    public function createPeriodSnapshot(LaborBudget $budget, Carbon $periodEnd): LaborBudgetSnapshot;
}

class LaborCostBreakdown
{
    public function __construct(
        public float $totalCost,
        public float $regularHours,
        public float $regularCost,
        public float $overtimeHours,
        public float $overtimeCost,
        public array $byDepartment,
        public array $byRole,
    ) {}
}

class BudgetStatus
{
    public function __construct(
        public float $budgetAmount,
        public float $scheduledAmount,
        public float $actualAmount,
        public float $remainingAmount,
        public float $percentUsed,
        public string $status, // 'on_track', 'warning', 'critical', 'over_budget'
    ) {}
}

class BudgetImpact
{
    public function __construct(
        public bool $wouldExceed,
        public float $shiftCost,
        public float $newTotal,
        public float $budgetAmount,
        public string $message,
    ) {}
}
```

### Kiosk Service

```php
class KioskService
{
    /**
     * Register a new kiosk for a location
     */
    public function registerKiosk(
        Location $location,
        string $name,
        array $authMethods,
        array $settings = []
    ): Kiosk;

    /**
     * Generate access token for kiosk device
     */
    public function generateAccessToken(Kiosk $kiosk): string;

    /**
     * Authenticate kiosk session
     */
    public function authenticateKiosk(string $accessToken): ?Kiosk;

    /**
     * Authenticate employee at kiosk
     */
    public function authenticateEmployee(
        Kiosk $kiosk,
        string $authMethod,
        string $credential // PIN, badge ID, or QR code
    ): ?User;

    /**
     * Process clock action from kiosk
     */
    public function processClockAction(
        Kiosk $kiosk,
        User $user,
        string $action, // 'clock_in', 'clock_out', 'break_start', 'break_end'
        ?string $photoPath = null
    ): KioskClockResult;

    /**
     * Set or update employee PIN
     */
    public function setEmployeePin(User $user, string $pin): void;

    /**
     * Verify employee PIN
     */
    public function verifyPin(User $user, string $pin): bool;

    /**
     * Lock/unlock kiosk remotely
     */
    public function setKioskLock(Kiosk $kiosk, bool $locked): void;

    /**
     * Get employees currently clocked in at location
     */
    public function getClockedInEmployees(Kiosk $kiosk): Collection;

    /**
     * Get kiosk activity log
     */
    public function getActivityLog(
        Kiosk $kiosk,
        ?Carbon $from = null,
        ?Carbon $to = null
    ): Collection;

    /**
     * Manager override for clock correction
     */
    public function managerOverride(
        Kiosk $kiosk,
        User $manager,
        User $employee,
        string $action,
        ?Carbon $adjustedTime = null
    ): KioskClockResult;
}

class KioskClockResult
{
    public function __construct(
        public bool $success,
        public ?TimeEntry $timeEntry,
        public ?string $error,
        public ?string $message,
        public ?Carbon $timestamp,
    ) {}
}
```

### API Routes for Premium Features

```php
// Advanced Geofencing
Route::prefix('geofencing')
    ->middleware(['auth', 'feature:advanced_geofencing'])
    ->group(function () {
        Route::get('locations/{location}', [GeofenceController::class, 'show']);
        Route::put('locations/{location}', [GeofenceController::class, 'update']);
        Route::get('events', [GeofenceController::class, 'events']);
    });

// Labor Forecasting
Route::prefix('forecasting')
    ->middleware(['auth', 'feature:labor_forecasting'])
    ->group(function () {
        Route::get('/', [ForecastController::class, 'index']);
        Route::post('generate', [ForecastController::class, 'generate']);
    });

// Payroll Integrations
Route::prefix('payroll')
    ->middleware(['auth', 'feature:payroll_integrations'])
    ->group(function () {
        Route::get('integration', [PayrollController::class, 'show']);
        Route::post('integration', [PayrollController::class, 'store']);
        Route::post('integration/test', [PayrollController::class, 'test']);
        Route::post('export', [PayrollController::class, 'export']);
        Route::get('exports', [PayrollController::class, 'exports']);
    });

// Team Messaging
Route::prefix('messaging')
    ->middleware(['auth', 'feature:team_messaging'])
    ->group(function () {
        Route::apiResource('announcements', AnnouncementController::class);
        Route::post('announcements/{announcement}/read', [AnnouncementController::class, 'markRead']);
        Route::post('announcements/{announcement}/acknowledge', [AnnouncementController::class, 'acknowledge']);
        Route::get('messages', [DirectMessageController::class, 'index']);
        Route::post('messages', [DirectMessageController::class, 'store']);
        Route::get('unread-counts', [MessagingController::class, 'unreadCounts']);
    });

// Document Management
Route::prefix('documents')
    ->middleware(['auth', 'feature:document_management'])
    ->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/', [DocumentController::class, 'store']);
        Route::delete('/{document}', [DocumentController::class, 'destroy']);
        Route::apiResource('certifications', CertificationController::class);
        Route::get('users/{user}/certifications', [UserCertificationController::class, 'index']);
        Route::post('users/{user}/certifications', [UserCertificationController::class, 'store']);
        Route::get('expiring', [CertificationController::class, 'expiring']);
    });

// Multi-Location Analytics (Enterprise)
Route::prefix('analytics/multi-location')
    ->middleware(['auth', 'feature:multi_location_analytics'])
    ->group(function () {
        Route::get('overview', [MultiLocationAnalyticsController::class, 'overview']);
        Route::get('comparison', [MultiLocationAnalyticsController::class, 'comparison']);
        Route::get('labor-costs', [MultiLocationAnalyticsController::class, 'laborCosts']);
    });

// Custom Branding (Enterprise)
Route::prefix('branding')
    ->middleware(['auth', 'feature:custom_branding'])
    ->group(function () {
        Route::get('/', [BrandingController::class, 'show']);
        Route::put('/', [BrandingController::class, 'update']);
        Route::post('logo', [BrandingController::class, 'uploadLogo']);
    });

// Labor Cost Budgeting
Route::prefix('budgets')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [LaborBudgetController::class, 'index']);
        Route::post('/', [LaborBudgetController::class, 'store']);
        Route::get('/{budget}', [LaborBudgetController::class, 'show']);
        Route::put('/{budget}', [LaborBudgetController::class, 'update']);
        Route::delete('/{budget}', [LaborBudgetController::class, 'destroy']);
        Route::get('/{budget}/status', [LaborBudgetController::class, 'status']);
        Route::get('/{budget}/breakdown', [LaborBudgetController::class, 'breakdown']);
        Route::get('/location/{location}/current', [LaborBudgetController::class, 'currentForLocation']);
    });

// Kiosk Mode
Route::prefix('kiosks')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [KioskController::class, 'index']);
        Route::post('/', [KioskController::class, 'store']);
        Route::get('/{kiosk}', [KioskController::class, 'show']);
        Route::put('/{kiosk}', [KioskController::class, 'update']);
        Route::delete('/{kiosk}', [KioskController::class, 'destroy']);
        Route::post('/{kiosk}/regenerate-token', [KioskController::class, 'regenerateToken']);
        Route::post('/{kiosk}/lock', [KioskController::class, 'lock']);
        Route::post('/{kiosk}/unlock', [KioskController::class, 'unlock']);
        Route::get('/{kiosk}/activity', [KioskController::class, 'activity']);
    });

// Kiosk Terminal Endpoints (authenticated via kiosk token)
Route::prefix('kiosk-terminal')
    ->middleware(['kiosk.auth'])
    ->group(function () {
        Route::get('/status', [KioskTerminalController::class, 'status']);
        Route::post('/authenticate', [KioskTerminalController::class, 'authenticateEmployee']);
        Route::post('/clock-in', [KioskTerminalController::class, 'clockIn']);
        Route::post('/clock-out', [KioskTerminalController::class, 'clockOut']);
        Route::post('/break-start', [KioskTerminalController::class, 'breakStart']);
        Route::post('/break-end', [KioskTerminalController::class, 'breakEnd']);
        Route::get('/clocked-in', [KioskTerminalController::class, 'clockedInEmployees']);
        Route::post('/photo-upload', [KioskTerminalController::class, 'uploadPhoto']);
    });

// Employee PIN Management
Route::prefix('employee-pin')
    ->middleware(['auth'])
    ->group(function () {
        Route::post('/set', [EmployeePinController::class, 'set']);
        Route::post('/reset/{user}', [EmployeePinController::class, 'reset']); // Manager only
    });

// Schedule Print/Export
Route::prefix('schedule')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/print', [SchedulePrintController::class, 'preview']);
        Route::get('/print/pdf', [SchedulePrintController::class, 'pdf']);
    });

// Stripe Subscription Management
Route::prefix('subscription')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [SubscriptionController::class, 'show']);
        Route::post('/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('/change-plan', [SubscriptionController::class, 'changePlan']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('/resume', [SubscriptionController::class, 'resume']);
        Route::get('/invoices', [SubscriptionController::class, 'invoices']);
        Route::get('/invoices/{invoice}/download', [SubscriptionController::class, 'downloadInvoice']);
        Route::post('/payment-method', [SubscriptionController::class, 'updatePaymentMethod']);
        Route::post('/add-addon/{addon}', [SubscriptionController::class, 'addAddon']);
        Route::post('/remove-addon/{addon}', [SubscriptionController::class, 'removeAddon']);
    });

// Stripe Webhooks (no auth - verified via signature)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// SuperAdmin Analytics
Route::prefix('super-admin/analytics')
    ->middleware(['auth', 'super-admin'])
    ->group(function () {
        Route::get('/revenue', [SuperAdminAnalyticsController::class, 'revenue']);
        Route::get('/subscriptions', [SuperAdminAnalyticsController::class, 'subscriptions']);
        Route::get('/growth', [SuperAdminAnalyticsController::class, 'growth']);
        Route::get('/churn', [SuperAdminAnalyticsController::class, 'churn']);
        Route::get('/mrr-history', [SuperAdminAnalyticsController::class, 'mrrHistory']);
        Route::get('/tenant-health', [SuperAdminAnalyticsController::class, 'tenantHealth']);
        Route::get('/export', [SuperAdminAnalyticsController::class, 'export']);
    });
```

---

## 2.16 Stripe Integration

### Database Tables for Billing

#### stripe_customers
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, UNIQUE | Tenant reference |
| stripe_id | VARCHAR(255) | NOT NULL, UNIQUE | Stripe customer ID |
| pm_type | VARCHAR(255) | NULLABLE | Payment method type (card) |
| pm_last_four | VARCHAR(4) | NULLABLE | Last 4 digits |
| trial_ends_at | TIMESTAMP | NULLABLE | Trial end date |
| created_at | TIMESTAMP | NULLABLE | |
| updated_at | TIMESTAMP | NULLABLE | |

#### subscriptions
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| name | VARCHAR(255) | NOT NULL | Subscription name (default) |
| stripe_id | VARCHAR(255) | NOT NULL, UNIQUE | Stripe subscription ID |
| stripe_status | VARCHAR(255) | NOT NULL | active, canceled, past_due, etc. |
| stripe_price | VARCHAR(255) | NULLABLE | Price ID |
| quantity | INTEGER | NULLABLE | Quantity (for per-seat) |
| trial_ends_at | TIMESTAMP | NULLABLE | Trial end |
| ends_at | TIMESTAMP | NULLABLE | Cancellation end date |
| created_at | TIMESTAMP | NULLABLE | |
| updated_at | TIMESTAMP | NULLABLE | |

#### subscription_items
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| subscription_id | BIGINT UNSIGNED | FK → subscriptions.id | Subscription reference |
| stripe_id | VARCHAR(255) | NOT NULL, UNIQUE | Stripe subscription item ID |
| stripe_product | VARCHAR(255) | NOT NULL | Product ID |
| stripe_price | VARCHAR(255) | NOT NULL | Price ID |
| quantity | INTEGER | NULLABLE | |
| created_at | TIMESTAMP | NULLABLE | |
| updated_at | TIMESTAMP | NULLABLE | |

#### tenant_billing_details
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id, UNIQUE | Tenant reference |
| legal_name | VARCHAR(255) | NOT NULL | Legal company name |
| trading_name | VARCHAR(255) | NULLABLE | Trading name if different |
| registration_number | VARCHAR(100) | NULLABLE | Company registration number |
| tax_id | VARCHAR(100) | NULLABLE | Tax ID (TIN/EIN) |
| vat_number | VARCHAR(50) | NULLABLE | VAT/GST registration |
| is_tax_exempt | BOOLEAN | DEFAULT FALSE | Tax exempt status |
| tax_exempt_certificate | VARCHAR(255) | NULLABLE | Certificate file path |
| billing_address_line_1 | VARCHAR(255) | NULLABLE | Street address |
| billing_address_line_2 | VARCHAR(255) | NULLABLE | Address line 2 |
| billing_city | VARCHAR(100) | NULLABLE | City |
| billing_state | VARCHAR(100) | NULLABLE | State/Province |
| billing_postal_code | VARCHAR(20) | NULLABLE | Postal/ZIP code |
| billing_country | VARCHAR(2) | NULLABLE | ISO country code |
| billing_contact_name | VARCHAR(255) | NULLABLE | Billing contact |
| billing_email | VARCHAR(255) | NULLABLE | Billing email |
| billing_phone | VARCHAR(50) | NULLABLE | Billing phone |
| accounts_payable_email | VARCHAR(255) | NULLABLE | AP email for invoices |
| preferred_currency | VARCHAR(3) | DEFAULT 'USD' | Currency code |
| payment_terms_days | INTEGER | DEFAULT 14 | Net payment terms |
| requires_po | BOOLEAN | DEFAULT FALSE | PO required |
| default_po_number | VARCHAR(100) | NULLABLE | Default PO number |
| bank_name | VARCHAR(255) | NULLABLE | Bank name for refunds |
| bank_account_name | VARCHAR(255) | NULLABLE | Account holder name |
| bank_account_number | VARCHAR(255) | NULLABLE | Account/IBAN (encrypted) |
| bank_routing_code | VARCHAR(100) | NULLABLE | Sort/SWIFT/BIC |
| created_at | TIMESTAMP | NULLABLE | |
| updated_at | TIMESTAMP | NULLABLE | |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (tenant_id)
- INDEX (vat_number)
- INDEX (tax_id)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE

---

#### invoice_number_sequences
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| year | SMALLINT | NOT NULL | Calendar year (2026) |
| type | VARCHAR(50) | DEFAULT 'invoice' | invoice, credit_note, proforma |
| last_number | INTEGER | DEFAULT 0 | Last used sequence number |
| prefix | VARCHAR(10) | NULLABLE | Optional prefix (e.g., INV-) |
| created_at | TIMESTAMP | NULLABLE | |
| updated_at | TIMESTAMP | NULLABLE | |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (year, type)

---

#### invoices
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant being invoiced |
| invoice_number | VARCHAR(20) | NOT NULL, UNIQUE | e.g., 2026-001 |
| type | VARCHAR(50) | DEFAULT 'invoice' | invoice, credit_note, proforma |
| source | VARCHAR(50) | DEFAULT 'manual' | manual, stripe, system |
| stripe_invoice_id | VARCHAR(255) | NULLABLE, UNIQUE | Stripe invoice ID if from Stripe |
| status | VARCHAR(50) | DEFAULT 'draft' | draft, sent, paid, overdue, cancelled, refunded |
| issue_date | DATE | NOT NULL | Invoice date |
| due_date | DATE | NOT NULL | Payment due date |
| paid_date | DATE | NULLABLE | Date payment received |
| currency | VARCHAR(3) | DEFAULT 'USD' | Currency code |
| subtotal | DECIMAL(12,2) | NOT NULL | Sum before tax |
| tax_rate | DECIMAL(5,2) | DEFAULT 0 | Tax percentage |
| tax_amount | DECIMAL(12,2) | DEFAULT 0 | Tax amount |
| total | DECIMAL(12,2) | NOT NULL | Final total |
| amount_paid | DECIMAL(12,2) | DEFAULT 0 | Amount received |
| amount_due | DECIMAL(12,2) | NOT NULL | Remaining balance |
| po_number | VARCHAR(100) | NULLABLE | Purchase order number |
| notes | TEXT | NULLABLE | Internal notes |
| customer_notes | TEXT | NULLABLE | Notes shown on invoice |
| payment_instructions | TEXT | NULLABLE | How to pay |
| pdf_path | VARCHAR(255) | NULLABLE | Generated PDF path |
| sent_at | TIMESTAMP | NULLABLE | When emailed to customer |
| created_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | User who created |
| created_at | TIMESTAMP | NULLABLE | |
| updated_at | TIMESTAMP | NULLABLE | |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (invoice_number)
- UNIQUE (stripe_invoice_id)
- INDEX (tenant_id)
- INDEX (status)
- INDEX (issue_date)
- INDEX (due_date)
- INDEX (type)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- created_by → users(id) ON DELETE SET NULL

---

#### invoice_items
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| invoice_id | BIGINT UNSIGNED | FK → invoices.id | Invoice reference |
| description | VARCHAR(500) | NOT NULL | Line item description |
| quantity | DECIMAL(10,2) | DEFAULT 1 | Quantity |
| unit_price | DECIMAL(12,2) | NOT NULL | Price per unit |
| amount | DECIMAL(12,2) | NOT NULL | Line total (qty × price) |
| tax_rate | DECIMAL(5,2) | DEFAULT 0 | Item-specific tax rate |
| tax_amount | DECIMAL(12,2) | DEFAULT 0 | Item tax amount |
| sort_order | INTEGER | DEFAULT 0 | Display order |
| created_at | TIMESTAMP | NULLABLE | |
| updated_at | TIMESTAMP | NULLABLE | |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (invoice_id)

**Foreign Keys:**
- invoice_id → invoices(id) ON DELETE CASCADE

---

#### invoice_payments
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| invoice_id | BIGINT UNSIGNED | FK → invoices.id | Invoice reference |
| amount | DECIMAL(12,2) | NOT NULL | Payment amount |
| payment_method | VARCHAR(50) | NOT NULL | card, bank_transfer, check, other |
| reference | VARCHAR(255) | NULLABLE | Transaction/check reference |
| stripe_payment_id | VARCHAR(255) | NULLABLE | Stripe payment intent ID |
| paid_at | TIMESTAMP | NOT NULL | Payment timestamp |
| notes | TEXT | NULLABLE | Payment notes |
| recorded_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | User who recorded |
| created_at | TIMESTAMP | NULLABLE | |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (invoice_id)
- INDEX (stripe_payment_id)

**Foreign Keys:**
- invoice_id → invoices(id) ON DELETE CASCADE
- recorded_by → users(id) ON DELETE SET NULL

---

### Invoice Service

```php
class InvoiceService
{
    /**
     * Generate next invoice number for the year
     */
    public function generateInvoiceNumber(
        int $year = null,
        string $type = 'invoice'
    ): string;

    /**
     * Create a new ad-hoc invoice
     */
    public function createInvoice(
        Tenant $tenant,
        array $items,
        array $options = []
    ): Invoice;

    /**
     * Create invoice from Stripe invoice
     */
    public function createFromStripeInvoice(
        Tenant $tenant,
        \Stripe\Invoice $stripeInvoice
    ): Invoice;

    /**
     * Add line item to invoice
     */
    public function addItem(
        Invoice $invoice,
        string $description,
        float $quantity,
        float $unitPrice,
        float $taxRate = 0
    ): InvoiceItem;

    /**
     * Calculate invoice totals
     */
    public function calculateTotals(Invoice $invoice): Invoice;

    /**
     * Mark invoice as sent
     */
    public function markAsSent(Invoice $invoice): Invoice;

    /**
     * Record payment against invoice
     */
    public function recordPayment(
        Invoice $invoice,
        float $amount,
        string $method,
        ?string $reference = null
    ): InvoicePayment;

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice, ?Carbon $paidDate = null): Invoice;

    /**
     * Cancel invoice
     */
    public function cancelInvoice(Invoice $invoice, ?string $reason = null): Invoice;

    /**
     * Create credit note for invoice
     */
    public function createCreditNote(
        Invoice $originalInvoice,
        array $items,
        ?string $reason = null
    ): Invoice;

    /**
     * Generate PDF for invoice
     */
    public function generatePdf(Invoice $invoice): string;

    /**
     * Send invoice to customer
     */
    public function sendToCustomer(Invoice $invoice): void;

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(): Collection;

    /**
     * Send overdue reminder
     */
    public function sendOverdueReminder(Invoice $invoice): void;
}
```

### Invoice Number Generator

```php
class InvoiceNumberGenerator
{
    /**
     * Get next sequential number for the year
     * Uses database locking to prevent duplicates
     */
    public function getNext(int $year = null, string $type = 'invoice'): string
    {
        $year = $year ?? now()->year;

        return DB::transaction(function () use ($year, $type) {
            $sequence = InvoiceNumberSequence::lockForUpdate()
                ->firstOrCreate(
                    ['year' => $year, 'type' => $type],
                    ['last_number' => 0, 'prefix' => null]
                );

            $sequence->increment('last_number');

            $number = str_pad($sequence->last_number, 3, '0', STR_PAD_LEFT);

            return $sequence->prefix
                ? "{$sequence->prefix}{$year}-{$number}"
                : "{$year}-{$number}";
        });
    }

    /**
     * Peek at what the next number would be (without incrementing)
     */
    public function peekNext(int $year = null, string $type = 'invoice'): string
    {
        $year = $year ?? now()->year;

        $sequence = InvoiceNumberSequence::where('year', $year)
            ->where('type', $type)
            ->first();

        $nextNumber = ($sequence?->last_number ?? 0) + 1;
        $number = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $prefix = $sequence?->prefix ?? '';

        return $prefix ? "{$prefix}{$year}-{$number}" : "{$year}-{$number}";
    }
}
```

### VIES VAT Validation Service

```php
use SoapClient;
use Illuminate\Support\Facades\Cache;

class ViesValidationService
{
    private const VIES_WSDL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * EU country codes for validation
     */
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
    ];

    /**
     * Validate a VAT number against VIES database
     */
    public function validate(string $vatNumber): ViesValidationResult
    {
        $vatNumber = $this->sanitizeVatNumber($vatNumber);
        $countryCode = substr($vatNumber, 0, 2);
        $number = substr($vatNumber, 2);

        if (!in_array($countryCode, self::EU_COUNTRIES)) {
            return new ViesValidationResult(
                valid: false,
                vatNumber: $vatNumber,
                countryCode: $countryCode,
                error: 'Not an EU country code'
            );
        }

        // Check cache first (cache for 24 hours)
        $cacheKey = "vies_validation_{$vatNumber}";
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $client = new SoapClient(self::VIES_WSDL, [
                'connection_timeout' => 10,
                'exceptions' => true,
            ]);

            $response = $client->checkVat([
                'countryCode' => $countryCode,
                'vatNumber' => $number,
            ]);

            $result = new ViesValidationResult(
                valid: $response->valid,
                vatNumber: $vatNumber,
                countryCode: $countryCode,
                companyName: $response->name ?? null,
                companyAddress: $response->address ?? null,
                requestDate: now(),
                requestId: $response->requestIdentifier ?? null
            );

            Cache::put($cacheKey, $result, now()->addHours(24));

            return $result;

        } catch (\Exception $e) {
            return new ViesValidationResult(
                valid: false,
                vatNumber: $vatNumber,
                countryCode: $countryCode,
                error: 'VIES service unavailable: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check if country is in EU
     */
    public function isEuCountry(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), self::EU_COUNTRIES);
    }

    /**
     * Sanitize VAT number (remove spaces and special chars)
     */
    private function sanitizeVatNumber(string $vatNumber): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', $vatNumber));
    }
}

class ViesValidationResult
{
    public function __construct(
        public bool $valid,
        public string $vatNumber,
        public string $countryCode,
        public ?string $companyName = null,
        public ?string $companyAddress = null,
        public ?Carbon $requestDate = null,
        public ?string $requestId = null,
        public ?string $error = null
    ) {}

    public function isEuBusiness(): bool
    {
        return $this->valid && $this->countryCode !== 'ES';
    }
}
```

### EU VAT Determination Service

```php
class EuVatDeterminationService
{
    private const SPANISH_VAT_RATE = 21.00;
    private const SPAIN_COUNTRY_CODE = 'ES';

    public function __construct(
        private ViesValidationService $viesService
    ) {}

    /**
     * Determine VAT treatment for an invoice
     */
    public function determineVatTreatment(
        TenantBillingDetail $customer,
        ?string $vatNumber = null
    ): VatDetermination {
        $customerCountry = strtoupper($customer->billing_country_code);
        $vatNumber = $vatNumber ?? $customer->tax_id;

        // Scenario 1: Spanish customer
        if ($customerCountry === self::SPAIN_COUNTRY_CODE) {
            return new VatDetermination(
                rate: self::SPANISH_VAT_RATE,
                type: VatType::DOMESTIC,
                reverseCharge: false,
                reason: 'Domestic Spanish supply - IVA applicable'
            );
        }

        // Scenario 2: EU customer with valid VAT number (B2B)
        if ($this->viesService->isEuCountry($customerCountry) && $vatNumber) {
            $validation = $this->viesService->validate($vatNumber);

            if ($validation->valid) {
                return new VatDetermination(
                    rate: 0,
                    type: VatType::INTRA_EU_B2B,
                    reverseCharge: true,
                    reason: 'Intra-community B2B supply - Reverse charge applies',
                    vatValidation: $validation,
                    invoiceText: 'Reverse charge - Article 196 Council Directive 2006/112/EC'
                );
            }
        }

        // Scenario 3: EU customer without valid VAT (B2C or invalid VAT)
        if ($this->viesService->isEuCountry($customerCountry)) {
            return new VatDetermination(
                rate: self::SPANISH_VAT_RATE,
                type: VatType::INTRA_EU_B2C,
                reverseCharge: false,
                reason: 'Intra-community B2C supply or invalid VAT - Spanish IVA applicable'
            );
        }

        // Scenario 4: Non-EU customer (export)
        return new VatDetermination(
            rate: 0,
            type: VatType::EXPORT,
            reverseCharge: false,
            reason: 'Export outside EU - Outside scope of VAT'
        );
    }
}

enum VatType: string
{
    case DOMESTIC = 'domestic';           // Spanish customer
    case INTRA_EU_B2B = 'intra_eu_b2b';   // EU B2B with valid VAT
    case INTRA_EU_B2C = 'intra_eu_b2c';   // EU B2C or invalid VAT
    case EXPORT = 'export';               // Non-EU
}

class VatDetermination
{
    public function __construct(
        public float $rate,
        public VatType $type,
        public bool $reverseCharge,
        public string $reason,
        public ?ViesValidationResult $vatValidation = null,
        public ?string $invoiceText = null
    ) {}
}
```

### Database Changes for VAT Compliance

```php
// Add to tenant_billing_details migration
$table->string('billing_country_code', 2)->nullable(); // ISO country code
$table->boolean('vat_validated')->default(false);
$table->timestamp('vat_validated_at')->nullable();
$table->string('vat_validation_request_id')->nullable();
$table->string('vat_company_name_from_vies')->nullable();

// Add to invoices migration
$table->string('vat_type')->nullable(); // domestic, intra_eu_b2b, intra_eu_b2c, export
$table->boolean('reverse_charge')->default(false);
$table->string('vat_determination_reason')->nullable();
$table->string('customer_vat_number')->nullable();
$table->boolean('customer_vat_validated')->default(false);
$table->string('reverse_charge_text')->nullable();
```

### VAT Validation API Routes

```php
Route::prefix('vat')
    ->middleware(['auth'])
    ->group(function () {
        // Validate a VAT number
        Route::post('/validate', [VatValidationController::class, 'validate']);

        // Re-validate tenant's stored VAT number
        Route::post('/revalidate', [VatValidationController::class, 'revalidate']);
    });
```

### Invoice PDF Template (VAT Compliant)

```blade
{{-- resources/views/super-admin/invoices/pdf.blade.php --}}

{{-- Supplier Details --}}
<div class="supplier">
    <h2>{{ config('billing.company.name') }}</h2>
    <p>{{ config('billing.company.address.line1') }}</p>
    <p>{{ config('billing.company.address.city') }}, {{ config('billing.company.address.state') }}</p>
    <p>{{ config('billing.company.address.postal_code') }}, {{ config('billing.company.address.country') }}</p>
    <p><strong>Tax ID:</strong> {{ config('billing.company.tax_id') }}</p>
</div>

{{-- Customer Details --}}
<div class="customer">
    <h3>Bill To:</h3>
    <p>{{ $invoice->customer_name }}</p>
    <p>{{ $invoice->customer_address }}</p>
    @if($invoice->customer_vat_number)
        <p><strong>VAT Number:</strong> {{ $invoice->customer_vat_number }}
            @if($invoice->customer_vat_validated)
                <span class="validated">(Validated)</span>
            @endif
        </p>
    @endif
</div>

{{-- Invoice Lines --}}
<table class="items">
    <thead>
        <tr>
            <th>Description</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>VAT</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td>{{ $item->quantity }}</td>
            <td>€{{ number_format($item->unit_price, 2) }}</td>
            <td>{{ $item->tax_rate }}%</td>
            <td>€{{ number_format($item->line_total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div class="totals">
    <p>Subtotal: €{{ number_format($invoice->subtotal, 2) }}</p>
    @if($invoice->reverse_charge)
        <p><strong>VAT (0% - Reverse Charge): €0.00</strong></p>
    @else
        <p>VAT ({{ $invoice->tax_rate }}%): €{{ number_format($invoice->tax_amount, 2) }}</p>
    @endif
    <p class="total"><strong>Total: €{{ number_format($invoice->total, 2) }}</strong></p>
</div>

{{-- Reverse Charge Notice --}}
@if($invoice->reverse_charge)
<div class="reverse-charge-notice">
    <p><strong>{{ $invoice->reverse_charge_text }}</strong></p>
    <p><strong>The Client is responsible for tax payment to their local tax authority.</strong></p>
</div>
@endif

{{-- Footer --}}
<div class="footer">
    <p>{{ config('billing.company.name') }} | Tax ID: {{ config('billing.company.tax_id') }}</p>
</div>
```

### API Routes for Invoicing

```php
// Ad-hoc Invoicing (SuperAdmin)
Route::prefix('super-admin/invoices')
    ->middleware(['auth', 'super-admin'])
    ->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
        Route::put('/{invoice}', [InvoiceController::class, 'update']);
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy']);
        Route::post('/{invoice}/send', [InvoiceController::class, 'send']);
        Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment']);
        Route::post('/{invoice}/mark-paid', [InvoiceController::class, 'markPaid']);
        Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel']);
        Route::post('/{invoice}/credit-note', [InvoiceController::class, 'createCreditNote']);
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
        Route::get('/overdue', [InvoiceController::class, 'overdue']);
        Route::post('/overdue/remind-all', [InvoiceController::class, 'sendOverdueReminders']);
    });

// Tenant Billing Details
Route::prefix('billing-details')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [BillingDetailsController::class, 'show']);
        Route::put('/', [BillingDetailsController::class, 'update']);
        Route::post('/tax-certificate', [BillingDetailsController::class, 'uploadTaxCertificate']);
    });

// Tenant Invoice View (their own invoices)
Route::prefix('invoices')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [TenantInvoiceController::class, 'index']);
        Route::get('/{invoice}', [TenantInvoiceController::class, 'show']);
        Route::get('/{invoice}/pdf', [TenantInvoiceController::class, 'downloadPdf']);
    });
```

### Stripe Service

```php
class StripeService
{
    /**
     * Create Stripe customer for tenant
     */
    public function createCustomer(Tenant $tenant): StripeCustomer;

    /**
     * Create checkout session for subscription
     */
    public function createCheckoutSession(
        Tenant $tenant,
        string $priceId,
        string $successUrl,
        string $cancelUrl
    ): CheckoutSession;

    /**
     * Create subscription directly (if payment method exists)
     */
    public function createSubscription(
        Tenant $tenant,
        string $priceId,
        ?string $coupon = null
    ): Subscription;

    /**
     * Change subscription plan
     */
    public function changePlan(
        Subscription $subscription,
        string $newPriceId
    ): Subscription;

    /**
     * Cancel subscription
     */
    public function cancelSubscription(
        Subscription $subscription,
        bool $immediately = false
    ): Subscription;

    /**
     * Resume cancelled subscription
     */
    public function resumeSubscription(Subscription $subscription): Subscription;

    /**
     * Add feature add-on to subscription
     */
    public function addAddon(
        Subscription $subscription,
        string $addonPriceId
    ): SubscriptionItem;

    /**
     * Remove feature add-on from subscription
     */
    public function removeAddon(
        Subscription $subscription,
        string $addonPriceId
    ): void;

    /**
     * Update default payment method
     */
    public function updatePaymentMethod(
        Tenant $tenant,
        string $paymentMethodId
    ): void;

    /**
     * Get invoice history
     */
    public function getInvoices(Tenant $tenant, int $limit = 10): Collection;

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(string $invoiceId): string;
}
```

### Stripe Webhook Handler

```php
class StripeWebhookController extends Controller
{
    protected array $handlers = [
        'customer.subscription.created' => 'handleSubscriptionCreated',
        'customer.subscription.updated' => 'handleSubscriptionUpdated',
        'customer.subscription.deleted' => 'handleSubscriptionDeleted',
        'invoice.payment_succeeded' => 'handlePaymentSucceeded',
        'invoice.payment_failed' => 'handlePaymentFailed',
        'customer.updated' => 'handleCustomerUpdated',
    ];

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        $event = Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );

        $method = $this->handlers[$event->type] ?? null;

        if ($method && method_exists($this, $method)) {
            return $this->{$method}($event->data->object);
        }

        return response('Webhook handled', 200);
    }

    protected function handleSubscriptionUpdated($subscription)
    {
        $tenant = Tenant::whereHas('stripeCustomer', function ($q) use ($subscription) {
            $q->where('stripe_id', $subscription->customer);
        })->first();

        if ($tenant) {
            $tenant->subscription->update([
                'stripe_status' => $subscription->status,
                'stripe_price' => $subscription->items->data[0]->price->id ?? null,
                'ends_at' => $subscription->cancel_at
                    ? Carbon::createFromTimestamp($subscription->cancel_at)
                    : null,
            ]);

            // Update tenant feature access based on plan
            $this->syncTenantFeatures($tenant);
        }
    }

    protected function handlePaymentFailed($invoice)
    {
        $tenant = $this->findTenantByStripeCustomer($invoice->customer);

        if ($tenant) {
            // Send payment failed notification
            $tenant->notify(new PaymentFailedNotification($invoice));
        }
    }
}
```

### Stripe Price Configuration

```php
// config/stripe.php
return [
    'plans' => [
        'starter' => [
            'monthly' => env('STRIPE_STARTER_MONTHLY_PRICE'),
            'yearly' => env('STRIPE_STARTER_YEARLY_PRICE'),
            'features' => ['core_scheduling', 'basic_reports'],
        ],
        'professional' => [
            'monthly' => env('STRIPE_PROFESSIONAL_MONTHLY_PRICE'),
            'yearly' => env('STRIPE_PROFESSIONAL_YEARLY_PRICE'),
            'features' => ['core_scheduling', 'basic_reports', 'time_attendance', 'advanced_analytics'],
        ],
        'enterprise' => [
            'monthly' => env('STRIPE_ENTERPRISE_MONTHLY_PRICE'),
            'yearly' => env('STRIPE_ENTERPRISE_YEARLY_PRICE'),
            'features' => ['all'],
        ],
    ],
    'addons' => [
        'ai_scheduling' => env('STRIPE_AI_SCHEDULING_PRICE'),
        'advanced_geofencing' => env('STRIPE_ADVANCED_GEOFENCING_PRICE'),
        'payroll_integrations' => env('STRIPE_PAYROLL_INTEGRATIONS_PRICE'),
        'team_messaging' => env('STRIPE_TEAM_MESSAGING_PRICE'),
        'document_management' => env('STRIPE_DOCUMENT_MANAGEMENT_PRICE'),
    ],
    'trial_days' => 14,
    'grace_days' => 7,
];
```

### Platform Billing Configuration

```php
// config/billing.php
return [
    /*
    |--------------------------------------------------------------------------
    | Platform Company Details (Invoice Issuer)
    |--------------------------------------------------------------------------
    |
    | These details appear on all invoices issued by the platform.
    | They represent the legal entity operating Plannrly.
    |
    */
    'company' => [
        'name' => env('BILLING_COMPANY_NAME', 'Checketts Propiedad SL'),
        'tax_id' => env('BILLING_TAX_ID', 'ESB42691550'),
        'address' => [
            'line1' => env('BILLING_ADDRESS_LINE1', 'Calle Francisco Salzillo 9'),
            'line2' => env('BILLING_ADDRESS_LINE2', null),
            'city' => env('BILLING_CITY', 'Orihuela Costa'),
            'state' => env('BILLING_STATE', 'Alicante'),
            'postal_code' => env('BILLING_POSTAL_CODE', '03189'),
            'country' => env('BILLING_COUNTRY', 'Spain'),
            'country_code' => env('BILLING_COUNTRY_CODE', 'ES'),
        ],
        'email' => env('BILLING_EMAIL', 'billing@plannrly.com'),
        'phone' => env('BILLING_PHONE', null),
        'website' => env('BILLING_WEBSITE', 'https://plannrly.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        'logo_path' => env('BILLING_LOGO_PATH', 'images/plannrly-logo.png'),
        'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'EUR'),
        'default_tax_rate' => env('BILLING_DEFAULT_TAX_RATE', 21.00), // Spanish IVA
        'payment_terms' => env('BILLING_PAYMENT_TERMS', 'net_30'),
        'bank_details' => [
            'bank_name' => env('BILLING_BANK_NAME'),
            'iban' => env('BILLING_IBAN'),
            'bic' => env('BILLING_BIC'),
        ],
        'footer_text' => env('BILLING_FOOTER_TEXT', 'Thank you for your business.'),
    ],
];
```

**Environment Variables (.env):**
```
# Platform Billing Configuration
BILLING_COMPANY_NAME="Checketts Propiedad SL"
BILLING_TAX_ID="ESB42691550"
BILLING_ADDRESS_LINE1="Calle Francisco Salzillo 9"
BILLING_CITY="Orihuela Costa"
BILLING_STATE="Alicante"
BILLING_POSTAL_CODE="03189"
BILLING_COUNTRY="Spain"
BILLING_COUNTRY_CODE="ES"
BILLING_EMAIL="billing@plannrly.com"
BILLING_DEFAULT_CURRENCY="EUR"
BILLING_DEFAULT_TAX_RATE="21.00"
```

---

## 2.17 Schedule Print Service

```php
class SchedulePrintService
{
    /**
     * Generate print-optimized schedule data
     */
    public function generatePrintData(
        Carbon $startDate,
        Carbon $endDate,
        ?Location $location = null,
        ?Department $department = null,
        ?BusinessRole $role = null,
        array $options = []
    ): SchedulePrintData;

    /**
     * Generate PDF of schedule
     */
    public function generatePdf(
        SchedulePrintData $data,
        string $orientation = 'landscape'
    ): string;

    /**
     * Get print preview HTML
     */
    public function getPreviewHtml(SchedulePrintData $data): string;
}

class SchedulePrintData
{
    public function __construct(
        public Carbon $startDate,
        public Carbon $endDate,
        public Collection $shifts,
        public Collection $users,
        public Collection $departments,
        public array $options,
        public ?string $locationName,
        public ?string $departmentName,
        public array $stats,
    ) {}
}
```

### Print View Blade Component

```blade
{{-- resources/views/schedule/print.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Schedule - {{ $startDate->format('M j') }} to {{ $endDate->format('M j, Y') }}</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        .schedule-table th, .schedule-table td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        .schedule-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .shift-block {
            background: #e0e7ff;
            padding: 2px 4px;
            margin: 1px 0;
            border-radius: 2px;
            font-size: 9pt;
        }
        .employee-name { font-weight: bold; }
        .stats-footer {
            margin-top: 20px;
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <h2>Schedule: {{ $startDate->format('F j') }} - {{ $endDate->format('F j, Y') }}</h2>
        @if($locationName)<p>Location: {{ $locationName }}</p>@endif
        @if($departmentName)<p>Department: {{ $departmentName }}</p>@endif
    </div>

    <table class="schedule-table">
        <thead>
            <tr>
                <th>Employee</th>
                @foreach($weekDates as $date)
                <th>{{ $date->format('D n/j') }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td class="employee-name">{{ $user->name }}</td>
                @foreach($weekDates as $date)
                <td>
                    @foreach($shiftsLookup[$user->id][$date->format('Y-m-d')] ?? [] as $shift)
                    <div class="shift-block">
                        {{ $shift->start_time->format('g:ia') }}-{{ $shift->end_time->format('g:ia') }}
                    </div>
                    @endforeach
                </td>
                @endforeach
                <td>{{ $userWeeklyHours[$user->id] ?? 0 }}h</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="stats-footer">
        <p>Total Shifts: {{ $stats['totalShifts'] }} | Total Hours: {{ $stats['totalHours'] }} | Generated: {{ now()->format('M j, Y g:ia') }}</p>
    </div>
</body>
</html>
```

---

## 2.18 SuperAdmin Analytics Service

```php
class SuperAdminAnalyticsService
{
    /**
     * Get MRR (Monthly Recurring Revenue)
     */
    public function getMRR(): Money;

    /**
     * Get ARR (Annual Recurring Revenue)
     */
    public function getARR(): Money;

    /**
     * Get MRR history for charting
     */
    public function getMRRHistory(int $months = 12): Collection;

    /**
     * Get subscription counts by plan
     */
    public function getSubscriptionsByPlan(): array;

    /**
     * Get subscription growth metrics
     */
    public function getGrowthMetrics(Carbon $startDate, Carbon $endDate): GrowthMetrics;

    /**
     * Get churn metrics
     */
    public function getChurnMetrics(Carbon $startDate, Carbon $endDate): ChurnMetrics;

    /**
     * Get trial conversion rate
     */
    public function getTrialConversionRate(Carbon $startDate, Carbon $endDate): float;

    /**
     * Get revenue by feature add-on
     */
    public function getRevenueByAddon(): array;

    /**
     * Get tenant health scores
     */
    public function getTenantHealthScores(): Collection;

    /**
     * Get at-risk accounts (low activity, payment issues)
     */
    public function getAtRiskAccounts(): Collection;

    /**
     * Export analytics data
     */
    public function exportAnalytics(
        Carbon $startDate,
        Carbon $endDate,
        string $format = 'csv'
    ): string;
}

class GrowthMetrics
{
    public function __construct(
        public int $newSubscriptions,
        public int $upgrades,
        public int $downgrades,
        public int $cancellations,
        public int $reactivations,
        public int $trialSignups,
        public float $netGrowth,
    ) {}
}

class ChurnMetrics
{
    public function __construct(
        public float $churnRate,
        public int $churnedSubscriptions,
        public Money $churnedMRR,
        public array $churnReasons,
        public float $revenueRetention,
    ) {}
}
```

### Analytics Database Views/Queries

```sql
-- MRR Calculation
SELECT
    DATE_FORMAT(created_at, '%Y-%m') as month,
    SUM(CASE
        WHEN stripe_price LIKE '%monthly%' THEN price_amount
        WHEN stripe_price LIKE '%yearly%' THEN price_amount / 12
    END) as mrr
FROM subscriptions
WHERE stripe_status = 'active'
GROUP BY month
ORDER BY month;

-- Subscription Counts by Plan
SELECT
    CASE
        WHEN stripe_price LIKE '%starter%' THEN 'starter'
        WHEN stripe_price LIKE '%professional%' THEN 'professional'
        WHEN stripe_price LIKE '%enterprise%' THEN 'enterprise'
    END as plan,
    COUNT(*) as count
FROM subscriptions
WHERE stripe_status = 'active'
GROUP BY plan;

-- Churn Rate (Monthly)
SELECT
    (SELECT COUNT(*) FROM subscriptions WHERE ends_at BETWEEN :start AND :end) /
    (SELECT COUNT(*) FROM subscriptions WHERE created_at < :start AND (ends_at IS NULL OR ends_at > :start))
    * 100 as churn_rate;
```

---

## 2.19 Security Features

### Two-Factor Authentication Database

```php
Schema::create('two_factor_authentications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('secret')->nullable(); // Encrypted TOTP secret
    $table->text('recovery_codes')->nullable(); // Encrypted JSON array
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
});

Schema::create('trusted_devices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('device_hash');
    $table->string('device_name')->nullable();
    $table->string('browser')->nullable();
    $table->string('ip_address')->nullable();
    $table->timestamp('trusted_until');
    $table->timestamps();
});
```

### Session Management Database

```php
Schema::create('user_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('session_id')->unique();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->string('device_type')->nullable(); // desktop, mobile, tablet
    $table->string('browser')->nullable();
    $table->string('location')->nullable();
    $table->timestamp('last_activity_at');
    $table->boolean('is_current')->default(false);
    $table->timestamps();
});
```

### Audit Log Database

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('user_name')->nullable(); // Preserved if user deleted
    $table->string('action'); // created, updated, deleted, etc.
    $table->string('entity_type'); // Shift, User, LeaveRequest, etc.
    $table->unsignedBigInteger('entity_id')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['tenant_id', 'created_at']);
    $table->index(['entity_type', 'entity_id']);
});
```

### Two-Factor Authentication Service

```php
class TwoFactorAuthService
{
    public function enable(User $user): array
    {
        $secret = $this->generateSecret();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->twoFactorAuth()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => encrypt($secret),
                'recovery_codes' => encrypt(json_encode($recoveryCodes)),
                'confirmed_at' => null,
            ]
        );

        return [
            'secret' => $secret,
            'qr_code' => $this->generateQrCode($user, $secret),
            'recovery_codes' => $recoveryCodes,
        ];
    }

    public function verify(User $user, string $code): bool;
    public function confirm(User $user, string $code): bool;
    public function disable(User $user): void;
    public function useRecoveryCode(User $user, string $code): bool;
    public function trustDevice(User $user, Request $request, int $days = 30): void;
    public function isDeviceTrusted(User $user, Request $request): bool;
}
```

### Audit Log Service

```php
class AuditLogService
{
    public function log(
        string $action,
        Model $entity,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): AuditLog;

    public function query(): Builder
    {
        return AuditLog::where('tenant_id', auth()->user()->tenant_id);
    }

    public function forEntity(string $type, int $id): Collection;
    public function forUser(int $userId): Collection;
    public function export(array $filters): string; // Returns CSV
}

// Trait for automatic auditing
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => app(AuditLogService::class)->log('created', $model, null, $model->toArray()));
        static::updated(fn ($model) => app(AuditLogService::class)->log('updated', $model, $model->getOriginal(), $model->toArray()));
        static::deleted(fn ($model) => app(AuditLogService::class)->log('deleted', $model, $model->toArray(), null));
    }
}
```

---

## 2.20 GDPR Compliance

### Data Export Service

```php
class GdprDataExportService
{
    public function exportUserData(User $user): array
    {
        return [
            'profile' => $user->only(['first_name', 'last_name', 'email', 'phone', 'address']),
            'employment' => $user->employmentDetails?->toArray(),
            'availability' => $user->availabilities->toArray(),
            'shifts' => $user->shifts()->with('location', 'department')->get()->toArray(),
            'time_entries' => $user->timeEntries->toArray(),
            'leave_requests' => $user->leaveRequests->toArray(),
            'swap_requests' => $user->swapRequests->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];
    }

    public function generateExportFile(User $user, string $format = 'json'): string;
    public function scheduleExport(User $user): DataExportRequest;
}
```

### Data Deletion Service

```php
Schema::create('data_deletion_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('requested_by')->constrained('users');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->string('status'); // pending, approved, processing, completed, rejected
    $table->text('reason')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->json('data_to_retain')->nullable(); // Legal retention requirements
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});

class GdprDeletionService
{
    public function requestDeletion(User $user, string $reason): DataDeletionRequest;
    public function approveDeletion(DataDeletionRequest $request, User $approver): void;
    public function processDeletion(DataDeletionRequest $request): void;

    public function anonymizeUser(User $user): void
    {
        $user->update([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'email' => "deleted_{$user->id}@anonymized.local",
            'phone' => null,
            'address' => null,
            'avatar' => null,
        ]);

        // Preserve historical data with anonymized reference
        // Delete personal documents
        // Log deletion in audit trail
    }
}
```

---

## 2.21 Scheduling Enhancements

### Shift Preferences Database

```php
Schema::create('user_shift_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->tinyInteger('day_of_week'); // 0-6 (Sunday-Saturday)
    $table->time('preferred_start')->nullable();
    $table->time('preferred_end')->nullable();
    $table->enum('preference_level', ['strong', 'mild', 'neutral', 'avoid', 'cannot']);
    $table->timestamps();
});

Schema::create('user_coworker_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('coworker_id')->constrained('users')->cascadeOnDelete();
    $table->enum('preference', ['prefer', 'neutral', 'avoid']);
    $table->timestamps();
});
```

### Schedule Templates Database

```php
Schema::create('schedule_templates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('created_by')->constrained('users');
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('category')->nullable(); // summer, holiday, standard, etc.
    $table->boolean('is_shared')->default(false);
    $table->timestamps();
});

Schema::create('schedule_template_shifts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('template_id')->constrained('schedule_templates')->cascadeOnDelete();
    $table->tinyInteger('day_of_week'); // 0-6
    $table->time('start_time');
    $table->time('end_time');
    $table->foreignId('business_role_id')->constrained()->cascadeOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
    $table->integer('quantity')->default(1); // How many of this shift
    $table->timestamps();
});
```

### Open Shift Claims Database

```php
Schema::create('open_shift_claims', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('status'); // pending, approved, rejected, withdrawn
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamp('claimed_at');
    $table->timestamp('decided_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Shift Notes Database

```php
Schema::create('shift_notes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['instruction', 'handover', 'general']);
    $table->text('content');
    $table->boolean('is_visible_to_employee')->default(true);
    $table->timestamp('acknowledged_at')->nullable();
    $table->foreignId('acknowledged_by')->nullable()->constrained('users');
    $table->timestamps();
});

Schema::create('shift_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
    $table->string('task');
    $table->boolean('is_completed')->default(false);
    $table->foreignId('completed_by')->nullable()->constrained('users');
    $table->timestamp('completed_at')->nullable();
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

### Shift Notes Models

```php
class ShiftNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'user_id',
        'type',
        'content',
        'is_visible_to_employee',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => ShiftNoteType::class,
            'is_visible_to_employee' => 'boolean',
            'acknowledged_at' => 'datetime',
        ];
    }

    // Relationships
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    // Scopes
    public function scopeInstructions(Builder $query): Builder
    {
        return $query->where('type', ShiftNoteType::Instruction);
    }

    public function scopeHandovers(Builder $query): Builder
    {
        return $query->where('type', ShiftNoteType::Handover);
    }

    public function scopeVisibleToEmployee(Builder $query): Builder
    {
        return $query->where('is_visible_to_employee', true);
    }

    public function scopeUnacknowledged(Builder $query): Builder
    {
        return $query->whereNull('acknowledged_at');
    }

    // Methods
    public function acknowledge(User $user): void
    {
        $this->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $user->id,
        ]);
    }
}

enum ShiftNoteType: string
{
    case Instruction = 'instruction';
    case Handover = 'handover';
    case General = 'general';
}

class ShiftTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'task',
        'is_completed',
        'completed_by',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Scopes
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // Methods
    public function markComplete(User $user): void
    {
        $this->update([
            'is_completed' => true,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);
    }

    public function markIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);
    }
}
```

### Shift Notes Service

```php
class ShiftNoteService
{
    /**
     * Add instruction note to a shift (manager → employee)
     */
    public function addInstruction(Shift $shift, User $author, string $content): ShiftNote
    {
        return ShiftNote::create([
            'shift_id' => $shift->id,
            'user_id' => $author->id,
            'type' => ShiftNoteType::Instruction,
            'content' => $content,
            'is_visible_to_employee' => true,
        ]);
    }

    /**
     * Add handover note (employee → next shift)
     */
    public function addHandover(Shift $shift, User $author, string $content): ShiftNote
    {
        return ShiftNote::create([
            'shift_id' => $shift->id,
            'user_id' => $author->id,
            'type' => ShiftNoteType::Handover,
            'content' => $content,
            'is_visible_to_employee' => true,
        ]);
    }

    /**
     * Get handover notes from previous shift at same location/role
     */
    public function getPreviousHandover(Shift $shift): ?ShiftNote
    {
        $previousShift = Shift::where('location_id', $shift->location_id)
            ->where('business_role_id', $shift->business_role_id)
            ->where('date', '<', $shift->date)
            ->orWhere(function ($q) use ($shift) {
                $q->where('date', $shift->date)
                  ->where('end_time', '<=', $shift->start_time);
            })
            ->orderByDesc('date')
            ->orderByDesc('end_time')
            ->first();

        return $previousShift?->notes()->handovers()->latest()->first();
    }

    /**
     * Add task to shift checklist
     */
    public function addTask(Shift $shift, string $task, int $sortOrder = 0): ShiftTask
    {
        return ShiftTask::create([
            'shift_id' => $shift->id,
            'task' => $task,
            'sort_order' => $sortOrder ?: $shift->tasks()->count(),
        ]);
    }

    /**
     * Copy tasks from template or another shift
     */
    public function copyTasksFromShift(Shift $source, Shift $target): void
    {
        foreach ($source->tasks()->ordered()->get() as $task) {
            ShiftTask::create([
                'shift_id' => $target->id,
                'task' => $task->task,
                'sort_order' => $task->sort_order,
            ]);
        }
    }

    /**
     * Get task completion stats for a shift
     */
    public function getTaskStats(Shift $shift): array
    {
        $tasks = $shift->tasks;
        return [
            'total' => $tasks->count(),
            'completed' => $tasks->where('is_completed', true)->count(),
            'incomplete' => $tasks->where('is_completed', false)->count(),
            'completion_rate' => $tasks->count() > 0
                ? round($tasks->where('is_completed', true)->count() / $tasks->count() * 100)
                : 0,
        ];
    }
}
```

### Smart Fill Service

```php
class SmartFillService
{
    public function fillShifts(
        Collection $unassignedShifts,
        array $options = []
    ): SmartFillResult {
        $suggestions = [];

        foreach ($unassignedShifts as $shift) {
            $candidates = $this->findCandidates($shift);
            $ranked = $this->rankCandidates($candidates, $shift, $options);

            if ($ranked->isNotEmpty()) {
                $suggestions[] = new ShiftSuggestion(
                    shift: $shift,
                    suggestedUser: $ranked->first(),
                    alternatives: $ranked->skip(1)->take(3),
                    score: $this->calculateScore($ranked->first(), $shift),
                    warnings: $this->checkWarnings($ranked->first(), $shift)
                );
            }
        }

        return new SmartFillResult($suggestions);
    }

    private function findCandidates(Shift $shift): Collection
    {
        return User::query()
            ->whereHas('businessRoles', fn ($q) => $q->where('business_role_id', $shift->business_role_id))
            ->where('is_active', true)
            ->whereDoesntHave('shifts', fn ($q) => $q->whereDate('date', $shift->date)
                ->where(fn ($q2) => $q2
                    ->whereBetween('start_time', [$shift->start_time, $shift->end_time])
                    ->orWhereBetween('end_time', [$shift->start_time, $shift->end_time])
                )
            )
            ->whereDoesntHave('leaveRequests', fn ($q) => $q
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $shift->date)
                ->whereDate('end_date', '>=', $shift->date)
            )
            ->get();
    }

    private function rankCandidates(Collection $candidates, Shift $shift, array $options): Collection;
    private function calculateScore(User $user, Shift $shift): float;
    private function checkWarnings(User $user, Shift $shift): array;
}
```

### Open Shift Service

```php
class OpenShiftService
{
    public function getAvailableShifts(User $user): Collection
    {
        $roleIds = $user->businessRoles->pluck('id');

        return Shift::query()
            ->whereNull('user_id')
            ->where('status', ShiftStatus::Published)
            ->whereIn('business_role_id', $roleIds)
            ->whereDate('date', '>=', today())
            ->whereDoesntHave('claims', fn ($q) => $q->where('user_id', $user->id))
            ->with(['location', 'department', 'businessRole'])
            ->orderBy('date')
            ->get();
    }

    public function claim(Shift $shift, User $user): OpenShiftClaim;
    public function approve(OpenShiftClaim $claim, User $approver): void;
    public function reject(OpenShiftClaim $claim, User $approver, string $reason): void;
    public function autoAssignAfterDeadline(Shift $shift): ?User;
}
```

### Calendar Integration Database

```php
Schema::create('calendar_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name'); // "My Work Schedule", "Team Calendar"
    $table->string('type'); // personal, team, location, department
    $table->string('token', 64)->unique(); // Secure random token for URL
    $table->json('filters')->nullable(); // location_id, department_id, etc.
    $table->boolean('include_drafts')->default(false);
    $table->boolean('include_open_shifts')->default(false);
    $table->boolean('include_leave')->default(true);
    $table->timestamp('last_accessed_at')->nullable();
    $table->integer('access_count')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['token']);
    $table->index(['user_id', 'type']);
});

Schema::create('external_calendar_syncs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('provider'); // google, outlook, apple
    $table->string('calendar_id'); // External calendar identifier
    $table->string('calendar_name');
    $table->text('access_token')->nullable(); // Encrypted
    $table->text('refresh_token')->nullable(); // Encrypted
    $table->timestamp('token_expires_at')->nullable();
    $table->string('sync_direction'); // export_only, import_only, bidirectional
    $table->json('sync_settings')->nullable();
    $table->timestamp('last_synced_at')->nullable();
    $table->string('sync_status')->default('pending'); // pending, syncing, synced, error
    $table->text('last_error')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'provider', 'calendar_id']);
});
```

### Calendar Subscription Model

```php
class CalendarSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'token',
        'filters',
        'include_drafts',
        'include_open_shifts',
        'include_leave',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'include_drafts' => 'boolean',
            'include_open_shifts' => 'boolean',
            'include_leave' => 'boolean',
            'is_active' => 'boolean',
            'last_accessed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CalendarSubscription $subscription) {
            $subscription->token = $subscription->token ?? Str::random(64);
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    public function getUrl(): string
    {
        return route('calendar.ical', ['token' => $this->token]);
    }

    public function recordAccess(): void
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function regenerateToken(): void
    {
        $this->update(['token' => Str::random(64)]);
    }
}

enum CalendarSubscriptionType: string
{
    case Personal = 'personal';       // User's own shifts
    case Team = 'team';               // All shifts user can see
    case Location = 'location';       // Specific location
    case Department = 'department';   // Specific department
}
```

### Calendar Service

```php
class CalendarService
{
    /**
     * Generate iCal feed for a subscription
     */
    public function generateIcalFeed(CalendarSubscription $subscription): string
    {
        $subscription->recordAccess();

        $events = $this->getEventsForSubscription($subscription);

        $calendar = new ICalendar();
        $calendar->setName($subscription->name);
        $calendar->setDescription("Plannrly Schedule - {$subscription->name}");

        foreach ($events as $event) {
            $calendar->addEvent($this->shiftToEvent($event));
        }

        return $calendar->render();
    }

    /**
     * Get shifts based on subscription filters
     */
    private function getEventsForSubscription(CalendarSubscription $subscription): Collection
    {
        $query = Shift::with(['user', 'location', 'department', 'businessRole'])
            ->where('date', '>=', now()->subMonths(1))
            ->where('date', '<=', now()->addMonths(3));

        // Apply type-based filtering
        switch ($subscription->type) {
            case 'personal':
                $query->where('user_id', $subscription->user_id);
                break;
            case 'location':
                $query->where('location_id', $subscription->filters['location_id'] ?? null);
                break;
            case 'department':
                $query->where('department_id', $subscription->filters['department_id'] ?? null);
                break;
            case 'team':
                $query->visibleToUser($subscription->user);
                break;
        }

        // Apply additional filters
        if (!$subscription->include_drafts) {
            $query->where('status', '!=', ShiftStatus::Draft);
        }

        $shifts = $query->get();

        // Include open shifts if requested
        if ($subscription->include_open_shifts) {
            $openShifts = Shift::whereNull('user_id')
                ->where('status', ShiftStatus::Published)
                ->where('date', '>=', now())
                ->where('date', '<=', now()->addMonths(3))
                ->get();
            $shifts = $shifts->merge($openShifts);
        }

        // Include leave if requested
        if ($subscription->include_leave && $subscription->type === 'personal') {
            // Leave requests will be converted to events separately
        }

        return $shifts;
    }

    /**
     * Convert shift to iCal event
     */
    private function shiftToEvent(Shift $shift): ICalEvent
    {
        $startDateTime = Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->start_time->format('H:i'));
        $endDateTime = Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->end_time->format('H:i'));

        // Handle overnight shifts
        if ($endDateTime <= $startDateTime) {
            $endDateTime->addDay();
        }

        return new ICalEvent([
            'uid' => "shift-{$shift->id}@plannrly.com",
            'summary' => $shift->user_id
                ? "{$shift->businessRole->name} at {$shift->location->name}"
                : "[OPEN] {$shift->businessRole->name} at {$shift->location->name}",
            'description' => $this->buildShiftDescription($shift),
            'location' => $shift->location->full_address,
            'start' => $startDateTime,
            'end' => $endDateTime,
            'status' => $shift->status === ShiftStatus::Published ? 'CONFIRMED' : 'TENTATIVE',
        ]);
    }

    /**
     * Create a new calendar subscription
     */
    public function createSubscription(
        User $user,
        string $name,
        string $type,
        array $options = []
    ): CalendarSubscription {
        return CalendarSubscription::create([
            'user_id' => $user->id,
            'name' => $name,
            'type' => $type,
            'filters' => $options['filters'] ?? null,
            'include_drafts' => $options['include_drafts'] ?? false,
            'include_open_shifts' => $options['include_open_shifts'] ?? false,
            'include_leave' => $options['include_leave'] ?? true,
        ]);
    }

    /**
     * Sync with external calendar provider (Google, Outlook)
     */
    public function syncExternalCalendar(ExternalCalendarSync $sync): void
    {
        // Implementation depends on provider OAuth integration
    }
}
```

### Calendar API Routes

```php
// Public iCal feed (authenticated via token in URL)
Route::get('/calendar/ical/{token}', [CalendarController::class, 'ical'])
    ->name('calendar.ical');

// Calendar subscription management
Route::prefix('calendar')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/subscriptions', [CalendarSubscriptionController::class, 'index']);
        Route::post('/subscriptions', [CalendarSubscriptionController::class, 'store']);
        Route::delete('/subscriptions/{subscription}', [CalendarSubscriptionController::class, 'destroy']);
        Route::post('/subscriptions/{subscription}/regenerate-token', [CalendarSubscriptionController::class, 'regenerateToken']);

        // External calendar sync
        Route::get('/external', [ExternalCalendarController::class, 'index']);
        Route::post('/external/connect/{provider}', [ExternalCalendarController::class, 'connect']);
        Route::delete('/external/{sync}', [ExternalCalendarController::class, 'disconnect']);
        Route::post('/external/{sync}/sync', [ExternalCalendarController::class, 'sync']);
    });
```

### Conflict Detection Database

```php
Schema::create('schedule_conflicts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('conflict_type'); // double_booking, leave_overlap, rest_period, overtime, qualification, availability
    $table->string('severity'); // error, warning, info
    $table->text('description');
    $table->json('conflict_data'); // Details about conflicting items
    $table->boolean('is_resolved')->default(false);
    $table->boolean('is_overridden')->default(false);
    $table->foreignId('overridden_by')->nullable()->constrained('users');
    $table->timestamp('overridden_at')->nullable();
    $table->text('override_reason')->nullable();
    $table->timestamps();

    $table->index(['shift_id', 'conflict_type']);
    $table->index(['tenant_id', 'is_resolved']);
});

Schema::create('conflict_overrides', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('conflict_id')->constrained('schedule_conflicts')->cascadeOnDelete();
    $table->foreignId('approved_by')->constrained('users');
    $table->text('reason');
    $table->json('original_data'); // Snapshot of conflict at override time
    $table->timestamps();
});
```

### Conflict Detection Service

```php
class ConflictDetectionService
{
    /**
     * Check all conflicts for a shift assignment
     */
    public function detectConflicts(Shift $shift, ?User $user = null): ConflictResult
    {
        $conflicts = collect();
        $user = $user ?? $shift->user;

        if (!$user) {
            return new ConflictResult($conflicts);
        }

        // Check for double booking
        $conflicts = $conflicts->merge($this->checkDoubleBooking($shift, $user));

        // Check for leave overlap
        $conflicts = $conflicts->merge($this->checkLeaveOverlap($shift, $user));

        // Check availability conflicts
        $conflicts = $conflicts->merge($this->checkAvailabilityConflict($shift, $user));

        // Check rest period violations (EU compliance - warning only)
        $conflicts = $conflicts->merge($this->checkRestPeriod($shift, $user));

        // Check overtime warnings
        $conflicts = $conflicts->merge($this->checkOvertimeWarning($shift, $user));

        // Check qualification gaps
        $conflicts = $conflicts->merge($this->checkQualificationGap($shift, $user));

        return new ConflictResult($conflicts);
    }

    /**
     * Check if user already has a shift overlapping this time
     */
    private function checkDoubleBooking(Shift $shift, User $user): Collection
    {
        $overlapping = Shift::where('user_id', $user->id)
            ->where('id', '!=', $shift->id)
            ->whereDate('date', $shift->date)
            ->where(function ($query) use ($shift) {
                $query->whereBetween('start_time', [$shift->start_time, $shift->end_time])
                    ->orWhereBetween('end_time', [$shift->start_time, $shift->end_time])
                    ->orWhere(function ($q) use ($shift) {
                        $q->where('start_time', '<=', $shift->start_time)
                          ->where('end_time', '>=', $shift->end_time);
                    });
            })
            ->get();

        return $overlapping->map(fn ($existing) => new Conflict(
            type: ConflictType::DoubleBooking,
            severity: ConflictSeverity::Error,
            description: "User already scheduled for shift at {$existing->location->name}",
            data: ['conflicting_shift_id' => $existing->id]
        ));
    }

    /**
     * Check if shift falls during approved leave
     */
    private function checkLeaveOverlap(Shift $shift, User $user): Collection
    {
        $leave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $shift->date)
            ->where('end_date', '>=', $shift->date)
            ->first();

        if ($leave) {
            return collect([new Conflict(
                type: ConflictType::LeaveOverlap,
                severity: ConflictSeverity::Error,
                description: "User on approved {$leave->leaveType->name} leave",
                data: ['leave_request_id' => $leave->id]
            )]);
        }

        return collect();
    }

    /**
     * Check if user marked as unavailable
     */
    private function checkAvailabilityConflict(Shift $shift, User $user): Collection
    {
        $unavailable = UserAvailability::where('user_id', $user->id)
            ->where('date', $shift->date)
            ->where('is_available', false)
            ->first();

        if ($unavailable) {
            return collect([new Conflict(
                type: ConflictType::Availability,
                severity: ConflictSeverity::Warning,
                description: "User marked as unavailable: {$unavailable->reason}",
                data: ['availability_id' => $unavailable->id]
            )]);
        }

        return collect();
    }

    /**
     * Check EU minimum rest period (11 hours between shifts)
     */
    private function checkRestPeriod(Shift $shift, User $user): Collection
    {
        $previousShift = Shift::where('user_id', $user->id)
            ->where('date', $shift->date->copy()->subDay())
            ->orderByDesc('end_time')
            ->first();

        if ($previousShift) {
            $restHours = Carbon::parse($previousShift->date->format('Y-m-d') . ' ' . $previousShift->end_time->format('H:i'))
                ->diffInHours(Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->start_time->format('H:i')));

            if ($restHours < 11) {
                return collect([new Conflict(
                    type: ConflictType::RestPeriod,
                    severity: ConflictSeverity::Warning,
                    description: "Only {$restHours}h rest (EU minimum: 11h)",
                    data: ['previous_shift_id' => $previousShift->id, 'rest_hours' => $restHours]
                )]);
            }
        }

        return collect();
    }

    /**
     * Check weekly hours approaching overtime threshold
     */
    private function checkOvertimeWarning(Shift $shift, User $user): Collection
    {
        $weekStart = $shift->date->copy()->startOfWeek();
        $weekEnd = $shift->date->copy()->endOfWeek();

        $weeklyHours = Shift::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->where('id', '!=', $shift->id)
            ->get()
            ->sum(fn ($s) => $s->duration_hours);

        $projectedHours = $weeklyHours + $shift->duration_hours;
        $maxHours = $user->userEmploymentDetails?->target_hours_per_week ?? 40;

        if ($projectedHours > $maxHours) {
            return collect([new Conflict(
                type: ConflictType::Overtime,
                severity: ConflictSeverity::Warning,
                description: "Projected {$projectedHours}h exceeds contracted {$maxHours}h",
                data: ['weekly_hours' => $weeklyHours, 'projected_hours' => $projectedHours]
            )]);
        }

        return collect();
    }

    /**
     * Check if user has required qualification for the role
     */
    private function checkQualificationGap(Shift $shift, User $user): Collection
    {
        $hasRole = $user->businessRoles()
            ->where('business_role_id', $shift->business_role_id)
            ->exists();

        if (!$hasRole) {
            return collect([new Conflict(
                type: ConflictType::Qualification,
                severity: ConflictSeverity::Warning,
                description: "User not assigned to role: {$shift->businessRole->name}",
                data: ['required_role_id' => $shift->business_role_id]
            )]);
        }

        return collect();
    }

    /**
     * Override a conflict with reason
     */
    public function overrideConflict(ScheduleConflict $conflict, User $approver, string $reason): void
    {
        ConflictOverride::create([
            'tenant_id' => $conflict->tenant_id,
            'conflict_id' => $conflict->id,
            'approved_by' => $approver->id,
            'reason' => $reason,
            'original_data' => $conflict->toArray(),
        ]);

        $conflict->update([
            'is_overridden' => true,
            'overridden_by' => $approver->id,
            'overridden_at' => now(),
            'override_reason' => $reason,
        ]);
    }
}

enum ConflictType: string
{
    case DoubleBooking = 'double_booking';
    case LeaveOverlap = 'leave_overlap';
    case Availability = 'availability';
    case RestPeriod = 'rest_period';
    case Overtime = 'overtime';
    case Qualification = 'qualification';
}

enum ConflictSeverity: string
{
    case Error = 'error';     // Blocks assignment
    case Warning = 'warning'; // Allows with override
    case Info = 'info';       // Informational only
}

class ConflictResult
{
    public function __construct(
        public Collection $conflicts
    ) {}

    public function hasErrors(): bool
    {
        return $this->conflicts->contains(fn ($c) => $c->severity === ConflictSeverity::Error);
    }

    public function hasWarnings(): bool
    {
        return $this->conflicts->contains(fn ($c) => $c->severity === ConflictSeverity::Warning);
    }

    public function isClean(): bool
    {
        return $this->conflicts->isEmpty();
    }
}
```

### Conflict Detection API

```php
// ShiftController integration
public function store(StoreShiftRequest $request): JsonResponse
{
    $shift = new Shift($request->validated());

    if ($request->user_id) {
        $user = User::findOrFail($request->user_id);
        $conflicts = app(ConflictDetectionService::class)->detectConflicts($shift, $user);

        if ($conflicts->hasErrors()) {
            return response()->json([
                'message' => 'Cannot assign shift due to conflicts',
                'conflicts' => $conflicts->conflicts,
            ], 422);
        }

        if ($conflicts->hasWarnings() && !$request->boolean('acknowledge_warnings')) {
            return response()->json([
                'message' => 'Shift has warnings that require acknowledgement',
                'conflicts' => $conflicts->conflicts,
                'requires_acknowledgement' => true,
            ], 422);
        }
    }

    $shift->save();

    // Store any acknowledged warnings
    if ($conflicts?->hasWarnings()) {
        foreach ($conflicts->conflicts as $conflict) {
            ScheduleConflict::create([
                'tenant_id' => $shift->tenant_id,
                'shift_id' => $shift->id,
                'user_id' => $shift->user_id,
                'conflict_type' => $conflict->type->value,
                'severity' => $conflict->severity->value,
                'description' => $conflict->description,
                'conflict_data' => $conflict->data,
                'is_overridden' => true,
                'overridden_by' => auth()->id(),
                'overridden_at' => now(),
            ]);
        }
    }

    return response()->json($shift, 201);
}
```

---

## 2.22 Working Time Compliance

### Compliance Check Service

```php
class WorkingTimeComplianceService
{
    private const EU_MIN_REST_HOURS = 11;
    private const EU_MAX_WEEKLY_HOURS = 48;
    private const EU_WEEKLY_REST_HOURS = 24;
    private const EU_BREAK_AFTER_HOURS = 6;
    private const EU_MIN_BREAK_MINUTES = 20;

    public function checkShift(Shift $shift, User $user): ComplianceResult
    {
        $violations = [];

        // Check minimum rest period
        $previousShift = $this->getPreviousShift($user, $shift->date);
        if ($previousShift) {
            $restHours = $previousShift->end_time->diffInHours($shift->start_time);
            if ($restHours < self::EU_MIN_REST_HOURS) {
                $violations[] = new ComplianceViolation(
                    type: 'insufficient_rest',
                    severity: 'warning',
                    message: "Only {$restHours}h rest between shifts (minimum 11h required)",
                    rule: 'EU Working Time Directive Article 3'
                );
            }
        }

        // Check weekly hours
        $weeklyHours = $this->calculateWeeklyHours($user, $shift->date);
        if ($weeklyHours + $shift->duration_hours > self::EU_MAX_WEEKLY_HOURS) {
            $violations[] = new ComplianceViolation(
                type: 'excessive_weekly_hours',
                severity: 'warning',
                message: "Would exceed 48h/week ({$weeklyHours}h + {$shift->duration_hours}h)",
                rule: 'EU Working Time Directive Article 6'
            );
        }

        // Check weekly rest
        // Check break requirements

        return new ComplianceResult($violations);
    }

    public function generateComplianceReport(
        int $tenantId,
        Carbon $startDate,
        Carbon $endDate
    ): ComplianceReport;
}

class ComplianceViolation
{
    public function __construct(
        public string $type,
        public string $severity, // info, warning, error
        public string $message,
        public string $rule
    ) {}
}
```

### Compliance Violation Logging

```php
Schema::create('compliance_violations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('violation_type');
    $table->string('severity');
    $table->text('message');
    $table->string('rule_reference');
    $table->boolean('was_acknowledged')->default(false);
    $table->foreignId('acknowledged_by')->nullable()->constrained('users');
    $table->timestamp('acknowledged_at')->nullable();
    $table->text('acknowledgment_reason')->nullable();
    $table->timestamps();
});
```

---

## 2.23 Webhook System

### Webhook Database

```php
Schema::create('webhooks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('url');
    $table->string('secret'); // For HMAC signature
    $table->json('events'); // Array of subscribed events
    $table->boolean('is_active')->default(true);
    $table->integer('failure_count')->default(0);
    $table->timestamp('last_triggered_at')->nullable();
    $table->timestamp('disabled_at')->nullable();
    $table->timestamps();
});

Schema::create('webhook_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
    $table->string('event');
    $table->json('payload');
    $table->integer('response_status')->nullable();
    $table->text('response_body')->nullable();
    $table->integer('attempt_count')->default(1);
    $table->timestamp('delivered_at')->nullable();
    $table->timestamp('next_retry_at')->nullable();
    $table->timestamps();
});
```

### Webhook Service

```php
class WebhookService
{
    private const MAX_RETRIES = 5;
    private const RETRY_DELAYS = [60, 300, 900, 3600, 86400]; // seconds

    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            WebhookDeliveryJob::dispatch($webhook, $event, $payload);
        }
    }

    public function deliver(Webhook $webhook, string $event, array $payload): WebhookDelivery
    {
        $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

        $response = Http::timeout(30)
            ->withHeaders([
                'X-Plannrly-Event' => $event,
                'X-Plannrly-Signature' => $signature,
                'X-Plannrly-Delivery' => Str::uuid(),
            ])
            ->post($webhook->url, $payload);

        return WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
            'response_status' => $response->status(),
            'response_body' => Str::limit($response->body(), 1000),
            'delivered_at' => $response->successful() ? now() : null,
        ]);
    }

    public function getAvailableEvents(): array
    {
        return [
            'shift.created',
            'shift.updated',
            'shift.deleted',
            'schedule.published',
            'employee.clocked_in',
            'employee.clocked_out',
            'leave.requested',
            'leave.approved',
            'leave.rejected',
            'swap.requested',
            'swap.approved',
            'swap.rejected',
            'employee.created',
            'employee.updated',
        ];
    }
}
```

---

## 2.24 Onboarding & Import

### Onboarding Progress Database

```php
Schema::create('onboarding_progress', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->json('completed_steps'); // Array of completed step names
    $table->boolean('is_complete')->default(false);
    $table->boolean('is_dismissed')->default(false);
    $table->boolean('used_sample_data')->default(false);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

### Onboarding Service

```php
class OnboardingService
{
    private const STEPS = [
        'organization_details',
        'first_location',
        'departments',
        'business_roles',
        'invite_team',
        'first_schedule',
    ];

    public function getProgress(Tenant $tenant): OnboardingProgress;
    public function completeStep(Tenant $tenant, string $step): void;
    public function loadSampleData(Tenant $tenant): void;
    public function clearSampleData(Tenant $tenant): void;
    public function dismiss(Tenant $tenant): void;
}
```

### Import Service

```php
class DataImportService
{
    public function importEmployees(UploadedFile $file, array $mapping): ImportResult
    {
        $rows = $this->parseFile($file);
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $data = $this->mapRow($row, $mapping);
                $this->validateRow($data);
                User::create($data);
                $imported++;
            } catch (ValidationException $e) {
                $errors[] = new ImportError($index + 2, $e->errors());
            }
        }

        return new ImportResult($imported, $errors);
    }

    public function getCompetitorImporter(string $competitor): CompetitorImporter
    {
        return match ($competitor) {
            'deputy' => new DeputyImporter(),
            'when_i_work' => new WhenIWorkImporter(),
            '7shifts' => new SevenShiftsImporter(),
            'homebase' => new HomebaseImporter(),
            'sling' => new SlingImporter(),
            default => throw new InvalidArgumentException("Unknown competitor: {$competitor}"),
        };
    }
}

interface CompetitorImporter
{
    public function import(UploadedFile $file, Tenant $tenant): ImportResult;
    public function getSupportedFileTypes(): array;
    public function getRequiredFields(): array;
}
```

---

## 2.25 Schedule Fairness Analytics

### Fairness Database Tables

```php
Schema::create('fairness_scores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->date('period_start');
    $table->date('period_end');
    $table->decimal('overall_score', 5, 2); // 0-100
    $table->decimal('weekend_score', 5, 2);
    $table->decimal('holiday_score', 5, 2);
    $table->decimal('hours_variance_score', 5, 2);
    $table->decimal('preference_score', 5, 2);
    $table->decimal('short_notice_score', 5, 2);
    $table->integer('weekends_worked');
    $table->integer('holidays_worked');
    $table->decimal('hours_variance', 5, 2); // Deviation from target
    $table->integer('short_notice_changes');
    $table->timestamps();

    $table->index(['tenant_id', 'period_start', 'period_end']);
    $table->unique(['user_id', 'period_start', 'period_end']);
});

Schema::create('fairness_alerts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
    $table->string('alert_type'); // weekend_imbalance, hours_variance, preference_ignored
    $table->string('severity'); // info, warning, critical
    $table->text('message');
    $table->decimal('current_score', 5, 2);
    $table->decimal('team_average_score', 5, 2)->nullable();
    $table->boolean('is_acknowledged')->default(false);
    $table->foreignId('acknowledged_by')->nullable()->constrained('users');
    $table->timestamp('acknowledged_at')->nullable();
    $table->timestamps();
});
```

### Fairness Analytics Service

```php
class FairnessAnalyticsService
{
    public function calculateFairnessScore(User $user, Carbon $startDate, Carbon $endDate): FairnessScore
    {
        $metrics = [
            'weekend_fairness' => $this->calculateWeekendFairness($user, $startDate, $endDate),
            'holiday_fairness' => $this->calculateHolidayFairness($user, $startDate, $endDate),
            'hours_variance' => $this->calculateHoursVariance($user, $startDate, $endDate),
            'preference_satisfaction' => $this->calculatePreferenceSatisfaction($user, $startDate, $endDate),
            'short_notice_changes' => $this->calculateShortNoticeImpact($user, $startDate, $endDate),
        ];

        $overallScore = collect($metrics)->average();

        return FairnessScore::updateOrCreate(
            ['user_id' => $user->id, 'period_start' => $startDate, 'period_end' => $endDate],
            [
                'tenant_id' => $user->tenant_id,
                'overall_score' => $overallScore,
                'weekend_score' => $metrics['weekend_fairness'],
                'holiday_score' => $metrics['holiday_fairness'],
                'hours_variance_score' => $metrics['hours_variance'],
                'preference_score' => $metrics['preference_satisfaction'],
                'short_notice_score' => $metrics['short_notice_changes'],
                // ... additional metrics
            ]
        );
    }

    private function calculateWeekendFairness(User $user, Carbon $start, Carbon $end): float
    {
        $userWeekends = $this->countWeekendsWorked($user, $start, $end);
        $teamAverage = $this->getTeamAverageWeekends($user->tenant_id, $start, $end);

        // Score based on deviation from team average (100 = fair, lower = unfair)
        $deviation = abs($userWeekends - $teamAverage);
        return max(0, 100 - ($deviation * 20));
    }

    public function getTeamFairnessReport(int $departmentId, Carbon $startDate, Carbon $endDate): array;
    public function identifyUnfairPatterns(int $tenantId): array;
    public function createFairnessAlert(User $user, Shift $shift, string $type, string $message): FairnessAlert;
    public function checkScheduleForFairness(Collection $shifts): array; // Returns potential alerts
}
```

---

## 2.26 Predictive Absence Analytics

### Absence Analytics Database Tables

```php
Schema::create('absence_patterns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('pattern_type'); // monday_friday, pre_holiday, post_holiday, seasonal, weather
    $table->decimal('confidence', 5, 2); // 0-100 confidence score
    $table->json('pattern_data'); // Specific pattern details
    $table->date('detected_at');
    $table->date('last_occurrence')->nullable();
    $table->integer('occurrence_count')->default(1);
    $table->timestamps();

    $table->index(['tenant_id', 'user_id', 'pattern_type']);
});

Schema::create('absence_risk_assessments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->decimal('risk_score', 5, 2); // 0-100
    $table->string('risk_level'); // low, medium, high, critical
    $table->json('contributing_factors'); // Array of factors and weights
    $table->json('recommendations')->nullable(); // Suggested interventions
    $table->date('assessment_date');
    $table->date('valid_until');
    $table->timestamps();

    $table->index(['tenant_id', 'risk_level']);
    $table->unique(['user_id', 'assessment_date']);
});
```

### Absence Analytics Service

```php
class AbsenceAnalyticsService
{
    public function detectPatterns(User $user): array
    {
        $absences = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '>=', now()->subYear())
            ->get();

        return [
            'monday_friday_pattern' => $this->detectMondayFridayPattern($absences),
            'pre_holiday_pattern' => $this->detectPreHolidayPattern($absences),
            'seasonal_pattern' => $this->detectSeasonalPattern($absences),
            'trend' => $this->calculateTrend($absences),
        ];
    }

    public function getRiskIndicators(int $tenantId): Collection
    {
        return User::where('tenant_id', $tenantId)
            ->get()
            ->map(fn ($user) => [
                'user' => $user,
                'risk_score' => $this->calculateRiskScore($user),
                'patterns' => $this->detectPatterns($user),
            ])
            ->filter(fn ($item) => $item['risk_score'] > 0.5)
            ->sortByDesc('risk_score');
    }
}
```

---

## 2.27 Real-Time Operations Dashboard

### Operations Dashboard Service

```php
class OperationsDashboardService
{
    /**
     * Get employees currently clocked in
     */
    public function getCurrentlyWorking(int $tenantId, ?int $locationId = null): Collection
    {
        $query = TimeEntry::with(['user', 'user.businessRoles', 'shift', 'shift.location'])
            ->whereHas('user', fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereNull('clock_out')
            ->whereDate('clock_in', today());

        if ($locationId) {
            $query->whereHas('shift', fn ($q) => $q->where('location_id', $locationId));
        }

        return $query->get()->map(fn ($entry) => [
            'user' => $entry->user,
            'shift' => $entry->shift,
            'clock_in' => $entry->clock_in,
            'time_since_clock_in' => $entry->clock_in->diffForHumans(),
            'expected_clock_out' => $entry->shift?->end_time,
            'location' => $entry->shift?->location,
            'role' => $entry->user->businessRoles->first(),
        ]);
    }

    /**
     * Get employees expected to arrive in next hours
     */
    public function getExpectedArrivals(
        int $tenantId,
        int $hoursAhead = 2,
        ?int $locationId = null
    ): Collection {
        $now = now();
        $cutoff = $now->copy()->addHours($hoursAhead);

        $query = Shift::with(['user', 'location', 'businessRole'])
            ->where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->whereNotNull('user_id')
            ->whereTime('start_time', '>=', $now->format('H:i:s'))
            ->whereTime('start_time', '<=', $cutoff->format('H:i:s'))
            ->whereDoesntHave('timeEntries', fn ($q) => $q->whereDate('clock_in', today()));

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()->map(fn ($shift) => [
            'user' => $shift->user,
            'shift' => $shift,
            'start_time' => $shift->start_time,
            'countdown' => Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->start_time->format('H:i'))
                ->diffForHumans(['parts' => 2]),
            'location' => $shift->location,
            'role' => $shift->businessRole,
        ]);
    }

    /**
     * Get employees who should be clocked in but aren't (late/missing)
     */
    public function getMissingLate(
        int $tenantId,
        int $graceMinutes = 15,
        ?int $locationId = null
    ): Collection {
        $now = now();
        $graceCutoff = $now->copy()->subMinutes($graceMinutes);

        $query = Shift::with(['user', 'location', 'businessRole'])
            ->where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->whereNotNull('user_id')
            ->whereTime('start_time', '<=', $graceCutoff->format('H:i:s'))
            ->whereDoesntHave('timeEntries', fn ($q) => $q->whereDate('clock_in', today()));

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()->map(fn ($shift) => [
            'user' => $shift->user,
            'shift' => $shift,
            'start_time' => $shift->start_time,
            'minutes_late' => Carbon::parse($shift->date->format('Y-m-d') . ' ' . $shift->start_time->format('H:i'))
                ->diffInMinutes($now),
            'location' => $shift->location,
            'role' => $shift->businessRole,
            'notified' => $this->wasReminderSent($shift),
        ]);
    }

    /**
     * Get staffing status by location
     */
    public function getLocationStatus(int $tenantId): Collection
    {
        return Location::where('tenant_id', $tenantId)
            ->with(['departments'])
            ->get()
            ->map(fn ($location) => [
                'location' => $location,
                'clocked_in_count' => $this->countClockedIn($location->id),
                'expected_count' => $this->countExpectedNow($location->id),
                'status' => $this->calculateCoverageStatus($location->id),
                'missing_count' => $this->countMissing($location->id),
            ]);
    }

    /**
     * Calculate coverage status for a location
     */
    private function calculateCoverageStatus(int $locationId): string
    {
        $clockedIn = $this->countClockedIn($locationId);
        $expected = $this->countExpectedNow($locationId);

        if ($expected === 0) {
            return 'no_shifts';
        }

        $ratio = $clockedIn / $expected;

        return match (true) {
            $ratio >= 1.2 => 'overstaffed',
            $ratio >= 0.9 => 'adequate',
            $ratio >= 0.7 => 'understaffed',
            default => 'critical',
        };
    }

    /**
     * Send reminder to late employee
     */
    public function sendLateReminder(Shift $shift): void
    {
        if ($shift->user) {
            $shift->user->notify(new LateReminderNotification($shift));

            // Track that reminder was sent
            $shift->update(['late_reminder_sent_at' => now()]);
        }
    }

    /**
     * Get dashboard summary for a tenant/location
     */
    public function getDashboardSummary(int $tenantId, ?int $locationId = null): array
    {
        return [
            'currently_working' => $this->getCurrentlyWorking($tenantId, $locationId),
            'expected_arrivals' => $this->getExpectedArrivals($tenantId, 2, $locationId),
            'missing_late' => $this->getMissingLate($tenantId, 15, $locationId),
            'location_status' => $locationId ? null : $this->getLocationStatus($tenantId),
            'stats' => [
                'total_clocked_in' => $this->getCurrentlyWorking($tenantId, $locationId)->count(),
                'total_expected_today' => $this->countExpectedToday($tenantId, $locationId),
                'total_late' => $this->getMissingLate($tenantId, 15, $locationId)->count(),
            ],
        ];
    }

    private function countClockedIn(int $locationId): int;
    private function countExpectedNow(int $locationId): int;
    private function countMissing(int $locationId): int;
    private function countExpectedToday(int $tenantId, ?int $locationId): int;
    private function wasReminderSent(Shift $shift): bool;
}
```

### Operations Dashboard API

```php
// routes/api.php
Route::prefix('operations')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/dashboard', [OperationsDashboardController::class, 'index']);
        Route::get('/currently-working', [OperationsDashboardController::class, 'currentlyWorking']);
        Route::get('/expected-arrivals', [OperationsDashboardController::class, 'expectedArrivals']);
        Route::get('/missing-late', [OperationsDashboardController::class, 'missingLate']);
        Route::get('/location-status', [OperationsDashboardController::class, 'locationStatus']);
        Route::post('/send-reminder/{shift}', [OperationsDashboardController::class, 'sendReminder']);
    });
```

### Real-Time Updates (Broadcasting)

```php
// Events for real-time dashboard updates
class EmployeeClockedIn implements ShouldBroadcast
{
    public function __construct(
        public TimeEntry $timeEntry
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->timeEntry->user->tenant_id}.operations"),
            new PrivateChannel("location.{$this->timeEntry->shift?->location_id}.operations"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'clock_in',
            'user' => $this->timeEntry->user->only(['id', 'first_name', 'last_name']),
            'time' => $this->timeEntry->clock_in->toIso8601String(),
            'shift' => $this->timeEntry->shift?->only(['id', 'start_time', 'end_time']),
            'location_id' => $this->timeEntry->shift?->location_id,
        ];
    }
}

class EmployeeClockedOut implements ShouldBroadcast
{
    public function __construct(
        public TimeEntry $timeEntry
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->timeEntry->user->tenant_id}.operations"),
            new PrivateChannel("location.{$this->timeEntry->shift?->location_id}.operations"),
        ];
    }
}

class EmployeeLateAlert implements ShouldBroadcast
{
    public function __construct(
        public Shift $shift,
        public int $minutesLate
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->shift->tenant_id}.operations"),
            new PrivateChannel("location.{$this->shift->location_id}.operations"),
        ];
    }
}

// Channel authorization
Broadcast::channel('tenant.{tenantId}.operations', function (User $user, int $tenantId) {
    return $user->tenant_id === $tenantId && !$user->isEmployee();
});

Broadcast::channel('location.{locationId}.operations', function (User $user, int $locationId) {
    return $user->canManageLocation($locationId);
});
```

---

## 2.28 Data Retention & Archival

### Tenant-Configurable Retention Database

```php
Schema::create('data_retention_policies', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('data_type'); // shifts, time_entries, leave_requests, audit_logs, messages
    $table->integer('retention_days')->nullable(); // null = keep forever
    $table->integer('archive_after_days')->nullable(); // Move to cold storage
    $table->boolean('auto_delete')->default(false);
    $table->boolean('require_approval_to_delete')->default(true);
    $table->timestamps();

    $table->unique(['tenant_id', 'data_type']);
});

Schema::create('archived_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('source_table');
    $table->unsignedBigInteger('source_id');
    $table->json('data'); // Full record snapshot
    $table->timestamp('original_created_at');
    $table->timestamp('archived_at');
    $table->timestamp('scheduled_deletion_at')->nullable();
    $table->timestamps();

    $table->index(['tenant_id', 'source_table', 'source_id']);
    $table->index(['scheduled_deletion_at']);
});
```

### Data Retention Service

```php
class DataRetentionService
{
    private const DEFAULT_POLICIES = [
        'shifts' => ['retention_days' => 730, 'archive_after_days' => 365],
        'time_entries' => ['retention_days' => 730, 'archive_after_days' => 365],
        'leave_requests' => ['retention_days' => 730, 'archive_after_days' => 365],
        'audit_logs' => ['retention_days' => 365, 'archive_after_days' => 180],
        'messages' => ['retention_days' => 365, 'archive_after_days' => 90],
    ];

    public function getPolicyForTenant(int $tenantId, string $dataType): DataRetentionPolicy
    {
        return DataRetentionPolicy::firstOrCreate(
            ['tenant_id' => $tenantId, 'data_type' => $dataType],
            self::DEFAULT_POLICIES[$dataType] ?? ['retention_days' => null]
        );
    }

    public function archiveOldRecords(int $tenantId): ArchiveResult
    {
        $archived = 0;
        $policies = DataRetentionPolicy::where('tenant_id', $tenantId)->get();

        foreach ($policies as $policy) {
            if ($policy->archive_after_days) {
                $cutoff = now()->subDays($policy->archive_after_days);
                $archived += $this->archiveRecordsOlderThan($tenantId, $policy->data_type, $cutoff);
            }
        }

        return new ArchiveResult($archived);
    }

    public function deleteExpiredRecords(int $tenantId): DeletionResult
    {
        $deleted = 0;

        // Delete archived records past retention
        $deleted += ArchivedRecord::where('tenant_id', $tenantId)
            ->where('scheduled_deletion_at', '<=', now())
            ->delete();

        return new DeletionResult($deleted);
    }

    public function restoreFromArchive(ArchivedRecord $record): Model;
    public function exportTenantData(int $tenantId): string; // GDPR export
}
```

---

## 2.29 Notification Channels

### Multi-Channel Notification Database

```php
Schema::create('notification_channels', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('channel'); // email, push, slack, whatsapp, teams
    $table->boolean('is_enabled')->default(true);
    $table->json('config')->nullable(); // Channel-specific configuration
    $table->timestamps();

    $table->unique(['tenant_id', 'channel']);
});

Schema::create('user_notification_channels', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('channel'); // email, push, slack, whatsapp, teams
    $table->string('identifier'); // Email, phone, Slack user ID, etc.
    $table->boolean('is_verified')->default(false);
    $table->boolean('is_primary')->default(false);
    $table->json('preferences')->nullable(); // Which notification types
    $table->timestamps();

    $table->unique(['user_id', 'channel', 'identifier']);
});

// Slack/Teams integration
Schema::create('workspace_integrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('provider'); // slack, teams
    $table->string('workspace_id');
    $table->string('workspace_name');
    $table->text('access_token'); // Encrypted
    $table->text('bot_token')->nullable(); // Encrypted
    $table->json('scopes')->nullable();
    $table->timestamp('token_expires_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->unique(['tenant_id', 'provider']);
});
```

### Notification Channel Services

```php
class SlackNotificationChannel
{
    public function send(User $user, Notification $notification): void
    {
        $integration = $user->tenant->workspaceIntegration('slack');
        if (!$integration?->is_active) {
            return;
        }

        $slackUserId = $user->notificationChannels()
            ->where('channel', 'slack')
            ->where('is_verified', true)
            ->first()?->identifier;

        if (!$slackUserId) {
            return;
        }

        Http::withToken($integration->bot_token)
            ->post('https://slack.com/api/chat.postMessage', [
                'channel' => $slackUserId,
                'text' => $notification->toSlack($user)->content,
                'blocks' => $notification->toSlack($user)->blocks ?? null,
            ]);
    }
}

class WhatsAppNotificationChannel
{
    public function send(User $user, Notification $notification): void
    {
        $phone = $user->notificationChannels()
            ->where('channel', 'whatsapp')
            ->where('is_verified', true)
            ->first()?->identifier;

        if (!$phone) {
            return;
        }

        // Using WhatsApp Business API (via Twilio or direct)
        Http::withToken(config('services.whatsapp.token'))
            ->post(config('services.whatsapp.api_url') . '/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'template',
                'template' => $notification->toWhatsApp($user),
            ]);
    }
}

class TeamsNotificationChannel
{
    public function send(User $user, Notification $notification): void
    {
        $integration = $user->tenant->workspaceIntegration('teams');
        if (!$integration?->is_active) {
            return;
        }

        $teamsUserId = $user->notificationChannels()
            ->where('channel', 'teams')
            ->where('is_verified', true)
            ->first()?->identifier;

        if (!$teamsUserId) {
            return;
        }

        // Microsoft Graph API
        Http::withToken($integration->access_token)
            ->post("https://graph.microsoft.com/v1.0/users/{$teamsUserId}/chat/messages", [
                'body' => [
                    'content' => $notification->toTeams($user)->content,
                ],
            ]);
    }
}
```

---

## 2.30 Internationalization (i18n)

### Supported Languages

```php
// config/locales.php
return [
    'supported' => [
        'en' => ['name' => 'English', 'native' => 'English', 'rtl' => false],
        'es' => ['name' => 'Spanish', 'native' => 'Español', 'rtl' => false],
        'fr' => ['name' => 'French', 'native' => 'Français', 'rtl' => false],
        'de' => ['name' => 'German', 'native' => 'Deutsch', 'rtl' => false],
        'it' => ['name' => 'Italian', 'native' => 'Italiano', 'rtl' => false],
        'pt' => ['name' => 'Portuguese', 'native' => 'Português', 'rtl' => false],
    ],

    'default' => 'en',
    'fallback' => 'en',
];
```

### Locale Database

```php
Schema::create('tenant_locales', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('default_locale', 5)->default('en');
    $table->json('enabled_locales'); // ['en', 'es', 'fr']
    $table->string('timezone')->default('UTC');
    $table->string('date_format')->default('d/m/Y');
    $table->string('time_format')->default('H:i');
    $table->string('currency', 3)->default('EUR');
    $table->string('first_day_of_week')->default('monday');
    $table->timestamps();
});

// User locale preference stored in users table
// users.locale VARCHAR(5) NULLABLE
// users.timezone VARCHAR(50) NULLABLE
```

### Translation Service

```php
class LocalizationService
{
    public function getLocaleForUser(User $user): string
    {
        // Priority: User preference > Tenant default > App default
        return $user->locale
            ?? $user->tenant->tenantLocale?->default_locale
            ?? config('locales.default');
    }

    public function getAvailableLocalesForTenant(int $tenantId): array
    {
        $tenantLocale = TenantLocale::where('tenant_id', $tenantId)->first();
        return $tenantLocale?->enabled_locales ?? ['en'];
    }

    public function formatDateForUser(User $user, Carbon $date): string
    {
        $format = $user->tenant->tenantLocale?->date_format ?? 'd/m/Y';
        return $date->translatedFormat($format);
    }

    public function formatTimeForUser(User $user, Carbon $time): string
    {
        $format = $user->tenant->tenantLocale?->time_format ?? 'H:i';
        return $time->format($format);
    }

    public function formatCurrencyForTenant(int $tenantId, float $amount): string
    {
        $locale = TenantLocale::where('tenant_id', $tenantId)->first();
        $currency = $locale?->currency ?? 'EUR';

        return Number::currency($amount, $currency);
    }
}
```

---

## 2.31 Offline PWA Support

### Offline Data Sync Database

```php
Schema::create('offline_sync_queue', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('action'); // create, update, delete
    $table->string('entity_type'); // shift, time_entry, availability
    $table->unsignedBigInteger('entity_id')->nullable();
    $table->json('payload'); // Full action data
    $table->string('status')->default('pending'); // pending, synced, conflict, failed
    $table->json('conflict_data')->nullable();
    $table->timestamp('created_offline_at');
    $table->timestamp('synced_at')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'status']);
});
```

### Offline Sync Service

```php
class OfflineSyncService
{
    /**
     * Data available for offline access
     */
    public function getOfflineDataForUser(User $user): array
    {
        return [
            'user' => $user->only(['id', 'first_name', 'last_name', 'email']),
            'shifts' => $this->getUpcomingShifts($user),
            'coworkers' => $this->getCoworkers($user),
            'locations' => $this->getLocations($user),
            'business_roles' => $user->businessRoles,
            'leave_balances' => $this->getLeaveBalances($user),
            'pending_requests' => $this->getPendingRequests($user),
            'sync_timestamp' => now()->toIso8601String(),
        ];
    }

    private function getUpcomingShifts(User $user): Collection
    {
        return Shift::with(['location', 'department', 'businessRole', 'notes', 'tasks'])
            ->where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7))
            ->where('date', '<=', now()->addDays(30))
            ->get();
    }

    /**
     * Process queued offline actions when back online
     */
    public function syncOfflineActions(User $user, array $actions): SyncResult
    {
        $results = [];

        foreach ($actions as $action) {
            try {
                $result = match ($action['action']) {
                    'clock_in' => $this->processOfflineClockIn($user, $action),
                    'clock_out' => $this->processOfflineClockOut($user, $action),
                    'availability' => $this->processOfflineAvailability($user, $action),
                    'leave_request' => $this->processOfflineLeaveRequest($user, $action),
                    'task_complete' => $this->processOfflineTaskComplete($user, $action),
                    default => throw new InvalidActionException($action['action']),
                };
                $results[] = ['action' => $action, 'status' => 'synced', 'result' => $result];
            } catch (ConflictException $e) {
                $results[] = ['action' => $action, 'status' => 'conflict', 'error' => $e->getMessage()];
            } catch (\Exception $e) {
                $results[] = ['action' => $action, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }

        return new SyncResult($results);
    }

    /**
     * Handle offline clock-in with timestamp from device
     */
    private function processOfflineClockIn(User $user, array $action): TimeEntry
    {
        $offlineTime = Carbon::parse($action['timestamp']);

        // Check for conflicts (already clocked in at that time)
        $existing = TimeEntry::where('user_id', $user->id)
            ->whereDate('clock_in', $offlineTime->toDateString())
            ->first();

        if ($existing && !$existing->clock_out) {
            throw new ConflictException('Already clocked in at this time');
        }

        return TimeEntry::create([
            'user_id' => $user->id,
            'shift_id' => $action['shift_id'] ?? null,
            'clock_in' => $offlineTime,
            'clock_in_method' => 'offline_sync',
            'notes' => 'Recorded offline at ' . $action['device_time'],
        ]);
    }
}
```

### Service Worker Configuration

```javascript
// service-worker.js offline caching strategy
const CACHE_VERSION = 'v1';
const OFFLINE_CACHE = `plannrly-offline-${CACHE_VERSION}`;

const CACHE_URLS = [
    '/',
    '/dashboard',
    '/schedule',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json',
];

// Cache API responses for offline access
const API_CACHE_PATTERNS = [
    /\/api\/shifts/,
    /\/api\/user\/profile/,
    /\/api\/locations/,
    /\/api\/business-roles/,
];
```

---

## 2.32 Employee Self-Service Portal

### Document Management Database

```php
Schema::create('employee_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // certification, id_document, contract, other
    $table->string('name');
    $table->string('file_path');
    $table->string('mime_type');
    $table->integer('file_size');
    $table->date('expiry_date')->nullable();
    $table->boolean('is_verified')->default(false);
    $table->foreignId('verified_by')->nullable()->constrained('users');
    $table->timestamp('verified_at')->nullable();
    $table->boolean('is_visible_to_employee')->default(true);
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['user_id', 'type']);
    $table->index(['expiry_date']);
});

Schema::create('document_types', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('category'); // certification, identification, legal, other
    $table->boolean('requires_expiry')->default(false);
    $table->integer('expiry_warning_days')->nullable(); // Days before expiry to warn
    $table->boolean('employee_can_upload')->default(true);
    $table->boolean('is_required')->default(false);
    $table->json('allowed_mime_types')->nullable();
    $table->integer('max_file_size_kb')->default(5120); // 5MB default
    $table->timestamps();
});
```

### Employee Self-Service Service

```php
class EmployeeSelfServiceService
{
    /**
     * Upload employee document
     */
    public function uploadDocument(
        User $employee,
        UploadedFile $file,
        string $type,
        ?Carbon $expiryDate = null
    ): EmployeeDocument {
        $documentType = DocumentType::where('tenant_id', $employee->tenant_id)
            ->where('name', $type)
            ->firstOrFail();

        // Validate file
        $this->validateDocument($file, $documentType);

        $path = $file->store("documents/{$employee->tenant_id}/{$employee->id}", 'private');

        return EmployeeDocument::create([
            'tenant_id' => $employee->tenant_id,
            'user_id' => $employee->id,
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'expiry_date' => $expiryDate,
        ]);
    }

    /**
     * Get expiring documents for notifications
     */
    public function getExpiringDocuments(int $tenantId, int $daysAhead = 30): Collection
    {
        return EmployeeDocument::with('user')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($daysAhead)])
            ->get();
    }

    /**
     * Submit shift preference/bid
     */
    public function submitShiftPreference(User $employee, Shift $shift, int $priority): void
    {
        ShiftPreferenceBid::updateOrCreate(
            ['user_id' => $employee->id, 'shift_id' => $shift->id],
            ['priority' => $priority, 'submitted_at' => now()]
        );
    }

    /**
     * Get employee's personal dashboard stats
     */
    public function getEmployeeStats(User $employee, bool $showToEmployee = true): array
    {
        $tenantSettings = $employee->tenant->tenantSettings;

        // Check if tenant allows employees to see their stats
        if (!$showToEmployee && !$tenantSettings?->employee_can_view_stats) {
            return [];
        }

        return [
            'hours_this_week' => $this->getHoursThisWeek($employee),
            'hours_this_month' => $this->getHoursThisMonth($employee),
            'leave_balance' => $this->getLeaveBalances($employee),
            'upcoming_shifts' => $this->getUpcomingShiftsCount($employee),
            'attendance_rate' => $tenantSettings?->employee_can_view_attendance
                ? $this->getAttendanceRate($employee)
                : null,
            'punctuality_rate' => $tenantSettings?->employee_can_view_punctuality
                ? $this->getPunctualityRate($employee)
                : null,
        ];
    }
}
```

---

## 2.33 Manager Delegation System

### Delegation Database

```php
Schema::create('manager_delegations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('delegator_id')->constrained('users'); // Manager delegating
    $table->foreignId('delegate_id')->constrained('users'); // Person receiving delegation
    $table->json('permissions'); // ['approve_leave', 'approve_swaps', 'publish_schedule', 'edit_shifts']
    $table->json('scope')->nullable(); // location_ids, department_ids - null = all delegator's scope
    $table->date('start_date');
    $table->date('end_date')->nullable(); // null = indefinite
    $table->string('reason')->nullable(); // "Vacation coverage", "Training"
    $table->boolean('is_active')->default(true);
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamps();

    $table->index(['delegate_id', 'is_active']);
    $table->index(['delegator_id', 'is_active']);
});

Schema::create('delegation_audit_log', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delegation_id')->constrained('manager_delegations')->cascadeOnDelete();
    $table->foreignId('delegate_id')->constrained('users');
    $table->string('action'); // approved_leave, approved_swap, published_schedule, etc.
    $table->string('entity_type');
    $table->unsignedBigInteger('entity_id');
    $table->json('action_data')->nullable();
    $table->timestamps();
});
```

### Delegation Service

```php
class DelegationService
{
    /**
     * Check if user can perform action via delegation
     */
    public function canPerformAction(User $user, string $permission, ?int $locationId = null, ?int $departmentId = null): bool
    {
        // First check direct permissions
        if ($this->hasDirectPermission($user, $permission, $locationId, $departmentId)) {
            return true;
        }

        // Then check delegated permissions
        return $this->hasDelegatedPermission($user, $permission, $locationId, $departmentId);
    }

    private function hasDelegatedPermission(User $user, string $permission, ?int $locationId, ?int $departmentId): bool
    {
        $delegations = ManagerDelegation::where('delegate_id', $user->id)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->get();

        foreach ($delegations as $delegation) {
            $permissions = $delegation->permissions;
            if (!in_array($permission, $permissions)) {
                continue;
            }

            // Check scope
            $scope = $delegation->scope;
            if ($scope === null) {
                return true; // Full scope of delegator
            }

            if ($locationId && isset($scope['location_ids'])) {
                if (in_array($locationId, $scope['location_ids'])) {
                    return true;
                }
            }

            if ($departmentId && isset($scope['department_ids'])) {
                if (in_array($departmentId, $scope['department_ids'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create delegation with tenant admin approval if required
     */
    public function createDelegation(
        User $delegator,
        User $delegate,
        array $permissions,
        Carbon $startDate,
        ?Carbon $endDate = null,
        ?array $scope = null,
        ?string $reason = null
    ): ManagerDelegation {
        $tenantSettings = $delegator->tenant->tenantSettings;

        $delegation = ManagerDelegation::create([
            'tenant_id' => $delegator->tenant_id,
            'delegator_id' => $delegator->id,
            'delegate_id' => $delegate->id,
            'permissions' => $permissions,
            'scope' => $scope,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $reason,
            'is_active' => !$tenantSettings?->delegation_requires_approval,
        ]);

        if ($tenantSettings?->delegation_requires_approval) {
            // Notify tenant admins for approval
            $this->notifyAdminsForApproval($delegation);
        }

        return $delegation;
    }

    /**
     * Log action performed under delegation
     */
    public function logDelegatedAction(
        ManagerDelegation $delegation,
        string $action,
        string $entityType,
        int $entityId,
        ?array $data = null
    ): void {
        DelegationAuditLog::create([
            'delegation_id' => $delegation->id,
            'delegate_id' => $delegation->delegate_id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action_data' => $data,
        ]);
    }
}

enum DelegationPermission: string
{
    case ApproveLeave = 'approve_leave';
    case ApproveSwaps = 'approve_swaps';
    case PublishSchedule = 'publish_schedule';
    case EditShifts = 'edit_shifts';
    case ViewReports = 'view_reports';
    case ManageAvailability = 'manage_availability';
}
```

---

## 2.34 Messaging System

### Messaging Database

```php
Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // direct, group, announcement, shift
    $table->string('title')->nullable(); // For groups/announcements
    $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete(); // For shift conversations
    $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete(); // For location announcements
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('created_by')->constrained('users');
    $table->boolean('is_archived')->default(false);
    $table->timestamps();

    $table->index(['tenant_id', 'type']);
});

Schema::create('conversation_participants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('role')->default('member'); // admin, member
    $table->timestamp('last_read_at')->nullable();
    $table->boolean('is_muted')->default(false);
    $table->boolean('is_archived')->default(false);
    $table->timestamps();

    $table->unique(['conversation_id', 'user_id']);
});

Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sender_id')->constrained('users');
    $table->text('content');
    $table->string('type')->default('text'); // text, image, file, system
    $table->json('metadata')->nullable(); // attachments, mentions, etc.
    $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
    $table->timestamp('edited_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['conversation_id', 'created_at']);
});

Schema::create('message_reads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('message_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('read_at');

    $table->unique(['message_id', 'user_id']);
});

// Team/Location announcements
Schema::create('announcements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('author_id')->constrained('users');
    $table->string('title');
    $table->text('content');
    $table->string('priority')->default('normal'); // low, normal, high, urgent
    $table->json('target_audience'); // all, locations: [], departments: [], roles: []
    $table->boolean('requires_acknowledgement')->default(false);
    $table->timestamp('publish_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->boolean('is_pinned')->default(false);
    $table->timestamps();
    $table->softDeletes();

    $table->index(['tenant_id', 'publish_at']);
});

Schema::create('announcement_acknowledgements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('acknowledged_at');

    $table->unique(['announcement_id', 'user_id']);
});
```

### Messaging Service

```php
class MessagingService
{
    /**
     * Create or get direct message conversation
     */
    public function getOrCreateDirectConversation(User $user1, User $user2): Conversation
    {
        // Find existing conversation
        $conversation = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user1->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user2->id))
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new
        $conversation = Conversation::create([
            'tenant_id' => $user1->tenant_id,
            'type' => 'direct',
            'created_by' => $user1->id,
        ]);

        $conversation->participants()->createMany([
            ['user_id' => $user1->id],
            ['user_id' => $user2->id],
        ]);

        return $conversation;
    }

    /**
     * Create group conversation
     */
    public function createGroupConversation(User $creator, string $title, array $memberIds): Conversation
    {
        $conversation = Conversation::create([
            'tenant_id' => $creator->tenant_id,
            'type' => 'group',
            'title' => $title,
            'created_by' => $creator->id,
        ]);

        $participants = collect($memberIds)
            ->push($creator->id)
            ->unique()
            ->map(fn ($id) => [
                'user_id' => $id,
                'role' => $id === $creator->id ? 'admin' : 'member',
            ]);

        $conversation->participants()->createMany($participants);

        return $conversation;
    }

    /**
     * Send message
     */
    public function sendMessage(
        Conversation $conversation,
        User $sender,
        string $content,
        ?int $replyToId = null,
        array $metadata = []
    ): Message {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => $content,
            'reply_to_id' => $replyToId,
            'metadata' => $metadata ?: null,
        ]);

        // Update sender's last read
        $conversation->participants()
            ->where('user_id', $sender->id)
            ->update(['last_read_at' => now()]);

        // Notify other participants
        $this->notifyParticipants($conversation, $message);

        // Broadcast for real-time
        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    /**
     * Post announcement
     */
    public function postAnnouncement(
        User $author,
        string $title,
        string $content,
        array $targetAudience,
        array $options = []
    ): Announcement {
        $announcement = Announcement::create([
            'tenant_id' => $author->tenant_id,
            'author_id' => $author->id,
            'title' => $title,
            'content' => $content,
            'target_audience' => $targetAudience,
            'priority' => $options['priority'] ?? 'normal',
            'requires_acknowledgement' => $options['requires_acknowledgement'] ?? false,
            'publish_at' => $options['publish_at'] ?? now(),
            'expires_at' => $options['expires_at'] ?? null,
            'is_pinned' => $options['is_pinned'] ?? false,
        ]);

        // Notify targeted users
        $this->notifyAnnouncementRecipients($announcement);

        return $announcement;
    }

    /**
     * Get unread counts for user
     */
    public function getUnreadCounts(User $user): array
    {
        $conversations = $user->conversationParticipations()
            ->with('conversation')
            ->get();

        $unreadByConversation = [];
        foreach ($conversations as $participant) {
            $unread = Message::where('conversation_id', $participant->conversation_id)
                ->where('sender_id', '!=', $user->id)
                ->where('created_at', '>', $participant->last_read_at ?? '1970-01-01')
                ->count();

            if ($unread > 0) {
                $unreadByConversation[$participant->conversation_id] = $unread;
            }
        }

        return [
            'total_unread' => array_sum($unreadByConversation),
            'by_conversation' => $unreadByConversation,
            'unread_announcements' => $this->getUnreadAnnouncementsCount($user),
        ];
    }
}
```

---

## 2.35 Custom Report Builder

### Report Builder Database

```php
Schema::create('custom_reports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users');
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('base_entity'); // shifts, time_entries, users, leave_requests
    $table->json('columns'); // Selected columns with aliases
    $table->json('filters')->nullable(); // Filter conditions
    $table->json('grouping')->nullable(); // Group by fields
    $table->json('sorting')->nullable(); // Order by fields
    $table->json('aggregations')->nullable(); // sum, count, avg
    $table->string('chart_type')->nullable(); // bar, line, pie, table
    $table->json('chart_config')->nullable();
    $table->boolean('is_shared')->default(false);
    $table->boolean('is_scheduled')->default(false);
    $table->string('schedule_frequency')->nullable(); // daily, weekly, monthly
    $table->json('schedule_recipients')->nullable(); // email addresses
    $table->timestamps();

    $table->index(['tenant_id', 'created_by']);
});

Schema::create('report_executions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_id')->constrained('custom_reports')->cascadeOnDelete();
    $table->foreignId('executed_by')->nullable()->constrained('users');
    $table->json('parameters')->nullable(); // Runtime parameters (date range, etc.)
    $table->string('status'); // pending, running, completed, failed
    $table->string('output_path')->nullable(); // Stored file path
    $table->integer('row_count')->nullable();
    $table->integer('execution_time_ms')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamps();
});
```

### Report Builder Service

```php
class ReportBuilderService
{
    private const AVAILABLE_ENTITIES = [
        'shifts' => [
            'columns' => ['id', 'date', 'start_time', 'end_time', 'status', 'user_id', 'location_id', 'department_id', 'business_role_id', 'created_at'],
            'relations' => ['user', 'location', 'department', 'businessRole'],
            'aggregatable' => ['duration_hours', 'count'],
        ],
        'time_entries' => [
            'columns' => ['id', 'clock_in', 'clock_out', 'break_minutes', 'user_id', 'shift_id'],
            'relations' => ['user', 'shift'],
            'aggregatable' => ['duration_hours', 'break_minutes', 'count'],
        ],
        'users' => [
            'columns' => ['id', 'first_name', 'last_name', 'email', 'is_active', 'created_at'],
            'relations' => ['businessRoles', 'employmentDetails'],
            'aggregatable' => ['count'],
        ],
        'leave_requests' => [
            'columns' => ['id', 'user_id', 'leave_type_id', 'start_date', 'end_date', 'status', 'days_requested'],
            'relations' => ['user', 'leaveType'],
            'aggregatable' => ['days_requested', 'count'],
        ],
    ];

    /**
     * Build and execute report query
     */
    public function executeReport(CustomReport $report, array $parameters = []): ReportResult
    {
        $execution = ReportExecution::create([
            'report_id' => $report->id,
            'executed_by' => auth()->id(),
            'parameters' => $parameters,
            'status' => 'running',
        ]);

        $startTime = microtime(true);

        try {
            $query = $this->buildQuery($report, $parameters);
            $data = $query->get();

            $execution->update([
                'status' => 'completed',
                'row_count' => $data->count(),
                'execution_time_ms' => (microtime(true) - $startTime) * 1000,
            ]);

            return new ReportResult($data, $report->chart_type, $report->chart_config);
        } catch (\Exception $e) {
            $execution->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build query from report definition
     */
    private function buildQuery(CustomReport $report, array $parameters): Builder
    {
        $modelClass = $this->getModelClass($report->base_entity);
        $query = $modelClass::query();

        // Apply tenant scope
        if (method_exists($modelClass, 'scopeTenant')) {
            $query->where('tenant_id', $report->tenant_id);
        }

        // Select columns
        $query->select($this->parseColumns($report->columns));

        // Apply filters
        if ($report->filters) {
            $this->applyFilters($query, $report->filters, $parameters);
        }

        // Apply date range from parameters
        if (isset($parameters['start_date']) && isset($parameters['end_date'])) {
            $dateColumn = $this->getDateColumn($report->base_entity);
            $query->whereBetween($dateColumn, [$parameters['start_date'], $parameters['end_date']]);
        }

        // Apply grouping
        if ($report->grouping) {
            $query->groupBy($report->grouping);
        }

        // Apply sorting
        if ($report->sorting) {
            foreach ($report->sorting as $sort) {
                $query->orderBy($sort['column'], $sort['direction'] ?? 'asc');
            }
        }

        // Apply aggregations
        if ($report->aggregations) {
            $this->applyAggregations($query, $report->aggregations);
        }

        return $query;
    }

    /**
     * Export report to PDF/Excel
     */
    public function exportReport(CustomReport $report, string $format, array $parameters = []): string
    {
        $result = $this->executeReport($report, $parameters);

        return match ($format) {
            'pdf' => $this->exportToPdf($report, $result),
            'excel' => $this->exportToExcel($report, $result),
            default => throw new InvalidArgumentException("Unsupported format: {$format}"),
        };
    }

    /**
     * Get available columns for entity
     */
    public function getAvailableColumns(string $entity): array
    {
        return self::AVAILABLE_ENTITIES[$entity] ?? [];
    }
}
```

---

## 2.36 Billing & Dunning

### Payment Failure Handling Database

```php
Schema::create('payment_attempts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
    $table->string('stripe_payment_intent_id')->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('EUR');
    $table->string('status'); // succeeded, failed, pending, requires_action
    $table->string('failure_reason')->nullable();
    $table->integer('attempt_number')->default(1);
    $table->timestamp('next_retry_at')->nullable();
    $table->timestamps();

    $table->index(['tenant_id', 'status']);
    $table->index(['next_retry_at']);
});

Schema::create('dunning_states', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('status'); // active, grace_period, suspended, cancelled
    $table->date('grace_period_started_at')->nullable();
    $table->date('grace_period_ends_at')->nullable();
    $table->date('suspended_at')->nullable();
    $table->integer('failed_payment_count')->default(0);
    $table->timestamp('last_payment_reminder_at')->nullable();
    $table->timestamps();
});
```

### Dunning Service

```php
class DunningService
{
    private const RETRY_SCHEDULE = [1, 3, 7]; // Days after initial failure
    private const GRACE_PERIOD_DAYS = 14;

    public function handlePaymentFailure(Tenant $tenant, string $reason): void
    {
        $dunning = DunningState::firstOrCreate(
            ['tenant_id' => $tenant->id],
            ['status' => 'active', 'failed_payment_count' => 0]
        );

        $dunning->increment('failed_payment_count');

        if ($dunning->failed_payment_count === 1) {
            $dunning->update([
                'status' => 'grace_period',
                'grace_period_started_at' => now(),
                'grace_period_ends_at' => now()->addDays(self::GRACE_PERIOD_DAYS),
            ]);
        }

        $this->scheduleRetry($tenant, $dunning->failed_payment_count);
        $this->sendPaymentFailureNotification($tenant, $reason, $dunning);
    }

    public function handlePaymentSuccess(Tenant $tenant): void
    {
        DunningState::where('tenant_id', $tenant->id)->update([
            'status' => 'active',
            'failed_payment_count' => 0,
            'grace_period_started_at' => null,
            'grace_period_ends_at' => null,
            'suspended_at' => null,
        ]);
    }

    public function processSuspensions(): void
    {
        DunningState::where('status', 'grace_period')
            ->where('grace_period_ends_at', '<=', now())
            ->each(function ($dunning) {
                $dunning->update(['status' => 'suspended', 'suspended_at' => now()]);
                $this->suspendTenant($dunning->tenant);
            });
    }

    private function suspendTenant(Tenant $tenant): void;
    private function scheduleRetry(Tenant $tenant, int $attemptNumber): void;
    private function sendPaymentFailureNotification(Tenant $tenant, string $reason, DunningState $state): void;
}
```

---

## 2.37 Global Search

### Search Index Database

```php
Schema::create('search_index', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('searchable_type'); // User, Shift, Message, Document, etc.
    $table->unsignedBigInteger('searchable_id');
    $table->text('content'); // Indexed searchable content
    $table->json('metadata')->nullable(); // Additional filterable data
    $table->timestamps();

    $table->fullText('content');
    $table->index(['tenant_id', 'searchable_type']);
    $table->unique(['searchable_type', 'searchable_id']);
});

Schema::create('recent_searches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('query');
    $table->string('context')->nullable(); // Page/context where search was made
    $table->timestamps();

    $table->index(['user_id', 'created_at']);
});
```

### Search Service

```php
class GlobalSearchService
{
    public function search(int $tenantId, string $query, array $filters = []): SearchResults
    {
        $results = SearchIndex::where('tenant_id', $tenantId)
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('searchable_type', $type))
            ->whereFullText('content', $query)
            ->limit(50)
            ->get()
            ->groupBy('searchable_type');

        return new SearchResults($results, $query);
    }

    public function indexModel(Model $model): void
    {
        SearchIndex::updateOrCreate(
            [
                'searchable_type' => get_class($model),
                'searchable_id' => $model->id,
            ],
            [
                'tenant_id' => $model->tenant_id,
                'content' => $model->toSearchableString(),
                'metadata' => $model->toSearchableMetadata(),
            ]
        );
    }

    public function removeFromIndex(Model $model): void
    {
        SearchIndex::where('searchable_type', get_class($model))
            ->where('searchable_id', $model->id)
            ->delete();
    }

    public function reindexTenant(int $tenantId): void;
    public function saveRecentSearch(int $userId, string $query): void;
    public function getRecentSearches(int $userId, int $limit = 10): Collection;
}

// Trait for searchable models
trait Searchable
{
    protected static function bootSearchable(): void
    {
        static::saved(fn ($model) => app(GlobalSearchService::class)->indexModel($model));
        static::deleted(fn ($model) => app(GlobalSearchService::class)->removeFromIndex($model));
    }

    abstract public function toSearchableString(): string;
    public function toSearchableMetadata(): array { return []; }
}
```

---

## 2.38 Advanced Shift Patterns

### Shift Pattern Database

```php
Schema::create('shift_patterns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('type'); // split, rotating, on_call, standard
    $table->json('pattern_config'); // Type-specific configuration
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('rotation_schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
    $table->string('name');
    $table->integer('rotation_weeks'); // Number of weeks in rotation cycle
    $table->json('week_patterns'); // Pattern for each week
    $table->date('start_date'); // When rotation started/starts
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('rotation_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rotation_schedule_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->integer('starting_week'); // Which week of rotation user starts on
    $table->date('effective_from');
    $table->date('effective_until')->nullable();
    $table->timestamps();

    $table->unique(['rotation_schedule_id', 'user_id', 'effective_from']);
});

// Add columns to shifts table for advanced patterns
Schema::table('shifts', function (Blueprint $table) {
    $table->string('shift_type')->default('standard'); // standard, split, on_call, overnight
    $table->foreignId('parent_shift_id')->nullable()->constrained('shifts')->nullOnDelete(); // For split shifts
    $table->boolean('is_on_call')->default(false);
    $table->decimal('on_call_rate', 10, 2)->nullable(); // Special rate for on-call
    $table->boolean('crosses_midnight')->default(false);
});
```

### Shift Pattern Service

```php
class ShiftPatternService
{
    /**
     * Create a split shift (two segments with gap)
     */
    public function createSplitShift(
        array $baseData,
        array $segments // [{start: '09:00', end: '12:00'}, {start: '17:00', end: '21:00'}]
    ): Collection {
        $parentShift = Shift::create([
            ...$baseData,
            'shift_type' => 'split',
            'start_time' => $segments[0]['start'],
            'end_time' => $segments[count($segments) - 1]['end'],
        ]);

        $shifts = collect([$parentShift]);

        foreach (array_slice($segments, 1) as $segment) {
            $shifts->push(Shift::create([
                ...$baseData,
                'shift_type' => 'split',
                'parent_shift_id' => $parentShift->id,
                'start_time' => $segment['start'],
                'end_time' => $segment['end'],
            ]));
        }

        return $shifts;
    }

    /**
     * Generate shifts from rotation schedule
     */
    public function generateRotationShifts(
        RotationSchedule $rotation,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $shifts = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $weekNumber = $this->getRotationWeek($rotation, $currentDate);
            $weekPattern = $rotation->week_patterns[$weekNumber] ?? [];
            $dayOfWeek = $currentDate->dayOfWeek;

            if (isset($weekPattern[$dayOfWeek])) {
                foreach ($weekPattern[$dayOfWeek] as $shiftTemplate) {
                    $shifts->push($this->createShiftFromTemplate($rotation, $shiftTemplate, $currentDate));
                }
            }

            $currentDate->addDay();
        }

        return $shifts;
    }

    /**
     * Handle overnight shifts (crossing midnight)
     */
    public function createOvernightShift(array $data): Shift
    {
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        if ($endTime <= $startTime) {
            $data['crosses_midnight'] = true;
            // End date is actually next day
        }

        return Shift::create($data);
    }

    private function getRotationWeek(RotationSchedule $rotation, Carbon $date): int;
    private function createShiftFromTemplate(RotationSchedule $rotation, array $template, Carbon $date): Shift;
}

enum ShiftType: string
{
    case Standard = 'standard';
    case Split = 'split';
    case OnCall = 'on_call';
    case Overnight = 'overnight';
    case MultiDay = 'multi_day';
}
```

---

## 2.39 Public Holiday Management

### Holiday Database

```php
Schema::create('public_holidays', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete(); // null = all locations
    $table->string('name');
    $table->date('date');
    $table->string('country_code', 2)->nullable();
    $table->string('region_code')->nullable(); // For regional holidays
    $table->boolean('is_recurring')->default(false); // Repeats annually
    $table->boolean('is_closure_day')->default(false); // Location closed
    $table->decimal('pay_multiplier', 3, 2)->default(1.00); // 1.5 = time and a half
    $table->string('source')->default('manual'); // manual, imported, api
    $table->timestamps();

    $table->unique(['tenant_id', 'location_id', 'date']);
    $table->index(['date']);
});

Schema::create('holiday_work_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('public_holiday_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('preference'); // want_to_work, prefer_off, no_preference
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['public_holiday_id', 'user_id']);
});
```

### Holiday Service

```php
class HolidayService
{
    /**
     * Import public holidays for a country/region
     */
    public function importHolidays(int $tenantId, string $countryCode, int $year, ?string $regionCode = null): int
    {
        $holidays = $this->fetchHolidaysFromApi($countryCode, $year, $regionCode);
        $imported = 0;

        foreach ($holidays as $holiday) {
            PublicHoliday::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'date' => $holiday['date'],
                    'location_id' => null,
                ],
                [
                    'name' => $holiday['name'],
                    'country_code' => $countryCode,
                    'region_code' => $regionCode,
                    'is_recurring' => $holiday['recurring'] ?? false,
                    'source' => 'api',
                ]
            );
            $imported++;
        }

        return $imported;
    }

    /**
     * Get holidays for a date range
     */
    public function getHolidaysForPeriod(int $tenantId, Carbon $start, Carbon $end, ?int $locationId = null): Collection
    {
        return PublicHoliday::where('tenant_id', $tenantId)
            ->where(fn ($q) => $q->whereNull('location_id')->orWhere('location_id', $locationId))
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();
    }

    /**
     * Check if date is a holiday
     */
    public function isHoliday(int $tenantId, Carbon $date, ?int $locationId = null): ?PublicHoliday
    {
        return PublicHoliday::where('tenant_id', $tenantId)
            ->where('date', $date->toDateString())
            ->where(fn ($q) => $q->whereNull('location_id')->orWhere('location_id', $locationId))
            ->first();
    }

    /**
     * Calculate holiday pay for a shift
     */
    public function calculateHolidayPay(Shift $shift): ?array
    {
        $holiday = $this->isHoliday($shift->tenant_id, $shift->date, $shift->location_id);

        if (!$holiday || $holiday->pay_multiplier == 1.00) {
            return null;
        }

        $baseRate = $shift->user?->getHourlyRateForRole($shift->business_role_id) ?? 0;
        $holidayRate = $baseRate * $holiday->pay_multiplier;

        return [
            'holiday' => $holiday,
            'base_rate' => $baseRate,
            'holiday_rate' => $holidayRate,
            'multiplier' => $holiday->pay_multiplier,
            'hours' => $shift->duration_hours,
            'holiday_pay' => $holidayRate * $shift->duration_hours,
        ];
    }

    private function fetchHolidaysFromApi(string $country, int $year, ?string $region): array;
}
```

---

## 2.40 Urgent Shift Coverage

### Coverage Request Database

```php
Schema::create('coverage_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
    $table->foreignId('requested_by')->constrained('users');
    $table->string('reason'); // no_show, illness, emergency, other
    $table->string('status')->default('open'); // open, claimed, filled, cancelled, expired
    $table->string('priority')->default('normal'); // normal, urgent, critical
    $table->timestamp('expires_at')->nullable();
    $table->foreignId('filled_by')->nullable()->constrained('users');
    $table->timestamp('filled_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['tenant_id', 'status']);
    $table->index(['shift_id']);
});

Schema::create('coverage_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('coverage_request_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('response'); // interested, declined, unavailable
    $table->text('message')->nullable();
    $table->timestamps();

    $table->unique(['coverage_request_id', 'user_id']);
});

Schema::create('coverage_notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('coverage_request_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('channel'); // push, sms, email
    $table->string('status'); // sent, delivered, failed
    $table->timestamp('sent_at');
    $table->timestamp('delivered_at')->nullable();
    $table->timestamps();
});
```

### Urgent Coverage Service

```php
class UrgentCoverageService
{
    /**
     * Create urgent coverage request
     */
    public function createCoverageRequest(Shift $shift, User $requestedBy, string $reason): CoverageRequest
    {
        $request = CoverageRequest::create([
            'tenant_id' => $shift->tenant_id,
            'shift_id' => $shift->id,
            'requested_by' => $requestedBy->id,
            'reason' => $reason,
            'priority' => $this->determinePriority($shift),
            'expires_at' => $shift->date->copy()->setTimeFromTimeString($shift->start_time->format('H:i')),
        ]);

        // Find and notify eligible employees
        $this->broadcastToEligibleEmployees($request);

        return $request;
    }

    /**
     * Determine priority based on shift timing
     */
    private function determinePriority(Shift $shift): string
    {
        $hoursUntilShift = now()->diffInHours($shift->getStartDateTime(), false);

        return match (true) {
            $hoursUntilShift <= 2 => 'critical',
            $hoursUntilShift <= 12 => 'urgent',
            default => 'normal',
        };
    }

    /**
     * Find and notify eligible employees
     */
    public function broadcastToEligibleEmployees(CoverageRequest $request): void
    {
        $shift = $request->shift;
        $eligibleUsers = $this->findEligibleEmployees($shift);

        foreach ($eligibleUsers as $user) {
            $channels = $this->getNotificationChannels($request->priority, $user);

            foreach ($channels as $channel) {
                $this->sendNotification($request, $user, $channel);
            }
        }
    }

    /**
     * Get notification channels based on priority
     */
    private function getNotificationChannels(string $priority, User $user): array
    {
        return match ($priority) {
            'critical' => ['push', 'sms', 'email'],
            'urgent' => ['push', 'sms'],
            'normal' => ['push', 'email'],
        };
    }

    /**
     * Handle employee response to coverage request
     */
    public function handleResponse(CoverageRequest $request, User $user, string $response): void
    {
        CoverageResponse::updateOrCreate(
            ['coverage_request_id' => $request->id, 'user_id' => $user->id],
            ['response' => $response]
        );

        if ($response === 'interested') {
            $this->notifyManagerOfInterest($request, $user);

            // Auto-assign if configured
            if ($this->shouldAutoAssign($request)) {
                $this->assignCoverage($request, $user);
            }
        }
    }

    /**
     * Assign coverage to an employee
     */
    public function assignCoverage(CoverageRequest $request, User $user): void
    {
        $request->shift->update(['user_id' => $user->id]);

        $request->update([
            'status' => 'filled',
            'filled_by' => $user->id,
            'filled_at' => now(),
        ]);

        // Notify all parties
        $this->notifyCoverageFilled($request, $user);
    }

    private function findEligibleEmployees(Shift $shift): Collection;
    private function sendNotification(CoverageRequest $request, User $user, string $channel): void;
    private function notifyManagerOfInterest(CoverageRequest $request, User $user): void;
    private function shouldAutoAssign(CoverageRequest $request): bool;
    private function notifyCoverageFilled(CoverageRequest $request, User $filler): void;
}
```

---

## 2.41 Configurable Approval Workflows

### Approval Workflow Database

```php
Schema::create('approval_workflows', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('entity_type'); // leave_request, shift_swap, overtime, expense, schedule_change
    $table->string('name');
    $table->json('conditions')->nullable(); // When this workflow applies
    $table->json('steps'); // Approval chain configuration
    $table->boolean('is_active')->default(true);
    $table->integer('priority')->default(0); // Higher = checked first
    $table->timestamps();

    $table->index(['tenant_id', 'entity_type', 'is_active']);
});

Schema::create('approval_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('workflow_id')->constrained('approval_workflows');
    $table->string('approvable_type');
    $table->unsignedBigInteger('approvable_id');
    $table->foreignId('requested_by')->constrained('users');
    $table->integer('current_step')->default(0);
    $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();

    $table->index(['approvable_type', 'approvable_id']);
    $table->index(['status']);
});

Schema::create('approval_actions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
    $table->integer('step_number');
    $table->foreignId('approver_id')->constrained('users');
    $table->string('action'); // approved, rejected, delegated
    $table->text('comments')->nullable();
    $table->timestamps();
});
```

### Approval Workflow Service

```php
class ApprovalWorkflowService
{
    /**
     * Submit item for approval
     */
    public function submitForApproval(Model $approvable, User $requestedBy): ApprovalRequest
    {
        $workflow = $this->findApplicableWorkflow($approvable);

        if (!$workflow) {
            // No workflow = auto-approve
            $this->autoApprove($approvable);
            return null;
        }

        $request = ApprovalRequest::create([
            'tenant_id' => $approvable->tenant_id,
            'workflow_id' => $workflow->id,
            'approvable_type' => get_class($approvable),
            'approvable_id' => $approvable->id,
            'requested_by' => $requestedBy->id,
            'current_step' => 0,
        ]);

        $this->notifyNextApprovers($request);

        return $request;
    }

    /**
     * Find applicable workflow based on conditions
     */
    private function findApplicableWorkflow(Model $approvable): ?ApprovalWorkflow
    {
        $entityType = $this->getEntityType($approvable);

        return ApprovalWorkflow::where('tenant_id', $approvable->tenant_id)
            ->where('entity_type', $entityType)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get()
            ->first(fn ($wf) => $this->conditionsMet($wf, $approvable));
    }

    /**
     * Process approval/rejection
     */
    public function processAction(ApprovalRequest $request, User $approver, string $action, ?string $comments = null): void
    {
        ApprovalAction::create([
            'approval_request_id' => $request->id,
            'step_number' => $request->current_step,
            'approver_id' => $approver->id,
            'action' => $action,
            'comments' => $comments,
        ]);

        if ($action === 'rejected') {
            $this->rejectRequest($request, $comments);
        } elseif ($this->isStepComplete($request)) {
            $this->advanceToNextStep($request);
        }
    }

    /**
     * Check if all required approvers at current step have approved
     */
    private function isStepComplete(ApprovalRequest $request): bool
    {
        $workflow = $request->workflow;
        $currentStepConfig = $workflow->steps[$request->current_step] ?? null;

        if (!$currentStepConfig) {
            return true;
        }

        $requiredApprovals = $currentStepConfig['required_approvals'] ?? 1;
        $actualApprovals = $request->actions()
            ->where('step_number', $request->current_step)
            ->where('action', 'approved')
            ->count();

        return $actualApprovals >= $requiredApprovals;
    }

    private function advanceToNextStep(ApprovalRequest $request): void;
    private function rejectRequest(ApprovalRequest $request, ?string $reason): void;
    private function autoApprove(Model $approvable): void;
    private function notifyNextApprovers(ApprovalRequest $request): void;
    private function conditionsMet(ApprovalWorkflow $workflow, Model $approvable): bool;
}
```

---

## 2.42 Team Availability Dashboard

### Availability View Service

```php
class TeamAvailabilityService
{
    /**
     * Get comprehensive team availability for a period
     */
    public function getTeamAvailability(
        int $tenantId,
        Carbon $startDate,
        Carbon $endDate,
        array $filters = []
    ): TeamAvailabilityResult {
        $users = $this->getFilteredUsers($tenantId, $filters);

        $availability = [];
        foreach ($users as $user) {
            $availability[$user->id] = [
                'user' => $user,
                'availability_patterns' => $this->getAvailabilityPatterns($user, $startDate, $endDate),
                'leave_periods' => $this->getApprovedLeave($user, $startDate, $endDate),
                'assigned_shifts' => $this->getAssignedShifts($user, $startDate, $endDate),
                'shift_preferences' => $user->shiftPreferences,
                'skills' => $user->businessRoles->pluck('name'),
                'certifications' => $this->getActiveCertifications($user),
                'weekly_hours' => $this->getWeeklyHoursCommitted($user, $startDate, $endDate),
                'max_hours' => $user->userEmploymentDetails?->target_hours_per_week,
            ];
        }

        return new TeamAvailabilityResult(
            users: $availability,
            gaps: $this->identifyGaps($availability, $startDate, $endDate, $filters),
            skillCoverage: $this->analyzeSkillCoverage($availability, $startDate, $endDate),
        );
    }

    /**
     * Identify understaffed periods
     */
    private function identifyGaps(array $availability, Carbon $start, Carbon $end, array $filters): Collection
    {
        $gaps = collect();
        $requirements = $this->getStaffingRequirements($filters);

        $current = $start->copy();
        while ($current <= $end) {
            foreach ($requirements as $req) {
                $availableCount = $this->countAvailableWithSkill(
                    $availability,
                    $current,
                    $req->business_role_id
                );

                if ($availableCount < $req->minimum_staff) {
                    $gaps->push([
                        'date' => $current->copy(),
                        'role' => $req->businessRole->name,
                        'required' => $req->minimum_staff,
                        'available' => $availableCount,
                        'shortage' => $req->minimum_staff - $availableCount,
                    ]);
                }
            }
            $current->addDay();
        }

        return $gaps;
    }

    /**
     * Analyze skill coverage
     */
    private function analyzeSkillCoverage(array $availability, Carbon $start, Carbon $end): array;
    private function getFilteredUsers(int $tenantId, array $filters): Collection;
    private function getAvailabilityPatterns(User $user, Carbon $start, Carbon $end): Collection;
    private function getApprovedLeave(User $user, Carbon $start, Carbon $end): Collection;
    private function getAssignedShifts(User $user, Carbon $start, Carbon $end): Collection;
    private function getActiveCertifications(User $user): Collection;
    private function getWeeklyHoursCommitted(User $user, Carbon $start, Carbon $end): array;
    private function getStaffingRequirements(array $filters): Collection;
    private function countAvailableWithSkill(array $availability, Carbon $date, int $roleId): int;
}
```

---

## 2.43 Tenant Branding

### Branding Database

```php
Schema::create('tenant_branding', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('logo_path')->nullable();
    $table->string('favicon_path')->nullable();
    $table->string('primary_color')->nullable(); // Hex color
    $table->string('secondary_color')->nullable();
    $table->string('accent_color')->nullable();
    $table->string('custom_domain')->nullable();
    $table->boolean('remove_plannrly_branding')->default(false);
    $table->string('login_background_path')->nullable();
    $table->text('custom_css')->nullable();
    $table->json('email_template_overrides')->nullable();
    $table->timestamps();
});
```

### Branding Service

```php
class BrandingService
{
    /**
     * Get branding for tenant based on subscription tier
     */
    public function getBrandingForTenant(Tenant $tenant): array
    {
        $branding = $tenant->branding ?? new TenantBranding();
        $tier = $tenant->getSubscriptionTier();

        return [
            'logo' => $branding->logo_path ? Storage::url($branding->logo_path) : null,
            'favicon' => $tier === 'enterprise' ? $branding->favicon_path : null,
            'colors' => $tier !== 'basic' ? [
                'primary' => $branding->primary_color ?? '#6366f1',
                'secondary' => $branding->secondary_color ?? '#4f46e5',
                'accent' => $branding->accent_color ?? '#818cf8',
            ] : null,
            'custom_domain' => $tier === 'enterprise' ? $branding->custom_domain : null,
            'show_plannrly_branding' => $tier !== 'enterprise' || !$branding->remove_plannrly_branding,
            'custom_css' => $tier === 'enterprise' ? $branding->custom_css : null,
        ];
    }

    /**
     * Update branding with tier restrictions
     */
    public function updateBranding(Tenant $tenant, array $data): TenantBranding
    {
        $tier = $tenant->getSubscriptionTier();
        $allowed = $this->getAllowedFields($tier);

        $filteredData = array_intersect_key($data, array_flip($allowed));

        return TenantBranding::updateOrCreate(
            ['tenant_id' => $tenant->id],
            $filteredData
        );
    }

    private function getAllowedFields(string $tier): array
    {
        return match ($tier) {
            'basic' => ['logo_path'],
            'professional' => ['logo_path', 'primary_color', 'secondary_color', 'accent_color'],
            'enterprise' => [
                'logo_path', 'favicon_path', 'primary_color', 'secondary_color',
                'accent_color', 'custom_domain', 'remove_plannrly_branding',
                'login_background_path', 'custom_css', 'email_template_overrides'
            ],
            default => ['logo_path'],
        };
    }
}
```

---

## 2.44 Performance SLA Monitoring

### SLA Configuration

```php
// config/sla.php
return [
    'tiers' => [
        'basic' => [
            'uptime' => 99.0,
            'page_load_ms' => 3000,
            'api_response_ms' => 1000,
            'support_response_hours' => 48,
            'backup_frequency' => 'daily',
        ],
        'professional' => [
            'uptime' => 99.5,
            'page_load_ms' => 2000,
            'api_response_ms' => 500,
            'support_response_hours' => 24,
            'backup_frequency' => 'hourly',
        ],
        'enterprise' => [
            'uptime' => 99.9,
            'page_load_ms' => 1000,
            'api_response_ms' => 200,
            'support_response_hours' => 4,
            'backup_frequency' => 'realtime',
        ],
    ],
];
```

### Performance Monitoring Service

```php
class PerformanceMonitoringService
{
    public function recordApiResponse(string $endpoint, int $durationMs, string $tier): void
    {
        $threshold = config("sla.tiers.{$tier}.api_response_ms");

        if ($durationMs > $threshold) {
            $this->logSlaViolation('api_response', $endpoint, $durationMs, $threshold, $tier);
        }
    }

    public function getUptimeForPeriod(Carbon $start, Carbon $end): float;
    public function getSlaComplianceReport(string $tier, Carbon $start, Carbon $end): array;
    private function logSlaViolation(string $type, string $context, int $actual, int $threshold, string $tier): void;
}
```

---

## 2.45 In-App Help & AI Chat

### Help System Database

```php
Schema::create('help_articles', function (Blueprint $table) {
    $table->id();
    $table->string('slug')->unique();
    $table->string('title');
    $table->text('content'); // Markdown content
    $table->string('category'); // getting_started, scheduling, time_tracking, etc.
    $table->json('tags')->nullable();
    $table->string('feature_context')->nullable(); // Which feature this relates to
    $table->integer('sort_order')->default(0);
    $table->boolean('is_published')->default(true);
    $table->timestamps();

    $table->fullText(['title', 'content']);
    $table->index(['category', 'is_published']);
});

Schema::create('help_tooltips', function (Blueprint $table) {
    $table->id();
    $table->string('element_id')->unique(); // DOM element ID or data attribute
    $table->string('title');
    $table->text('content');
    $table->string('placement')->default('top'); // top, bottom, left, right
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('ai_chat_conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('title')->nullable();
    $table->string('status')->default('active'); // active, archived
    $table->integer('message_count')->default(0);
    $table->integer('token_count')->default(0);
    $table->timestamps();

    $table->index(['user_id', 'status']);
});

Schema::create('ai_chat_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained('ai_chat_conversations')->cascadeOnDelete();
    $table->string('role'); // user, assistant, system
    $table->text('content');
    $table->integer('tokens')->default(0);
    $table->json('metadata')->nullable(); // Context, citations, etc.
    $table->timestamps();
});

Schema::create('ai_usage_tracking', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->integer('requests')->default(0);
    $table->integer('tokens_used')->default(0);
    $table->decimal('cost', 10, 4)->default(0);
    $table->timestamps();

    $table->unique(['tenant_id', 'date']);
});
```

### Help Service

```php
class HelpService
{
    /**
     * Search help articles
     */
    public function searchArticles(string $query, ?string $category = null): Collection
    {
        return HelpArticle::query()
            ->where('is_published', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->whereFullText(['title', 'content'], $query)
            ->orderByDesc('sort_order')
            ->limit(20)
            ->get();
    }

    /**
     * Get contextual help for a feature
     */
    public function getContextualHelp(string $featureContext): Collection
    {
        return HelpArticle::where('feature_context', $featureContext)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get tooltip for an element
     */
    public function getTooltip(string $elementId): ?HelpTooltip
    {
        return HelpTooltip::where('element_id', $elementId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get articles by category
     */
    public function getArticlesByCategory(string $category): Collection
    {
        return HelpArticle::where('category', $category)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all categories with article counts
     */
    public function getCategories(): Collection
    {
        return HelpArticle::where('is_published', true)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();
    }
}
```

### AI Chat Service

```php
class AIChatService
{
    private const MAX_CONTEXT_MESSAGES = 10;
    private const SYSTEM_PROMPT = <<<PROMPT
You are a helpful assistant for Plannrly, a workforce scheduling platform.
Help users understand features, configure settings, and solve problems.
Be concise and practical. Reference specific features when relevant.
If you don't know something, say so rather than guessing.
PROMPT;

    /**
     * Check if user can access AI chat
     */
    public function canAccessAIChat(User $user): bool
    {
        // Must be business admin (not regular employee)
        if ($user->isEmployee()) {
            return false;
        }

        // Must have Professional or Enterprise plan
        $tier = $user->tenant->getSubscriptionTier();
        return in_array($tier, ['professional', 'enterprise']);
    }

    /**
     * Send message and get AI response
     */
    public function chat(User $user, string $message, ?int $conversationId = null): AIChatMessage
    {
        if (!$this->canAccessAIChat($user)) {
            throw new UnauthorizedException('AI chat requires Professional or Enterprise plan');
        }

        // Get or create conversation
        $conversation = $conversationId
            ? AIChatConversation::findOrFail($conversationId)
            : $this->createConversation($user);

        // Store user message
        $userMessage = $this->storeMessage($conversation, 'user', $message);

        // Build context from recent messages
        $context = $this->buildContext($conversation);

        // Call AI API
        $response = $this->callAI($context, $message);

        // Store assistant response
        $assistantMessage = $this->storeMessage(
            $conversation,
            'assistant',
            $response['content'],
            $response['tokens']
        );

        // Track usage
        $this->trackUsage($user->tenant_id, $response['tokens'], $response['cost']);

        return $assistantMessage;
    }

    /**
     * Create new conversation
     */
    public function createConversation(User $user): AIChatConversation
    {
        return AIChatConversation::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);
    }

    /**
     * Build context from conversation history
     */
    private function buildContext(AIChatConversation $conversation): array
    {
        $messages = $conversation->messages()
            ->orderByDesc('created_at')
            ->limit(self::MAX_CONTEXT_MESSAGES)
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        array_unshift($messages, ['role' => 'system', 'content' => self::SYSTEM_PROMPT]);

        return $messages;
    }

    /**
     * Call AI API (Claude or OpenAI)
     */
    private function callAI(array $context, string $message): array
    {
        $context[] = ['role' => 'user', 'content' => $message];

        // Using Claude API
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 1024,
            'messages' => $context,
        ]);

        $data = $response->json();

        return [
            'content' => $data['content'][0]['text'] ?? '',
            'tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
            'cost' => $this->calculateCost($data['usage'] ?? []),
        ];
    }

    /**
     * Store message in database
     */
    private function storeMessage(
        AIChatConversation $conversation,
        string $role,
        string $content,
        int $tokens = 0
    ): AIChatMessage {
        $message = AIChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
            'tokens' => $tokens,
        ]);

        $conversation->increment('message_count');
        $conversation->increment('token_count', $tokens);

        return $message;
    }

    /**
     * Track AI usage for billing/limits
     */
    private function trackUsage(int $tenantId, int $tokens, float $cost): void
    {
        AIUsageTracking::updateOrCreate(
            ['tenant_id' => $tenantId, 'date' => today()],
            []
        )->increment('requests');

        AIUsageTracking::where('tenant_id', $tenantId)
            ->where('date', today())
            ->increment('tokens_used', $tokens);

        AIUsageTracking::where('tenant_id', $tenantId)
            ->where('date', today())
            ->increment('cost', $cost);
    }

    /**
     * Calculate cost based on token usage
     */
    private function calculateCost(array $usage): float
    {
        $inputCost = ($usage['input_tokens'] ?? 0) * 0.00025 / 1000;
        $outputCost = ($usage['output_tokens'] ?? 0) * 0.00125 / 1000;
        return $inputCost + $outputCost;
    }

    /**
     * Get user's conversation history
     */
    public function getConversations(User $user): Collection
    {
        return AIChatConversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();
    }

    /**
     * Get monthly usage for tenant
     */
    public function getMonthlyUsage(int $tenantId): array
    {
        $usage = AIUsageTracking::where('tenant_id', $tenantId)
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('SUM(requests) as requests, SUM(tokens_used) as tokens, SUM(cost) as cost')
            ->first();

        return [
            'requests' => $usage->requests ?? 0,
            'tokens' => $usage->tokens ?? 0,
            'cost' => $usage->cost ?? 0,
        ];
    }
}
```

### Help & AI Chat API

```php
// routes/api.php
Route::prefix('help')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/search', [HelpController::class, 'search']);
        Route::get('/articles', [HelpController::class, 'index']);
        Route::get('/articles/{slug}', [HelpController::class, 'show']);
        Route::get('/categories', [HelpController::class, 'categories']);
        Route::get('/tooltip/{elementId}', [HelpController::class, 'tooltip']);
        Route::get('/contextual/{feature}', [HelpController::class, 'contextual']);
    });

Route::prefix('ai-chat')
    ->middleware(['auth:sanctum', 'can:access-ai-chat'])
    ->group(function () {
        Route::get('/conversations', [AIChatController::class, 'index']);
        Route::post('/conversations', [AIChatController::class, 'create']);
        Route::get('/conversations/{conversation}', [AIChatController::class, 'show']);
        Route::post('/conversations/{conversation}/messages', [AIChatController::class, 'sendMessage']);
        Route::delete('/conversations/{conversation}', [AIChatController::class, 'archive']);
        Route::get('/usage', [AIChatController::class, 'usage']);
    });
```

---

## 2.46 Employee Invitation & Onboarding

### Invitation Database

```php
Schema::create('employee_invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
    $table->string('email')->index();
    $table->string('first_name')->nullable();
    $table->string('last_name')->nullable();
    $table->string('token', 64)->unique();
    $table->string('status')->default('pending'); // pending, accepted, expired, cancelled
    $table->json('role_assignments')->nullable(); // Pre-configured roles
    $table->json('department_ids')->nullable(); // Pre-assigned departments
    $table->json('business_role_ids')->nullable(); // Pre-assigned business roles
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();

    $table->unique(['tenant_id', 'email', 'status']);
});

Schema::create('onboarding_checklists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('step'); // profile, availability, documents, policies, tour
    $table->boolean('completed')->default(false);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'step']);
});

Schema::create('onboarding_configurations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->json('required_steps'); // ['profile', 'availability', 'policies']
    $table->json('optional_steps'); // ['documents', 'tour']
    $table->boolean('require_photo')->default(false);
    $table->boolean('require_availability')->default(true);
    $table->boolean('require_policy_acknowledgement')->default(true);
    $table->text('welcome_message')->nullable();
    $table->timestamps();
});
```

### Invitation Status Enum

```php
enum InvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
```

### Invitation Service

```php
class InvitationService
{
    /**
     * Send employee invitation
     */
    public function sendInvitation(
        User $inviter,
        string $email,
        ?string $firstName = null,
        ?string $lastName = null,
        array $roleAssignments = [],
        array $departmentIds = [],
        array $businessRoleIds = []
    ): EmployeeInvitation {
        // Check for existing pending invitation
        $existing = EmployeeInvitation::where('tenant_id', $inviter->tenant_id)
            ->where('email', $email)
            ->where('status', InvitationStatus::Pending)
            ->first();

        if ($existing) {
            throw new InvitationExistsException('Pending invitation already exists for this email');
        }

        // Check if user already exists in tenant
        $existingUser = User::where('tenant_id', $inviter->tenant_id)
            ->where('email', $email)
            ->first();

        if ($existingUser) {
            throw new UserExistsException('User already exists in this organization');
        }

        $invitation = EmployeeInvitation::create([
            'tenant_id' => $inviter->tenant_id,
            'invited_by' => $inviter->id,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'token' => Str::random(64),
            'role_assignments' => $roleAssignments,
            'department_ids' => $departmentIds,
            'business_role_ids' => $businessRoleIds,
            'expires_at' => now()->addDays(7),
        ]);

        // Send invitation email
        Mail::to($email)->send(new EmployeeInvitationMail($invitation));

        return $invitation;
    }

    /**
     * Accept invitation and create user
     */
    public function acceptInvitation(
        string $token,
        string $password,
        ?string $firstName = null,
        ?string $lastName = null
    ): User {
        $invitation = EmployeeInvitation::where('token', $token)
            ->where('status', InvitationStatus::Pending)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Create user
        $user = User::create([
            'tenant_id' => $invitation->tenant_id,
            'email' => $invitation->email,
            'first_name' => $firstName ?? $invitation->first_name,
            'last_name' => $lastName ?? $invitation->last_name,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Apply pre-configured roles
        if ($invitation->role_assignments) {
            foreach ($invitation->role_assignments as $assignment) {
                UserRoleAssignment::create([
                    'user_id' => $user->id,
                    'system_role' => $assignment['system_role'],
                    'scope_type' => $assignment['scope_type'] ?? null,
                    'scope_id' => $assignment['scope_id'] ?? null,
                ]);
            }
        } else {
            // Default to employee role
            UserRoleAssignment::create([
                'user_id' => $user->id,
                'system_role' => SystemRole::Employee->value,
            ]);
        }

        // Attach business roles
        if ($invitation->business_role_ids) {
            $user->businessRoles()->attach($invitation->business_role_ids);
        }

        // Mark invitation as accepted
        $invitation->update([
            'status' => InvitationStatus::Accepted,
            'accepted_at' => now(),
        ]);

        // Initialize onboarding checklist
        $this->initializeOnboarding($user);

        return $user;
    }

    /**
     * Bulk import employees from CSV
     */
    public function bulkImport(User $inviter, array $employees): BulkImportResult
    {
        $results = new BulkImportResult();

        foreach ($employees as $row) {
            try {
                $invitation = $this->sendInvitation(
                    $inviter,
                    $row['email'],
                    $row['first_name'] ?? null,
                    $row['last_name'] ?? null,
                    $row['role_assignments'] ?? [],
                    $row['department_ids'] ?? [],
                    $row['business_role_ids'] ?? []
                );
                $results->addSuccess($invitation);
            } catch (\Exception $e) {
                $results->addError($row['email'], $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Initialize onboarding checklist for user
     */
    private function initializeOnboarding(User $user): void
    {
        $config = OnboardingConfiguration::where('tenant_id', $user->tenant_id)->first();

        $steps = $config?->required_steps ?? ['profile', 'availability'];

        foreach ($steps as $step) {
            OnboardingChecklist::create([
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'step' => $step,
            ]);
        }
    }

    /**
     * Complete an onboarding step
     */
    public function completeOnboardingStep(User $user, string $step): void
    {
        OnboardingChecklist::where('user_id', $user->id)
            ->where('step', $step)
            ->update([
                'completed' => true,
                'completed_at' => now(),
            ]);
    }

    /**
     * Get onboarding progress
     */
    public function getOnboardingProgress(User $user): array
    {
        $checklist = OnboardingChecklist::where('user_id', $user->id)->get();

        $total = $checklist->count();
        $completed = $checklist->where('completed', true)->count();

        return [
            'steps' => $checklist,
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 100,
            'is_complete' => $completed === $total,
        ];
    }

    /**
     * Resend invitation
     */
    public function resendInvitation(EmployeeInvitation $invitation): void
    {
        if ($invitation->status !== InvitationStatus::Pending) {
            throw new InvalidInvitationException('Can only resend pending invitations');
        }

        // Update expiry
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new EmployeeInvitationMail($invitation));
    }

    /**
     * Cancel invitation
     */
    public function cancelInvitation(EmployeeInvitation $invitation): void
    {
        $invitation->update(['status' => InvitationStatus::Cancelled]);
    }
}
```

### Invitation API

```php
Route::prefix('invitations')
    ->middleware(['auth:sanctum', 'can:manage-employees'])
    ->group(function () {
        Route::get('/', [InvitationController::class, 'index']);
        Route::post('/', [InvitationController::class, 'store']);
        Route::post('/bulk', [InvitationController::class, 'bulkImport']);
        Route::post('/{invitation}/resend', [InvitationController::class, 'resend']);
        Route::delete('/{invitation}', [InvitationController::class, 'cancel']);
    });

// Public routes for accepting invitations
Route::get('/invite/{token}', [InvitationController::class, 'show']);
Route::post('/invite/{token}/accept', [InvitationController::class, 'accept']);

// Onboarding routes
Route::prefix('onboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/progress', [OnboardingController::class, 'progress']);
        Route::post('/complete/{step}', [OnboardingController::class, 'completeStep']);
        Route::get('/welcome', [OnboardingController::class, 'welcome']);
    });
```

---

## 2.47 Employee Offboarding

### Offboarding Database

```php
Schema::create('employee_offboardings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('initiated_by')->constrained('users');
    $table->date('effective_date');
    $table->string('reason'); // resignation, termination, retirement, contract_end, other
    $table->text('notes')->nullable();
    $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
    $table->json('completed_tasks')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});

Schema::create('offboarding_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('is_system_task')->default(false); // System tasks run automatically
    $table->string('system_action')->nullable(); // revoke_access, remove_shifts, archive_data
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Offboarding Status Enum

```php
enum OffboardingStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}

enum OffboardingReason: string
{
    case Resignation = 'resignation';
    case Termination = 'termination';
    case Retirement = 'retirement';
    case ContractEnd = 'contract_end';
    case Other = 'other';
}
```

### Offboarding Service

```php
class OffboardingService
{
    /**
     * Initiate employee offboarding
     */
    public function initiateOffboarding(
        User $employee,
        User $initiatedBy,
        Carbon $effectiveDate,
        OffboardingReason $reason,
        ?string $notes = null
    ): EmployeeOffboarding {
        // Validate employee is not already being offboarded
        $existing = EmployeeOffboarding::where('user_id', $employee->id)
            ->whereIn('status', [OffboardingStatus::Pending, OffboardingStatus::InProgress])
            ->first();

        if ($existing) {
            throw new OffboardingExistsException('Employee already has pending offboarding');
        }

        $offboarding = EmployeeOffboarding::create([
            'user_id' => $employee->id,
            'tenant_id' => $employee->tenant_id,
            'initiated_by' => $initiatedBy->id,
            'effective_date' => $effectiveDate,
            'reason' => $reason->value,
            'notes' => $notes,
            'status' => OffboardingStatus::Pending,
        ]);

        // Notify HR/admin
        $this->notifyOffboardingInitiated($offboarding);

        return $offboarding;
    }

    /**
     * Execute offboarding process
     */
    public function executeOffboarding(EmployeeOffboarding $offboarding): void
    {
        $offboarding->update(['status' => OffboardingStatus::InProgress]);

        $completedTasks = [];

        // 1. Remove from future schedules
        $removedShifts = $this->removeFromFutureSchedules($offboarding->user);
        $completedTasks['remove_shifts'] = ['count' => $removedShifts];

        // 2. Reassign pending tasks/handovers
        $reassigned = $this->reassignPendingTasks($offboarding->user);
        $completedTasks['reassign_tasks'] = ['count' => $reassigned];

        // 3. Archive messages
        $this->archiveMessages($offboarding->user);
        $completedTasks['archive_messages'] = ['completed' => true];

        // 4. Revoke system access (deactivate user)
        $this->revokeAccess($offboarding->user);
        $completedTasks['revoke_access'] = ['completed' => true];

        // Complete offboarding
        $offboarding->update([
            'status' => OffboardingStatus::Completed,
            'completed_tasks' => $completedTasks,
            'completed_at' => now(),
        ]);

        // Send completion notification
        $this->notifyOffboardingCompleted($offboarding);
    }

    /**
     * Remove user from all future shifts
     */
    private function removeFromFutureSchedules(User $user): int
    {
        return Shift::where('user_id', $user->id)
            ->where('date', '>', today())
            ->update(['user_id' => null, 'status' => ShiftStatus::Draft]);
    }

    /**
     * Reassign pending tasks to manager or unassigned
     */
    private function reassignPendingTasks(User $user): int
    {
        // Reassign shift notes/tasks they created
        ShiftNote::where('created_by', $user->id)
            ->where('date', '>', today())
            ->update(['created_by' => $user->tenant->getPrimaryAdmin()->id]);

        // Cancel their pending leave requests
        LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        // Cancel their pending swap requests
        ShiftSwapRequest::where('requesting_user_id', $user->id)
            ->where('status', SwapRequestStatus::Pending)
            ->update(['status' => SwapRequestStatus::Cancelled]);

        return 1;
    }

    /**
     * Archive user's messages
     */
    private function archiveMessages(User $user): void
    {
        // Mark messages as archived rather than deleting
        Message::where('sender_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->update(['is_archived' => true]);
    }

    /**
     * Revoke system access (soft delete)
     */
    private function revokeAccess(User $user): void
    {
        $user->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);

        // Revoke all sessions
        $user->tokens()->delete();
    }

    /**
     * Cancel offboarding
     */
    public function cancelOffboarding(EmployeeOffboarding $offboarding): void
    {
        if ($offboarding->status === OffboardingStatus::Completed) {
            throw new OffboardingCompletedException('Cannot cancel completed offboarding');
        }

        $offboarding->update(['status' => OffboardingStatus::Cancelled]);
    }

    /**
     * Get offboarding tasks for tenant
     */
    public function getOffboardingTasks(int $tenantId): Collection
    {
        return OffboardingTask::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Notify relevant parties when offboarding is initiated
     */
    private function notifyOffboardingInitiated(EmployeeOffboarding $offboarding): void
    {
        $employee = $offboarding->user;
        $initiator = $offboarding->initiatedBy;
        $manager = $employee->getDirectManager();
        $hrAdmins = $employee->tenant->getHRAdmins();

        $notification = new OffboardingInitiatedNotification($offboarding);

        // Notify the employee (all channels)
        $employee->notify($notification);

        // Notify the employee's direct manager (if different from initiator)
        if ($manager && $manager->id !== $initiator->id) {
            $manager->notify($notification);
        }

        // Notify HR admins
        foreach ($hrAdmins as $admin) {
            if ($admin->id !== $initiator->id) {
                $admin->notify($notification);
            }
        }
    }

    /**
     * Notify relevant parties when offboarding is completed
     */
    private function notifyOffboardingCompleted(EmployeeOffboarding $offboarding): void
    {
        $employee = $offboarding->user;
        $initiator = $offboarding->initiatedBy;
        $manager = $employee->getDirectManager();
        $hrAdmins = $employee->tenant->getHRAdmins();

        $notification = new OffboardingCompletedNotification($offboarding);

        // Notify the employee (email only - they may have lost system access)
        Mail::to($employee->email)->send(new OffboardingCompleteMail($offboarding));

        // Notify the employee's direct manager
        if ($manager) {
            $manager->notify($notification);
        }

        // Notify HR admins
        foreach ($hrAdmins as $admin) {
            $admin->notify($notification);
        }

        // Notify the initiator if different from above
        if ($initiator->id !== $manager?->id && !$hrAdmins->contains('id', $initiator->id)) {
            $initiator->notify($notification);
        }
    }
}
```

### Offboarding API

```php
Route::prefix('offboarding')
    ->middleware(['auth:sanctum', 'can:manage-employees'])
    ->group(function () {
        Route::get('/', [OffboardingController::class, 'index']);
        Route::post('/', [OffboardingController::class, 'initiate']);
        Route::get('/{offboarding}', [OffboardingController::class, 'show']);
        Route::post('/{offboarding}/execute', [OffboardingController::class, 'execute']);
        Route::post('/{offboarding}/cancel', [OffboardingController::class, 'cancel']);
        Route::get('/tasks', [OffboardingController::class, 'tasks']);
    });
```

---

## 2.48 Bulk Operations

### Bulk Operations Service

```php
class BulkOperationsService
{
    /**
     * Bulk create shifts from template
     */
    public function bulkCreateShifts(
        User $creator,
        array $shiftData,
        Carbon $startDate,
        Carbon $endDate,
        array $options = []
    ): BulkOperationResult {
        $result = new BulkOperationResult();

        $dates = CarbonPeriod::create($startDate, $endDate);

        foreach ($dates as $date) {
            // Skip excluded days
            if (isset($options['exclude_days']) && in_array($date->dayOfWeek, $options['exclude_days'])) {
                continue;
            }

            // Skip holidays if configured
            if ($options['skip_holidays'] ?? false) {
                if (Holiday::where('tenant_id', $creator->tenant_id)->where('date', $date)->exists()) {
                    continue;
                }
            }

            foreach ($shiftData as $shift) {
                try {
                    $created = Shift::create([
                        'tenant_id' => $creator->tenant_id,
                        'location_id' => $shift['location_id'],
                        'department_id' => $shift['department_id'],
                        'business_role_id' => $shift['business_role_id'],
                        'user_id' => $shift['user_id'] ?? null,
                        'date' => $date,
                        'start_time' => $shift['start_time'],
                        'end_time' => $shift['end_time'],
                        'break_duration_minutes' => $shift['break_duration_minutes'] ?? 0,
                        'status' => ShiftStatus::Draft,
                    ]);
                    $result->addSuccess($created);
                } catch (\Exception $e) {
                    $result->addError($date->format('Y-m-d'), $e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Bulk assign employees to shifts
     */
    public function bulkAssignShifts(array $assignments): BulkOperationResult
    {
        $result = new BulkOperationResult();

        foreach ($assignments as $assignment) {
            try {
                $shift = Shift::findOrFail($assignment['shift_id']);
                $shift->update(['user_id' => $assignment['user_id']]);
                $result->addSuccess($shift);
            } catch (\Exception $e) {
                $result->addError($assignment['shift_id'], $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bulk unassign employees from shifts
     */
    public function bulkUnassignShifts(array $shiftIds): BulkOperationResult
    {
        $result = new BulkOperationResult();

        foreach ($shiftIds as $shiftId) {
            try {
                $shift = Shift::findOrFail($shiftId);
                $shift->update(['user_id' => null]);
                $result->addSuccess($shift);
            } catch (\Exception $e) {
                $result->addError($shiftId, $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bulk publish shifts
     */
    public function bulkPublishShifts(
        array $shiftIds,
        bool $notifyEmployees = true
    ): BulkOperationResult {
        $result = new BulkOperationResult();

        $shifts = Shift::whereIn('id', $shiftIds)
            ->where('status', ShiftStatus::Draft)
            ->get();

        foreach ($shifts as $shift) {
            try {
                $shift->update(['status' => ShiftStatus::Published]);

                if ($notifyEmployees && $shift->user) {
                    $shift->user->notify(new ShiftPublishedNotification($shift));
                }

                $result->addSuccess($shift);
            } catch (\Exception $e) {
                $result->addError($shift->id, $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bulk delete shifts
     */
    public function bulkDeleteShifts(array $shiftIds, User $deletedBy): BulkOperationResult
    {
        $result = new BulkOperationResult();

        foreach ($shiftIds as $shiftId) {
            try {
                $shift = Shift::findOrFail($shiftId);

                // Don't delete published shifts without confirmation
                if ($shift->status === ShiftStatus::Published && $shift->user) {
                    $shift->user->notify(new ShiftCancelledNotification($shift));
                }

                $shift->delete();
                $result->addSuccess(['id' => $shiftId, 'deleted' => true]);
            } catch (\Exception $e) {
                $result->addError($shiftId, $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Copy week to another week
     */
    public function copyWeek(
        int $tenantId,
        Carbon $sourceStart,
        Carbon $targetStart,
        array $options = []
    ): BulkOperationResult {
        $result = new BulkOperationResult();

        $sourceEnd = $sourceStart->copy()->endOfWeek();
        $dayOffset = $sourceStart->diffInDays($targetStart);

        $shifts = Shift::where('tenant_id', $tenantId)
            ->whereBetween('date', [$sourceStart, $sourceEnd]);

        if (isset($options['location_id'])) {
            $shifts->where('location_id', $options['location_id']);
        }

        if (isset($options['department_id'])) {
            $shifts->where('department_id', $options['department_id']);
        }

        foreach ($shifts->get() as $shift) {
            try {
                $newShift = $shift->replicate();
                $newShift->date = $shift->date->copy()->addDays($dayOffset);
                $newShift->status = ShiftStatus::Draft;

                // Optionally copy assignments
                if (!($options['copy_assignments'] ?? true)) {
                    $newShift->user_id = null;
                }

                $newShift->save();
                $result->addSuccess($newShift);
            } catch (\Exception $e) {
                $result->addError($shift->id, $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bulk update employee details
     */
    public function bulkUpdateEmployees(array $updates): BulkOperationResult
    {
        $result = new BulkOperationResult();

        foreach ($updates as $update) {
            try {
                $user = User::findOrFail($update['user_id']);
                unset($update['user_id']);
                $user->update($update);
                $result->addSuccess($user);
            } catch (\Exception $e) {
                $result->addError($update['user_id'] ?? 'unknown', $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bulk assign roles to employees
     */
    public function bulkAssignRoles(array $userIds, array $businessRoleIds): BulkOperationResult
    {
        $result = new BulkOperationResult();

        foreach ($userIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                $user->businessRoles()->syncWithoutDetaching($businessRoleIds);
                $result->addSuccess($user);
            } catch (\Exception $e) {
                $result->addError($userId, $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bulk deactivate employees
     */
    public function bulkDeactivateEmployees(array $userIds, User $deactivatedBy): BulkOperationResult
    {
        $result = new BulkOperationResult();

        foreach ($userIds as $userId) {
            try {
                $user = User::findOrFail($userId);

                // Don't deactivate admins without explicit confirmation
                if ($user->isAdmin()) {
                    $result->addError($userId, 'Cannot bulk deactivate admin users');
                    continue;
                }

                $user->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                ]);

                $result->addSuccess($user);
            } catch (\Exception $e) {
                $result->addError($userId, $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Bulk approve/reject leave requests
     */
    public function bulkProcessLeaveRequests(
        array $requestIds,
        string $action,
        User $processor,
        ?string $notes = null
    ): BulkOperationResult {
        $result = new BulkOperationResult();

        foreach ($requestIds as $requestId) {
            try {
                $request = LeaveRequest::findOrFail($requestId);

                if ($action === 'approve') {
                    $request->approve($processor, $notes);
                } else {
                    $request->reject($processor, $notes);
                }

                $result->addSuccess($request);
            } catch (\Exception $e) {
                $result->addError($requestId, $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Export employees to CSV
     */
    public function exportEmployeesToCsv(int $tenantId, array $filters = []): string
    {
        $query = User::where('tenant_id', $tenantId);

        if (isset($filters['department_id'])) {
            $query->whereHas('businessRoles', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $users = $query->get();

        $csv = "ID,First Name,Last Name,Email,Phone,Status,Created At\n";

        foreach ($users as $user) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $user->id,
                $user->first_name,
                $user->last_name,
                $user->email,
                $user->phone ?? '',
                $user->is_active ? 'Active' : 'Inactive',
                $user->created_at->format('Y-m-d')
            );
        }

        return $csv;
    }
}
```

### Bulk Operation Result Class

```php
class BulkOperationResult
{
    private array $successes = [];
    private array $errors = [];

    public function addSuccess(mixed $item): void
    {
        $this->successes[] = $item;
    }

    public function addError(mixed $identifier, string $message): void
    {
        $this->errors[] = [
            'identifier' => $identifier,
            'message' => $message,
        ];
    }

    public function getSuccessCount(): int
    {
        return count($this->successes);
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    public function getSuccesses(): array
    {
        return $this->successes;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isFullySuccessful(): bool
    {
        return empty($this->errors);
    }

    public function toArray(): array
    {
        return [
            'success_count' => $this->getSuccessCount(),
            'error_count' => $this->getErrorCount(),
            'successes' => $this->successes,
            'errors' => $this->errors,
        ];
    }
}
```

### Bulk Operations API

```php
Route::prefix('bulk')
    ->middleware(['auth:sanctum', 'bulk.rate-limit'])
    ->group(function () {
        // Shift operations
        Route::post('/shifts/create', [BulkShiftController::class, 'create'])
            ->middleware('can:manage-schedules');
        Route::post('/shifts/assign', [BulkShiftController::class, 'assign'])
            ->middleware('can:manage-schedules');
        Route::post('/shifts/unassign', [BulkShiftController::class, 'unassign'])
            ->middleware('can:manage-schedules');
        Route::post('/shifts/publish', [BulkShiftController::class, 'publish'])
            ->middleware('can:manage-schedules');
        Route::post('/shifts/delete', [BulkShiftController::class, 'delete'])
            ->middleware('can:manage-schedules');
        Route::post('/shifts/copy-week', [BulkShiftController::class, 'copyWeek'])
            ->middleware('can:manage-schedules');

        // Employee operations
        Route::post('/employees/update', [BulkEmployeeController::class, 'update'])
            ->middleware('can:manage-employees');
        Route::post('/employees/assign-roles', [BulkEmployeeController::class, 'assignRoles'])
            ->middleware('can:manage-employees');
        Route::post('/employees/deactivate', [BulkEmployeeController::class, 'deactivate'])
            ->middleware('can:manage-employees');
        Route::get('/employees/export', [BulkEmployeeController::class, 'export'])
            ->middleware('can:manage-employees');

        // Leave/approval operations
        Route::post('/leave-requests/process', [BulkApprovalController::class, 'processLeave'])
            ->middleware('can:approve-leave');
        Route::post('/timesheets/approve', [BulkApprovalController::class, 'approveTimesheets'])
            ->middleware('can:approve-timesheets');
    });
```

### Bulk Operations Rate Limiting

Rate limits are applied per-request based on the number of items being processed:

| Plan | Items per Request | Daily Item Limit |
|------|-------------------|------------------|
| Basic | 100 | 1,000 |
| Professional | 500 | 10,000 |
| Enterprise | 2,000 | Unlimited |

```php
class BulkRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $tier = $tenant->getSubscriptionTier();

        // Get limits for tier
        $limits = $this->getLimitsForTier($tier);

        // Count items in request
        $itemCount = $this->countItemsInRequest($request);

        // Check per-request limit
        if ($itemCount > $limits['per_request']) {
            return response()->json([
                'error' => 'Bulk operation exceeds per-request limit',
                'limit' => $limits['per_request'],
                'requested' => $itemCount,
                'upgrade_url' => route('subscription.upgrade'),
            ], 429);
        }

        // Check daily limit (skip for Enterprise)
        if ($limits['daily'] !== null) {
            $todayUsage = $this->getTodayUsage($tenant->id);

            if ($todayUsage + $itemCount > $limits['daily']) {
                return response()->json([
                    'error' => 'Daily bulk operation limit exceeded',
                    'limit' => $limits['daily'],
                    'used_today' => $todayUsage,
                    'requested' => $itemCount,
                    'resets_at' => now()->endOfDay()->toIso8601String(),
                ], 429);
            }
        }

        // Process request
        $response = $next($request);

        // Track usage after successful request
        if ($response->isSuccessful()) {
            $this->trackUsage($tenant->id, $itemCount);
        }

        // Add rate limit headers
        return $response->withHeaders([
            'X-Bulk-Limit-Per-Request' => $limits['per_request'],
            'X-Bulk-Limit-Daily' => $limits['daily'] ?? 'unlimited',
            'X-Bulk-Used-Today' => $this->getTodayUsage($tenant->id),
        ]);
    }

    private function getLimitsForTier(string $tier): array
    {
        return match($tier) {
            'basic', 'starter' => ['per_request' => 100, 'daily' => 1000],
            'professional' => ['per_request' => 500, 'daily' => 10000],
            'enterprise' => ['per_request' => 2000, 'daily' => null],
            default => ['per_request' => 100, 'daily' => 1000],
        };
    }

    private function countItemsInRequest(Request $request): int
    {
        // Count based on common bulk operation fields
        $countFields = ['shift_ids', 'user_ids', 'request_ids', 'assignments', 'shifts', 'updates'];

        foreach ($countFields as $field) {
            if ($request->has($field) && is_array($request->input($field))) {
                return count($request->input($field));
            }
        }

        // For date range operations, estimate items
        if ($request->has('start_date') && $request->has('end_date')) {
            $days = Carbon::parse($request->input('start_date'))
                ->diffInDays(Carbon::parse($request->input('end_date'))) + 1;
            return $days * ($request->input('shifts_per_day', 1));
        }

        return 1;
    }

    private function getTodayUsage(int $tenantId): int
    {
        return Cache::get("bulk_usage:{$tenantId}:" . today()->format('Y-m-d'), 0);
    }

    private function trackUsage(int $tenantId, int $count): void
    {
        $key = "bulk_usage:{$tenantId}:" . today()->format('Y-m-d');
        $current = Cache::get($key, 0);
        Cache::put($key, $current + $count, now()->endOfDay());
    }
}
```

### Bulk Operation Design Notes

**Transaction Handling:** Bulk operations use a "partial success" model rather than all-or-nothing transactions. This means:
- Each item is processed independently
- Successful items are committed immediately
- Failed items are collected and reported in the response
- The BulkOperationResult tracks both successes and errors

This approach is preferred because:
1. Users get immediate value from successful operations
2. Large operations don't fail entirely due to a single bad item
3. Error reporting is granular and actionable
4. Retry logic can target only failed items

---

## 2.49 Employee Personal Insights

### Insights Database

```php
Schema::create('employee_insights_cache', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->date('period_start');
    $table->date('period_end');
    $table->string('period_type'); // week, month, quarter, year
    $table->decimal('hours_scheduled', 8, 2)->default(0);
    $table->decimal('hours_worked', 8, 2)->default(0);
    $table->decimal('overtime_hours', 8, 2)->default(0);
    $table->integer('shifts_scheduled')->default(0);
    $table->integer('shifts_worked')->default(0);
    $table->integer('late_arrivals')->default(0);
    $table->integer('early_departures')->default(0);
    $table->integer('no_shows')->default(0);
    $table->decimal('punctuality_rate', 5, 2)->default(100);
    $table->decimal('attendance_rate', 5, 2)->default(100);
    $table->decimal('fairness_score', 5, 2)->nullable();
    $table->json('shift_type_breakdown')->nullable(); // {morning: 5, evening: 3, night: 2}
    $table->json('day_of_week_breakdown')->nullable(); // {mon: 2, tue: 2, ...}
    $table->timestamps();

    $table->unique(['user_id', 'period_start', 'period_type']);
});
```

### Employee Insights Service

```php
class EmployeeInsightsService
{
    /**
     * Get insights for employee
     */
    public function getInsights(User $user, string $periodType = 'month'): EmployeeInsights
    {
        [$startDate, $endDate] = $this->getPeriodDates($periodType);

        // Try to get from cache
        $cached = EmployeeInsightsCache::where('user_id', $user->id)
            ->where('period_start', $startDate)
            ->where('period_type', $periodType)
            ->first();

        if ($cached && $cached->updated_at->gt(now()->subHours(1))) {
            return EmployeeInsights::fromCache($cached);
        }

        // Calculate fresh insights
        return $this->calculateInsights($user, $startDate, $endDate, $periodType);
    }

    /**
     * Calculate insights for period
     */
    private function calculateInsights(
        User $user,
        Carbon $startDate,
        Carbon $endDate,
        string $periodType
    ): EmployeeInsights {
        // Get shifts for period
        $shifts = Shift::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('timeEntries')
            ->get();

        // Get time entries for period
        $timeEntries = TimeEntry::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Calculate metrics
        $hoursScheduled = $shifts->sum(fn ($s) => $s->duration_hours);
        $hoursWorked = $timeEntries->sum(fn ($te) => $te->worked_hours);
        $overtimeHours = max(0, $hoursWorked - ($user->target_hours_per_week * $this->getWeeksInPeriod($startDate, $endDate)));

        $shiftsScheduled = $shifts->count();
        $shiftsWorked = $shifts->filter(fn ($s) => $s->timeEntries->isNotEmpty())->count();

        // Attendance metrics
        $lateArrivals = $timeEntries->filter(fn ($te) => $te->was_late)->count();
        $earlyDepartures = $timeEntries->filter(fn ($te) => $te->left_early)->count();
        $noShows = $shifts->filter(fn ($s) => $s->date->lt(today()) && $s->timeEntries->isEmpty())->count();

        $punctualityRate = $shiftsWorked > 0
            ? round((($shiftsWorked - $lateArrivals) / $shiftsWorked) * 100, 2)
            : 100;

        $attendanceRate = $shiftsScheduled > 0
            ? round(($shiftsWorked / $shiftsScheduled) * 100, 2)
            : 100;

        // Shift type breakdown
        $shiftTypeBreakdown = $shifts->groupBy(function ($shift) {
            $hour = Carbon::parse($shift->start_time)->hour;
            if ($hour < 12) return 'morning';
            if ($hour < 17) return 'afternoon';
            if ($hour < 21) return 'evening';
            return 'night';
        })->map->count()->toArray();

        // Day of week breakdown
        $dayOfWeekBreakdown = $shifts->groupBy(fn ($s) => strtolower($s->date->format('D')))
            ->map->count()
            ->toArray();

        // Calculate fairness score
        $fairnessScore = $this->calculateFairnessScore(
            $user,
            $shifts,
            $hoursWorked,
            $shiftTypeBreakdown,
            $dayOfWeekBreakdown,
            $startDate,
            $endDate
        );

        // Cache the results
        $cache = EmployeeInsightsCache::updateOrCreate(
            [
                'user_id' => $user->id,
                'period_start' => $startDate,
                'period_type' => $periodType,
            ],
            [
                'tenant_id' => $user->tenant_id,
                'period_end' => $endDate,
                'hours_scheduled' => $hoursScheduled,
                'hours_worked' => $hoursWorked,
                'overtime_hours' => $overtimeHours,
                'shifts_scheduled' => $shiftsScheduled,
                'shifts_worked' => $shiftsWorked,
                'late_arrivals' => $lateArrivals,
                'early_departures' => $earlyDepartures,
                'no_shows' => $noShows,
                'punctuality_rate' => $punctualityRate,
                'attendance_rate' => $attendanceRate,
                'fairness_score' => $fairnessScore,
                'shift_type_breakdown' => $shiftTypeBreakdown,
                'day_of_week_breakdown' => $dayOfWeekBreakdown,
            ]
        );

        return EmployeeInsights::fromCache($cache);
    }

    /**
     * Get hours trend over time
     */
    public function getHoursTrend(User $user, int $periods = 12): array
    {
        $trend = [];
        $currentDate = now()->startOfMonth();

        for ($i = $periods - 1; $i >= 0; $i--) {
            $periodStart = $currentDate->copy()->subMonths($i)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();

            $hours = TimeEntry::where('user_id', $user->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('worked_hours');

            $trend[] = [
                'period' => $periodStart->format('M Y'),
                'hours' => round($hours, 2),
            ];
        }

        return $trend;
    }

    /**
     * Get leave balance projection
     */
    public function getLeaveBalanceProjection(User $user): array
    {
        $balances = [];

        $leaveTypes = LeaveType::where('tenant_id', $user->tenant_id)->get();

        foreach ($leaveTypes as $type) {
            $allowance = LeaveAllowance::where('user_id', $user->id)
                ->where('leave_type_id', $type->id)
                ->where('year', now()->year)
                ->first();

            if (!$allowance) {
                continue;
            }

            // Calculate projected usage based on approved future requests
            $futureUsage = LeaveRequest::where('user_id', $user->id)
                ->where('leave_type_id', $type->id)
                ->where('status', 'approved')
                ->where('start_date', '>', today())
                ->sum('days_requested');

            $balances[] = [
                'type' => $type->name,
                'total' => $allowance->days_allowance,
                'used' => $allowance->days_used,
                'pending' => $allowance->days_pending,
                'future_approved' => $futureUsage,
                'remaining' => $allowance->days_allowance - $allowance->days_used - $futureUsage,
                'expires_at' => $allowance->expires_at?->format('Y-m-d'),
            ];
        }

        return $balances;
    }

    /**
     * Get upcoming shift load
     */
    public function getUpcomingShiftLoad(User $user, int $weeks = 4): array
    {
        $load = [];
        $startDate = now()->startOfWeek();

        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $shifts = Shift::where('user_id', $user->id)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->get();

            $load[] = [
                'week' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'shifts' => $shifts->count(),
                'hours' => round($shifts->sum(fn ($s) => $s->duration_hours), 2),
                'target' => $user->target_hours_per_week,
            ];
        }

        return $load;
    }

    /**
     * Get recommendations for employee
     */
    public function getRecommendations(User $user): array
    {
        $recommendations = [];

        // Check availability vs scheduled
        $recentInsights = $this->getInsights($user, 'month');
        if ($recentInsights->hoursWorked < $user->target_hours_per_week * 4 * 0.8) {
            $recommendations[] = [
                'type' => 'availability',
                'message' => 'Consider updating your availability to get more shifts',
                'action' => 'Update Availability',
                'route' => 'profile.availability',
            ];
        }

        // Check for expiring certifications
        $expiringDocs = EmployeeDocument::where('user_id', $user->id)
            ->where('expires_at', '<=', now()->addMonths(1))
            ->where('expires_at', '>', now())
            ->count();

        if ($expiringDocs > 0) {
            $recommendations[] = [
                'type' => 'certification',
                'message' => "You have {$expiringDocs} certification(s) expiring soon",
                'action' => 'View Documents',
                'route' => 'profile.documents',
            ];
        }

        // Check leave balance
        $leaveProjection = $this->getLeaveBalanceProjection($user);
        $expiringLeave = collect($leaveProjection)->filter(fn ($b) => $b['remaining'] > 5 && $b['expires_at'] && Carbon::parse($b['expires_at'])->lt(now()->addMonths(3)));

        if ($expiringLeave->isNotEmpty()) {
            $recommendations[] = [
                'type' => 'leave',
                'message' => 'You have leave balance expiring soon - use it or lose it!',
                'action' => 'Request Leave',
                'route' => 'leave.create',
            ];
        }

        // Check for open shifts matching preferences
        $openShifts = Shift::whereNull('user_id')
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('business_role_id', $user->businessRoles->pluck('id'))
            ->where('date', '>=', today())
            ->where('date', '<=', now()->addDays(14))
            ->count();

        if ($openShifts > 0) {
            $recommendations[] = [
                'type' => 'shifts',
                'message' => "{$openShifts} open shift(s) matching your roles are available",
                'action' => 'View Open Shifts',
                'route' => 'shifts.marketplace',
            ];
        }

        return $recommendations;
    }

    private function getPeriodDates(string $periodType): array
    {
        return match($periodType) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function getWeeksInPeriod(Carbon $start, Carbon $end): float
    {
        return $start->diffInDays($end) / 7;
    }

    /**
     * Calculate fairness score based on:
     * - Weekend shift distribution (compared to team average)
     * - Hours vs contracted/target hours
     * - Shift type variety (morning/evening/night balance)
     *
     * Score ranges from 0-100, where 100 is perfectly fair
     */
    private function calculateFairnessScore(
        User $user,
        Collection $shifts,
        float $hoursWorked,
        array $shiftTypeBreakdown,
        array $dayOfWeekBreakdown,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        $scores = [];

        // 1. Weekend shift distribution score (0-100)
        // Compare user's weekend ratio to team average
        $weekendScore = $this->calculateWeekendFairnessScore($user, $shifts, $startDate, $endDate);
        $scores[] = $weekendScore;

        // 2. Hours vs target score (0-100)
        // How close are actual hours to contracted/target hours?
        $hoursScore = $this->calculateHoursFairnessScore($user, $hoursWorked, $startDate, $endDate);
        $scores[] = $hoursScore;

        // 3. Shift type variety score (0-100)
        // Measure balance across shift types (morning/afternoon/evening/night)
        $varietyScore = $this->calculateVarietyFairnessScore($shiftTypeBreakdown);
        $scores[] = $varietyScore;

        // Average all component scores
        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Calculate weekend fairness score
     * Compares user's weekend shift ratio to team average
     */
    private function calculateWeekendFairnessScore(
        User $user,
        Collection $shifts,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        // User's weekend shifts
        $userWeekendShifts = $shifts->filter(fn ($s) => $s->date->isWeekend())->count();
        $userTotalShifts = $shifts->count();
        $userWeekendRatio = $userTotalShifts > 0 ? $userWeekendShifts / $userTotalShifts : 0;

        // Get team average weekend ratio (same department/role)
        $teamShifts = Shift::where('tenant_id', $user->tenant_id)
            ->whereIn('business_role_id', $user->businessRoles->pluck('id'))
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->get();

        $teamWeekendShifts = $teamShifts->filter(fn ($s) => $s->date->isWeekend())->count();
        $teamTotalShifts = $teamShifts->count();
        $teamWeekendRatio = $teamTotalShifts > 0 ? $teamWeekendShifts / $teamTotalShifts : 0;

        // If no team data, assume fair
        if ($teamTotalShifts === 0 || $userTotalShifts === 0) {
            return 100.0;
        }

        // Calculate deviation from team average (lower deviation = fairer)
        // Allow 20% variance before penalizing
        $deviation = abs($userWeekendRatio - $teamWeekendRatio);
        $tolerance = 0.20;

        if ($deviation <= $tolerance) {
            return 100.0;
        }

        // Score decreases as deviation increases beyond tolerance
        // Max penalty when deviation is 50% or more
        $penaltyRatio = min(1.0, ($deviation - $tolerance) / 0.30);
        return round(100 - ($penaltyRatio * 50), 2);
    }

    /**
     * Calculate hours fairness score
     * How close are actual hours to target hours?
     */
    private function calculateHoursFairnessScore(
        User $user,
        float $hoursWorked,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        $weeksInPeriod = $this->getWeeksInPeriod($startDate, $endDate);
        $targetHours = $user->target_hours_per_week * $weeksInPeriod;

        // If no target set, assume fair
        if ($targetHours <= 0) {
            return 100.0;
        }

        // Calculate percentage of target achieved
        $percentageOfTarget = $hoursWorked / $targetHours;

        // Perfect score if between 90% and 110% of target
        if ($percentageOfTarget >= 0.90 && $percentageOfTarget <= 1.10) {
            return 100.0;
        }

        // Penalize for being under target (unfair - not getting enough hours)
        if ($percentageOfTarget < 0.90) {
            $underRatio = $percentageOfTarget / 0.90;
            return round($underRatio * 100, 2);
        }

        // Slight penalty for being over target (overtime burden)
        // But less severe than under-scheduling
        $overRatio = min(1.5, $percentageOfTarget);
        $penalty = ($overRatio - 1.10) / 0.40 * 20; // Max 20 point penalty
        return round(100 - $penalty, 2);
    }

    /**
     * Calculate shift type variety score
     * Measures balance across morning/afternoon/evening/night shifts
     */
    private function calculateVarietyFairnessScore(array $shiftTypeBreakdown): float
    {
        $types = ['morning', 'afternoon', 'evening', 'night'];
        $counts = [];

        foreach ($types as $type) {
            $counts[$type] = $shiftTypeBreakdown[$type] ?? 0;
        }

        $totalShifts = array_sum($counts);

        // If few shifts, variety is less meaningful
        if ($totalShifts < 4) {
            return 100.0;
        }

        // Calculate ideal distribution (equal across all types)
        $idealPerType = $totalShifts / 4;

        // Calculate variance from ideal
        $totalVariance = 0;
        foreach ($counts as $count) {
            $totalVariance += abs($count - $idealPerType);
        }

        // Normalize variance (max variance = totalShifts when all shifts are one type)
        $maxVariance = $totalShifts * 0.75; // 75% of shifts in one type is max expected
        $normalizedVariance = min(1.0, $totalVariance / $maxVariance);

        // Higher variance = lower score
        // Some variance is expected and acceptable
        $acceptableVariance = 0.30;

        if ($normalizedVariance <= $acceptableVariance) {
            return 100.0;
        }

        $penaltyRatio = ($normalizedVariance - $acceptableVariance) / 0.70;
        return round(100 - ($penaltyRatio * 40), 2); // Max 40 point penalty
    }
}
```

### Employee Insights DTO

```php
class EmployeeInsights
{
    public function __construct(
        public float $hoursScheduled,
        public float $hoursWorked,
        public float $overtimeHours,
        public int $shiftsScheduled,
        public int $shiftsWorked,
        public int $lateArrivals,
        public int $earlyDepartures,
        public int $noShows,
        public float $punctualityRate,
        public float $attendanceRate,
        public ?float $fairnessScore,
        public array $shiftTypeBreakdown,
        public array $dayOfWeekBreakdown,
        public string $periodStart,
        public string $periodEnd,
    ) {}

    public static function fromCache(EmployeeInsightsCache $cache): self
    {
        return new self(
            hoursScheduled: $cache->hours_scheduled,
            hoursWorked: $cache->hours_worked,
            overtimeHours: $cache->overtime_hours,
            shiftsScheduled: $cache->shifts_scheduled,
            shiftsWorked: $cache->shifts_worked,
            lateArrivals: $cache->late_arrivals,
            earlyDepartures: $cache->early_departures,
            noShows: $cache->no_shows,
            punctualityRate: $cache->punctuality_rate,
            attendanceRate: $cache->attendance_rate,
            fairnessScore: $cache->fairness_score,
            shiftTypeBreakdown: $cache->shift_type_breakdown ?? [],
            dayOfWeekBreakdown: $cache->day_of_week_breakdown ?? [],
            periodStart: $cache->period_start->format('Y-m-d'),
            periodEnd: $cache->period_end->format('Y-m-d'),
        );
    }
}
```

### Employee Insights API

```php
Route::prefix('insights')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/summary', [InsightsController::class, 'summary']);
        Route::get('/hours-trend', [InsightsController::class, 'hoursTrend']);
        Route::get('/leave-balance', [InsightsController::class, 'leaveBalance']);
        Route::get('/upcoming-load', [InsightsController::class, 'upcomingLoad']);
        Route::get('/recommendations', [InsightsController::class, 'recommendations']);
    });
```

---

## 3. Authentication Flow

### 3.1 Registration Flow

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Registration   │────▶│ RegisterController│────▶│    Validate     │
│     Form        │     │                  │     │    Request      │
└─────────────────┘     └──────────────────┘     └────────┬────────┘
                                                          │
                                                          ▼
                        ┌──────────────────┐     ┌─────────────────┐
                        │   Assign Admin   │◀────│  Create Tenant  │
                        │      Role        │     │   + User        │
                        └────────┬─────────┘     └─────────────────┘
                                 │
                                 ▼
                        ┌──────────────────┐     ┌─────────────────┐
                        │  Create Default  │────▶│    Login User   │
                        │   Leave Types    │     │   & Redirect    │
                        └──────────────────┘     └─────────────────┘
```

**Implementation Details:**

```php
// RegisterController::store()
public function store(RegisterRequest $request): RedirectResponse
{
    // 1. Create tenant
    $tenant = Tenant::create([
        'name' => $request->company_name,
        'slug' => Str::slug($request->company_name),
        'email' => $request->email,
    ]);

    // 2. Create user
    $user = User::create([
        'tenant_id' => $tenant->id,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // 3. Assign Admin role
    UserRoleAssignment::create([
        'user_id' => $user->id,
        'system_role' => SystemRole::Admin->value,
    ]);

    // 4. Create default leave types
    $this->createDefaultLeaveTypes($tenant);

    // 5. Login and redirect
    Auth::login($user);
    return redirect()->route('dashboard');
}
```

### 3.2 Login Flow

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   Login Form    │────▶│ LoginController  │────▶│   Validate      │
│                 │     │                  │     │  Credentials    │
└─────────────────┘     └──────────────────┘     └────────┬────────┘
                                                          │
                                                          ▼
                                                 ┌─────────────────┐
                                          NO ◀───│  Credentials    │
                                          ┌─────▶│    Valid?       │
                                          │      └────────┬────────┘
                                          │               │ YES
                                          ▼               ▼
                                 ┌─────────────┐ ┌─────────────────┐
                                 │ Return with │ │  Update Login   │
                                 │   Errors    │ │   Timestamp     │
                                 └─────────────┘ └────────┬────────┘
                                                          │
                                                          ▼
                                                 ┌─────────────────┐
                                                 │   Redirect to   │
                                                 │   Dashboard     │
                                                 └─────────────────┘
```

### 3.3 Session Management

- Session driver: `file` (configurable via `SESSION_DRIVER`)
- Session lifetime: 120 minutes (configurable via `SESSION_LIFETIME`)
- Remember me: 5 years token validity
- CSRF protection on all POST/PUT/DELETE requests

---

## 4. Authorization Matrix Implementation

### 4.1 System Roles Enum

```php
enum SystemRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case LocationAdmin = 'location_admin';
    case DepartmentAdmin = 'department_admin';
    case Employee = 'employee';

    public function label(): string;
    public function isAdminLevel(): bool;
    public function canManageLocations(): bool;
    public function canManageDepartments(): bool;
    public function canManageBusinessRoles(): bool;
    public function canManageUsers(): bool;
    public function canApproveLeave(): bool;
    public static function forTenantAdmins(): array;
}
```

### 4.2 Additional Enums

#### ShiftStatus

```php
enum ShiftStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Missed = 'missed';
    case Cancelled = 'cancelled';

    public function label(): string;
    public function color(): string;
    public function isActive(): bool;      // Returns true for Published, InProgress
    public function isFinal(): bool;       // Returns true for Completed, Missed, Cancelled
    public function isDraft(): bool;
    public function isPublished(): bool;
    public function isVisibleToEmployee(): bool;  // Returns true for non-draft statuses
}
```

**Status Workflow:**
```
Draft → Published → InProgress → Completed
                  └→ Missed
                  └→ Cancelled
```

- **Draft**: Default status for new shifts. Hidden from employees.
- **Published**: Visible to employees. Can be published individually or in bulk.
- **InProgress**: Clock-in recorded.
- **Completed**: Clock-out recorded.
- **Missed**: No clock-in after grace period (configurable via TenantSettings).
- **Cancelled**: Shift cancelled by admin.

#### LeaveRequestStatus

```php
enum LeaveRequestStatus: string
{
    case Draft = 'draft';
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string;
    public function color(): string;
}
```

#### SwapRequestStatus

```php
enum SwapRequestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string;
    public function color(): string;
    public function isPending(): bool;
    public function isFinal(): bool;       // Returns true for Accepted, Rejected, Cancelled
    public function canBeCancelled(): bool; // Returns true for Pending
}
```

**Note:** SwapRequestStatus uses `Accepted` (not `Approved`) for the accepted state. This distinguishes between employee acceptance and optional manager approval workflows.

#### TimeEntryStatus

```php
enum TimeEntryStatus: string
{
    case ClockedIn = 'clocked_in';
    case OnBreak = 'on_break';
    case ClockedOut = 'clocked_out';

    public function label(): string;
    public function color(): string;
}
```

### 4.3 Role Hierarchy

```
Level 0: SuperAdmin (Plannrly Staff Only)
    │
Level 1: Admin (Tenant Administrator)
    │
Level 2: LocationAdmin (Location-scoped)
    │
Level 3: DepartmentAdmin (Department-scoped)
    │
Level 4: Employee (Self-service only)
```

### 4.4 Policy Implementation

#### LocationPolicy

```php
class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        // SuperAdmin, Admin, or LocationAdmin can view locations
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin();
    }

    public function view(User $user, Location $location): bool
    {
        // Must belong to same tenant
        if ($user->tenant_id !== $location->tenant_id) {
            return false;
        }

        // SuperAdmin/Admin can view any, LocationAdmin only assigned
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin($location->id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, Location $location): bool
    {
        if ($user->tenant_id !== $location->tenant_id) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin($location->id);
    }

    public function delete(User $user, Location $location): bool
    {
        if ($user->tenant_id !== $location->tenant_id) {
            return false;
        }

        return $user->isSuperAdmin() || $user->isAdmin();
    }
}
```

#### LeaveRequestPolicy

```php
class LeaveRequestPolicy
{
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // Own request
        if ($leaveRequest->user_id === $user->id) {
            return true;
        }

        // Check if can approve (has authority over user)
        return $this->review($user, $leaveRequest);
    }

    public function create(User $user): bool
    {
        // All authenticated users can create leave requests
        return true;
    }

    public function review(User $user, LeaveRequest $leaveRequest): bool
    {
        // Cannot review own request
        if ($leaveRequest->user_id === $user->id) {
            return false;
        }

        // SuperAdmin/Admin can review all
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // LocationAdmin can review users in their locations
        // DepartmentAdmin can review users in their departments
        // (Implementation checks role assignments)

        return $user->canApproveLeaveFor($leaveRequest->user);
    }
}
```

### 4.5 Middleware Implementation

#### CheckSystemRole Middleware

```php
class CheckSystemRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRoles = $request->user()->roleAssignments
            ->pluck('system_role')
            ->toArray();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
```

**Usage:**

```php
// In routes/web.php
Route::middleware(['auth', 'role:super_admin,admin'])
    ->group(function () {
        Route::resource('locations', LocationController::class);
    });
```

#### EnsureSuperAdmin Middleware

```php
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isSuperAdmin()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        return $next($request);
    }
}
```

#### EnsureTenantAccess Middleware

```php
class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // SuperAdmin can access without tenant
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Regular users must have a tenant
        if (!$request->user()->tenant_id) {
            abort(403, 'No tenant associated with this account.');
        }

        return $next($request);
    }
}
```

#### SetTenantContext Middleware

```php
class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->tenant_id) {
            // Sets tenant context for the request
            app()->instance('current_tenant_id', $request->user()->tenant_id);
        }

        return $next($request);
    }
}
```

**Middleware Registration (bootstrap/app.php):**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => CheckSystemRole::class,
        'super_admin' => EnsureSuperAdmin::class,
        'tenant' => EnsureTenantAccess::class,
    ]);
})
```

---

## 5. Tenant Isolation Implementation

### 5.1 Global Scope

```php
// app/Scopes/TenantScope.php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->tenant_id) {
            $builder->where(
                $model->getTable() . '.tenant_id',
                Auth::user()->tenant_id
            );
        }
    }
}
```

### 5.2 BelongsToTenant Trait

```php
// app/Traits/BelongsToTenant.php
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Apply global scope for reading
        static::addGlobalScope(new TenantScope);

        // Auto-assign tenant_id on creation
        static::creating(function (Model $model) {
            if (Auth::check() && !$model->tenant_id) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

### 5.3 Models Using Tenant Isolation

All tenant-scoped models use the `BelongsToTenant` trait:

- Location
- Department
- BusinessRole
- Shift
- TimeEntry
- LeaveType
- LeaveAllowance
- LeaveRequest
- ShiftSwapRequest

### 5.4 Bypassing Tenant Scope

For administrative purposes (e.g., SuperAdmin viewing all data):

```php
// Query without tenant scope
Location::withoutGlobalScope(TenantScope::class)->get();

// Or for a specific query
Location::query()
    ->withoutGlobalScope(TenantScope::class)
    ->where('id', $locationId)
    ->first();
```

---

## 6. API Endpoint Documentation

### 6.1 Web Routes (Phase 1 Implementation)

#### Authentication Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /login | LoginController@showLoginForm | Display login form |
| POST | /login | LoginController@login | Process login |
| POST | /logout | LoginController@logout | Process logout |
| GET | /register | RegisterController@showRegistrationForm | Display registration |
| POST | /register | RegisterController@store | Process registration |

#### Dashboard Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /dashboard | DashboardController@index | Role-based dashboard |

#### Location Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /locations | LocationController@index | List locations |
| GET | /locations/create | LocationController@create | Create form |
| POST | /locations | LocationController@store | Store location |
| GET | /locations/{location} | LocationController@show | View location |
| GET | /locations/{location}/edit | LocationController@edit | Edit form |
| PUT | /locations/{location} | LocationController@update | Update location |
| DELETE | /locations/{location} | LocationController@destroy | Delete location |

#### Department Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /departments | DepartmentController@index | List departments |
| GET | /departments/create | DepartmentController@create | Create form |
| POST | /departments | DepartmentController@store | Store department |
| GET | /departments/{department}/edit | DepartmentController@edit | Edit form |
| PUT | /departments/{department} | DepartmentController@update | Update department |
| DELETE | /departments/{department} | DepartmentController@destroy | Delete department |

#### Business Role Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /business-roles | BusinessRoleController@index | List roles |
| GET | /business-roles/create | BusinessRoleController@create | Create form |
| POST | /business-roles | BusinessRoleController@store | Store role |
| GET | /business-roles/{businessRole}/edit | BusinessRoleController@edit | Edit form |
| PUT | /business-roles/{businessRole} | BusinessRoleController@update | Update role |
| DELETE | /business-roles/{businessRole} | BusinessRoleController@destroy | Delete role |

#### User Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /users | UserController@index | List users |
| GET | /users/create | UserController@create | Create form |
| POST | /users | UserController@store | Store user |
| GET | /users/{user} | UserController@show | View user |
| GET | /users/{user}/edit | UserController@edit | Edit form |
| PUT | /users/{user} | UserController@update | Update user |
| DELETE | /users/{user} | UserController@destroy | Delete user |

#### Schedule Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /schedule | ScheduleController@index | View weekly schedule (default: current week) |
| GET | /schedule?start=YYYY-MM-DD | ScheduleController@index | View schedule for specific week |
| GET | /schedule?group_by=role | ScheduleController@index | View schedule grouped by role |
| GET | /schedule/day | ScheduleController@day | View daily schedule (default: today) |
| GET | /schedule/day?date=YYYY-MM-DD | ScheduleController@day | View schedule for specific day |
| GET | /schedule/day?group_by=role | ScheduleController@day | View day schedule grouped by role |
| GET | /schedule/draft-count | ScheduleController@draftCount | Get count of draft shifts (JSON) |
| POST | /schedule/publish | ScheduleController@publishAll | Publish all draft shifts in date range |

**Group By Parameter:**
- `group_by=department` (default) - Groups employees under department headers
- `group_by=role` - Groups employees under business role headers

**Draft Count Response (JSON):**
```json
{ "count": 5 }
```

**Publish All Request:**
```json
{
    "start_date": "2024-01-15",
    "end_date": "2024-01-21",
    "location_id": 1,        // optional filter
    "department_id": 2,      // optional filter
    "business_role_id": 3    // optional filter
}
```

**Publish All Response (JSON):**
```json
{
    "success": true,
    "published_count": 5,
    "message": "5 shift(s) published successfully."
}
```

#### Shift Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /shifts | ShiftController@index | List shifts |
| POST | /shifts | ShiftController@store | Store shift |
| GET | /shifts/{shift} | ShiftController@show | Get shift JSON (for modal) |
| GET | /shifts/{shift}/edit | ShiftController@edit | Edit form |
| PUT | /shifts/{shift} | ShiftController@update | Update shift |
| DELETE | /shifts/{shift} | ShiftController@destroy | Delete shift |
| POST | /shifts/{shift}/assign | ShiftController@assign | Assign user |
| POST | /shifts/{shift}/publish | ShiftController@publish | Publish single shift |
| GET | /shifts/{shift}/available-users | ShiftController@availableUsers | Get assignable users |

#### Leave Request Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /leave-requests | LeaveRequestController@index | List requests |
| GET | /leave-requests/create | LeaveRequestController@create | Create form |
| POST | /leave-requests | LeaveRequestController@store | Store request |
| GET | /leave-requests/{leaveRequest} | LeaveRequestController@show | View request |
| POST | /leave-requests/{leaveRequest}/review | LeaveRequestController@review | Approve/Reject |

### 6.1.1 Shift Validation

**Shift Overlap Validation:**

Both `StoreShiftRequest` and `UpdateShiftRequest` include server-side validation to prevent overlapping shifts for the same employee.

```php
// Validation rule in user_id field
function ($attribute, $value, $fail) {
    // Check for overlapping shifts
    if ($this->hasOverlappingShift($value, $date, $startTime, $endTime, $excludeShiftId)) {
        $fail('This shift overlaps with another shift for this employee.');
    }
}
```

**Overlap Detection Algorithm:**
1. Query existing shifts for the same user on the same date
2. Convert times to minutes since midnight for comparison
3. Handle overnight shifts (end time < start time = crosses midnight)
4. Two shifts overlap if: `start1 < end2 AND start2 < end1`

**Helper Methods in Request Classes:**
- `hasOverlappingShift(userId, date, startTime, endTime, excludeShiftId)` - Checks for conflicts
- `shiftsOverlap(start1, end1, start2, end2)` - Compares two time ranges
- `timeToMinutes(time)` - Converts HH:MM to minutes since midnight

**Overnight Shift Handling:**
```php
// If end time is before start time, add 24 hours (1440 minutes)
if ($endMin <= $startMin) {
    $endMin += 1440;
}
```

#### User Filter Preferences Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /user/filter-defaults | UserFilterController@getDefault | Get user's saved filter defaults |
| POST | /user/filter-defaults | UserFilterController@storeDefault | Save user's filter defaults |

**Request Parameters (POST):**
```json
{
  "filter_context": "schedule",
  "location_id": 1,
  "department_id": 2,
  "business_role_id": 3,
  "group_by": "department"
}
```

**Filter Contexts:**
- `schedule` - Week view filter defaults
- `schedule_day` - Day view filter defaults

**Response (GET):**
```json
{
  "location_id": 1,
  "department_id": 2,
  "business_role_id": 3,
  "group_by": "department"
}
```

**Note:** The `group_by` value is stored in the `additional_filters` JSON column and retrieved via the `getFilter()` helper method on the model.

#### Time Entry Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /time-entries | TimeEntryController@index | List time entries (admin: all, employee: own) |
| GET | /time-entries/{timeEntry} | TimeEntryController@show | View time entry details |
| POST | /time-entries/clock-in | TimeEntryController@clockIn | Employee clocks in |
| POST | /time-entries/clock-out | TimeEntryController@clockOut | Employee clocks out |
| POST | /time-entries/start-break | TimeEntryController@startBreak | Start break |
| POST | /time-entries/end-break | TimeEntryController@endBreak | End break |
| PUT | /time-entries/{timeEntry} | TimeEntryController@update | Manager adjusts time entry |
| POST | /time-entries/{timeEntry}/approve | TimeEntryController@approve | Manager approves entry |

**AJAX Endpoints (Clock Widget):**
| Method | URI | Response | Description |
|--------|-----|----------|-------------|
| GET | /time-entries/current-status | JSON | Get employee's current clock status |
| POST | /time-entries/clock-in | JSON | AJAX clock in with GPS |
| POST | /time-entries/clock-out | JSON | AJAX clock out with GPS |
| POST | /time-entries/start-break | JSON | AJAX start break |
| POST | /time-entries/end-break | JSON | AJAX end break |

#### Timesheet Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /timesheets | TimesheetController@index | Admin weekly timesheet view |
| GET | /timesheets?week_start=YYYY-MM-DD | TimesheetController@index | View specific week |
| GET | /timesheets/employee | TimesheetController@employee | Employee's own timesheet |
| POST | /timesheets/approve | TimesheetController@batchApprove | Batch approve time entries |

**Timesheet Query Parameters:**
- `week_start` - Start date of week to view (defaults to current week)
- `location_id` - Filter by location
- `department_id` - Filter by department
- `user_id` - Filter by specific user

#### Attendance Report Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /reports/attendance | AttendanceReportController@index | Summary dashboard |
| GET | /reports/attendance/punctuality | AttendanceReportController@punctuality | Punctuality report |
| GET | /reports/attendance/hours | AttendanceReportController@hours | Hours worked report |
| GET | /reports/attendance/overtime | AttendanceReportController@overtime | Overtime report |
| GET | /reports/attendance/absence | AttendanceReportController@absence | Absence/no-show report |
| GET | /reports/attendance/employee/{user} | AttendanceReportController@employee | Individual employee report |
| GET | /reports/attendance/department/{department} | AttendanceReportController@department | Department report |
| GET | /reports/attendance/export/{type} | AttendanceReportController@export | Export to CSV |

**Report Query Parameters:**
- `start_date` - Start of date range (defaults to first of current month)
- `end_date` - End of date range (defaults to today)
- `department_id` - Filter by department
- `location_id` - Filter by location

**Export Types:**
- `punctuality` - Punctuality report CSV
- `hours` - Hours worked report CSV
- `overtime` - Overtime report CSV
- `absence` - Absence report CSV

### 6.2 API Routes (v1 - Placeholder)

```php
// routes/api.php
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Future API implementation
});
```

---

## 6.3 Schedule Page UI Patterns

### Schedule Navigation

The schedule system uses URL query parameters for navigation:

**Week View:**
```
/schedule                      → Current week (Monday to Sunday)
/schedule?start=2024-01-15     → Week starting from specified Monday
```

**Day View:**
```
/schedule/day                  → Today
/schedule/day?date=2024-01-15  → Specific date
```

**Week View Navigation:**
- **Previous Arrow**: Subtracts 7 days from current start date
- **Next Arrow**: Adds 7 days to current start date
- **Today Button**: Resets to current week

**Day View Navigation:**
- **Previous Arrow**: Subtracts 1 day from current date
- **Next Arrow**: Adds 1 day to current date
- **Today Button**: Resets to today

**View Toggle:**
- Day/Week toggle buttons in the header
- Switching views preserves date context (day within week)

### Cascading Filter Behavior

The schedule filters implement a hierarchical dependency pattern:

```
Location Filter (always enabled)
    │
    └─► Department Filter (disabled until location selected)
            │
            └─► Role Filter (disabled until department selected)
```

**Disabled State Styling:**
```css
.filter-select:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background-color: #1f2937;
}
```

**Placeholder Text States:**
| Filter | When Enabled | When Disabled |
|--------|--------------|---------------|
| Location | "Select Location" | N/A (always enabled) |
| Department | "Select Department" | "Select Location First" |
| Role | "Select Role" | "Select Department First" |

**JavaScript Filter Logic:**

1. On Location change:
   - Filter departments to those belonging to selected location
   - Enable department dropdown
   - Reset department and role selections
   - Disable role dropdown

2. On Department change:
   - Filter roles to those belonging to selected department
   - Enable role dropdown
   - Reset role selection

3. On any filter change:
   - Filter visible employee rows based on selections
   - Update shift visibility based on employee filter

### Filter Defaults Persistence

The "Make Default" button saves current filter state via AJAX:

```javascript
POST /user/filter-defaults
{
    filter_context: 'schedule',
    location_id: selectedLocationId || null,
    department_id: selectedDepartmentId || null,
    business_role_id: selectedRoleId || null
}
```

On page load, defaults are fetched and applied:

```javascript
GET /user/filter-defaults?filter_context=schedule
```

### Shift Edit Modal

The schedule view includes an Alpine.js-powered modal for editing shifts in place.

**Modal State Management:**

```javascript
editModal: {
    isOpen: false,           // Modal visibility
    isCreateMode: false,     // True = creating new shift, False = editing existing
    loading: false,          // Fetching shift data
    saving: false,           // Save in progress
    deleting: false,         // Delete in progress
    confirmDelete: false,    // Delete confirmation step
    error: null,             // Error message
    errors: {},              // Validation errors by field
    shiftId: null,           // Current shift ID (null in create mode)
    originalUserId: null,    // Original user for detecting changes
    originalDate: null,      // Original date for detecting changes
    shift: {                 // Form data (cascading filter order)
        location_id, department_id,
        business_role_id, user_id,
        date, start_time, end_time,
        break_duration_minutes, notes, status
    }
}
```

**Modal Workflow:**

1. Click shift block → `editModal.open(shiftId)` called
2. Fetch shift data from `GET /shifts/{id}` (JSON response)
3. Populate form fields (location, department, role, employee cascade)
4. On location change → filter departments → auto-select first
5. On department change → filter roles → auto-select first
6. On role change → filter employees → validate current selection
7. On Save → `PUT /shifts/{id}` with form data
8. On success → Update DOM in place, close modal
9. On validation error → Display field errors
10. On Delete → Confirm first, then `DELETE /shifts/{id}`

**Cascading Filter Data:**

```javascript
const modalData = {
    locations: [{ id, name }],
    departments: [{ id, name, location_id }],
    roles: [{ id, name, department_id }],
    users: [{ id, name, role_ids: [] }]
};

// Filter functions:
getAvailableDepartments()  // Filtered by location_id
getAvailableRoles()        // Filtered by department_id
getAvailableEmployees()    // Filtered by users who have the role

// Cascade handlers:
onLocationChange()   // Updates department, role, employee
onDepartmentChange() // Updates role, employee
onRoleChange()       // Validates employee selection
```

**Create Mode:**

```javascript
// Click empty cell to create new shift
editModal.create(userId, date, locationId, departmentId)

// State differences in create mode:
editModal.isCreateMode = true
editModal.shiftId = null

// Save uses POST /shifts instead of PUT /shifts/{id}
// After success, addShiftToDom() creates the shift block
```

**DOM Update Functions:**

- `updateShiftInDom(shift)` - Updates times, role name, and block color in shift block
- `moveShiftInDom(shift, originalUserId, originalDate)` - Moves shift block to new cell when user or date changes
- `removeShiftFromDom(shiftId)` - Removes shift block, adds empty placeholder
- `addShiftToDom(shift)` - Creates new shift block in cell, removes placeholder, attaches event listeners

**Shift Block Display:**

Each shift block in the schedule grid shows:
- Times: `HH:MM - HH:MM` format
- Role: Business role name (truncated with CSS `truncate` class)
- Color: Block background uses `business_role.color` (falls back to user's primary role color)

### Drag-and-Drop Implementation

Native HTML5 Drag and Drop API for moving shifts between cells.

**Shift Block Attributes:**

```html
<div class="shift-block"
     data-shift-id="{{ $shift->id }}"
     data-user-id="{{ $user->id }}"
     data-date="{{ $dateStr }}"
     draggable="true"
     @dragstart="handleDragStart($event, {{ $shift->id }})"
     @dragend="handleDragEnd($event)"
     @click.stop="editModal.open({{ $shift->id }})">
```

**Schedule Cell (Drop Target) Attributes:**

```html
<div class="schedule-cell"
     data-user-id="{{ $user->id }}"
     data-date="{{ $dateStr }}"
     @dragover.prevent
     @dragenter="handleDragEnter($event)"
     @dragleave="handleDragLeave($event)"
     @drop="handleDrop($event, {{ $user->id }}, '{{ $dateStr }}')">
```

**Handler Functions:**

| Handler | Purpose |
|---------|---------|
| `handleDragStart` | Store shift ID, add `.dragging` class |
| `handleDragEnd` | Remove `.dragging` class, clear state |
| `handleDragEnter` | Add `.drag-over` class to target cell |
| `handleDragLeave` | Remove `.drag-over` class when leaving |
| `handleDrop` | Validate drop, call API, update DOM |

**Drop Validation:**

- Cannot drop on same cell (no change)
- Cannot drop on cell with existing shift
- API call: `PUT /shifts/{id}` with new `user_id` and `date`

**CSS Classes:**

```css
.drag-over {
    background-color: rgba(90, 48, 240, 0.2) !important;
    outline: 2px dashed #5a30f0;
    outline-offset: -2px;
}

.dragging {
    opacity: 0.5;
}
```

**DOM Manipulation on Successful Drop:**

1. Remove placeholder from target cell
2. Update shift element's `data-user-id` and `data-date`
3. Move shift element to target cell
4. Add placeholder to original cell

### Publish Workflow

The schedule includes a bulk publish feature for draft shifts.

**Publish Button:**
- Shows count of draft shifts in current view: "Publish All (X)"
- Only visible to admins/managers (not employees)
- Respects active filters (location, department, role)

**Publish Process:**
1. Click "Publish All (X)" button
2. Confirmation modal shows count
3. POST to `/schedule/publish` with date range and filters
4. Backend updates all matching draft shifts to `published` status
5. If `notify_on_publish` is enabled in TenantSettings, sends ShiftPublishedNotification to assigned employees
6. Returns JSON with count of published shifts
7. Frontend updates draft count display

**Individual Publish:**
- Shifts can also be published individually via the edit modal
- POST to `/shifts/{id}/publish`

**ShiftPublishedNotification:**
- Sent to the assigned employee when their shift is published
- Includes shift date, times, location, and role
- Delivered via configured notification channels (email, database)

### Unassigned Shifts Row

The schedule view includes a dedicated row at the top for shifts that have no user assigned (`user_id = NULL`). This allows managers to:
- See all unassigned shifts at a glance
- Create unassigned shifts that need to be filled
- Drag shifts from the unassigned row to assign them to employees
- Drag assigned shifts to the unassigned row to unassign them

**Controller Data:**

```php
// ScheduleController creates unassigned shifts lookup grouped by date
$unassignedShiftsLookup = [];
foreach ($shifts as $shift) {
    if (! $shift->user_id) {
        $dateStr = $shift->date->format('Y-m-d');
        $unassignedShiftsLookup[$dateStr][] = $shift;
    }
}
```

**Row Display:**

```html
<div class="unassigned-row" data-user-id="">
    <!-- Unassigned label with count -->
    <div class="p-3">
        <div class="text-amber-400">Unassigned</div>
        <div class="text-amber-500/70">{{ $unassignedShifts }} shifts</div>
    </div>

    <!-- Day cells - can contain multiple shifts per cell -->
    @foreach($weekDates as $date)
        <div class="schedule-cell" data-user-id="" data-date="{{ $dateStr }}">
            @foreach($unassignedShiftsLookup[$dateStr] ?? [] as $shift)
                <!-- Shift block with amber border -->
            @endforeach
        </div>
    @endforeach
</div>
```

**Key Differences from Employee Rows:**

| Feature | Employee Row | Unassigned Row |
|---------|--------------|----------------|
| `data-user-id` | Employee ID | Empty string |
| Shifts per cell | Maximum 1 | Multiple allowed |
| Container class | Direct in cell | `.space-y-1` wrapper |
| Placeholder style | `border-gray-700` | `border-amber-700/50` |
| Shift border | None | `border-amber-500/30` |

**Drag-and-Drop Behavior:**

- **Drop TO unassigned row**: Sets `user_id: null`, adds amber border to shift
- **Drop FROM unassigned row**: Assigns user, removes amber border
- **Multiple shifts**: Unassigned row can accept drops even if cell has existing shifts
- **Count update**: `updateUnassignedCount()` called after any drag operation

**Create Shift in Unassigned Row:**

```javascript
// Called when clicking empty cell in unassigned row
editModal.create(null, date, locationId, departmentId)

// userId = null triggers different role selection logic:
// - For assigned: Find user's role in department
// - For unassigned: Select first role in department
```

---

## 7. Event/Listener Architecture

### 7.1 Planned Events (Future Implementation)

| Event | Description | Listeners |
|-------|-------------|-----------|
| ShiftAssigned | User assigned to shift | SendShiftNotification |
| ShiftUpdated | Shift details changed | SendShiftUpdateNotification |
| LeaveRequested | New leave request | NotifyApprovers |
| LeaveApproved | Leave request approved | NotifyEmployee, UpdateCalendar |
| LeaveRejected | Leave request rejected | NotifyEmployee |
| ShiftSwapRequested | Swap request created | NotifyTargetEmployee |
| ShiftSwapAccepted | Target accepts swap | NotifyRequester, NotifyAdmin |
| ClockInMissed | No clock-in for shift | NotifyEmployee, NotifyManager |

### 7.2 Event Implementation Pattern

```php
// app/Events/LeaveRequestStatusChanged.php
class LeaveRequestStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public LeaveRequestStatus $previousStatus
    ) {}
}

// app/Listeners/SendLeaveStatusNotification.php
class SendLeaveStatusNotification
{
    public function handle(LeaveRequestStatusChanged $event): void
    {
        $event->leaveRequest->user->notify(
            new LeaveRequestStatusNotification($event->leaveRequest)
        );
    }
}
```

---

## 8. Testing Coverage

### 8.1 Unit Tests

| Test Class | Coverage |
|------------|----------|
| SystemRoleTest | Enum methods, permissions |
| UserTest | Attributes, relationships, role methods |

### 8.2 Feature Tests

| Test Class | Test Cases |
|------------|------------|
| RegistrationTest | Valid registration, validation errors |
| LoginTest | Valid login, invalid credentials, logout |
| TenantIsolationTest | Data visibility, cross-tenant access prevention |
| LocationManagementTest | CRUD operations, authorization |
| LeaveRequestTest | Create, submit, approve, reject workflows |

### 8.3 Running Tests

```bash
# Run all tests
php artisan test --compact

# Run specific test file
php artisan test tests/Feature/LeaveRequestTest.php

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

### 8.4 Test Factories

All models have corresponding factories with useful states:

```php
// UserFactory
User::factory()->forTenant($tenant)->create();

// LeaveRequestFactory
LeaveRequest::factory()
    ->forUser($user)
    ->requested()  // Status: Requested
    ->create();

// ShiftFactory
Shift::factory()
    ->forUser($user)
    ->onDate(now())
    ->create();
```

---

## 9. Deployment Checklist

### 9.1 Environment Setup

- [ ] Configure `.env` file with production values
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY` with `php artisan key:generate`
- [ ] Configure database connection (MySQL/PostgreSQL)
- [ ] Configure mail settings
- [ ] Configure queue driver (Redis recommended)
- [ ] Configure session driver (Redis/database recommended)

### 9.2 Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=TenantSeeder
php artisan db:seed --class=LeaveTypeSeeder
```

### 9.3 Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 9.4 Frontend Build

```bash
# Install dependencies
npm ci

# Build production assets
npm run build
```

### 9.5 Queue Workers

```bash
# Start queue worker (use Supervisor in production)
php artisan queue:work --queue=default,notifications
```

### 9.6 Scheduled Tasks

Add to crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 9.7 Security Checklist

- [ ] HTTPS configured and enforced
- [ ] Database credentials secured
- [ ] `.env` file not publicly accessible
- [ ] Debug mode disabled
- [ ] CORS configured appropriately
- [ ] Rate limiting enabled on auth routes
- [ ] CSRF protection active
- [ ] XSS protection via Blade escaping
- [ ] SQL injection prevention via Eloquent

### 9.8 Monitoring

- [ ] Error logging configured (Laravel Log, Sentry, etc.)
- [ ] Application performance monitoring (APM)
- [ ] Database query monitoring
- [ ] Queue job monitoring
- [ ] Uptime monitoring

---

## 10. Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2024-01-15 | Claude | Initial Phase 1 implementation |
| 1.1.0 | 2025-01-25 | Claude | Added Day view, Draft/Publish workflow, TenantSettings, Notifications |
| 1.2.0 | 2025-01-25 | Claude | Removed rotas table and consolidated migrations for fresh install |
| 1.3.0 | 2025-01-25 | Claude | Added Group By feature, shift overlap validation, bookmark for day view |
| 1.4.0 | 2025-01-25 | Claude | Mobile Implementation Phase 1: Layout Foundation, Controllers, Views |
| 1.5.0 | 2025-01-25 | Claude | Mobile Implementation Phase 2: Enhanced Employee Dashboard |
| 1.6.0 | 2025-01-25 | Claude | Mobile Implementation Phases 3-8: Complete mobile functionality |
| 1.6.1 | 2025-01-25 | Claude | Bug fixes: Fixed User-Department relationships, corrected SwapRequestStatus enum usage |
| 2.0.0 | 2025-01-27 | Claude | Major refactor: Removed mobile-specific views, added day view drag-and-drop/resize, published shift handling, SuperAdmin features |

---

---

## 11. Day View Drag-and-Drop and Resize

### 11.1 Overview

The day view (`/schedule/day`) provides interactive shift management with mouse-based drag-and-drop for moving shifts and resize handles for adjusting shift times. Unlike the week view which uses HTML5 Drag and Drop API, the day view uses mouse events for precise positioning.

### 11.2 Day View Timeline Structure

**Timeline Layout:**
```html
<div class="timeline-container relative" style="height: 48px;">
    <!-- Timeline grid with hour markers -->
    <div class="timeline-grid absolute inset-0">
        @for ($hour = $dayStartHour; $hour <= $dayEndHour; $hour++)
            <div class="hour-marker" style="left: {{ (($hour - $dayStartHour) / $numHours) * 100 }}%"></div>
        @endfor
    </div>

    <!-- Shift bars positioned absolutely -->
    <div class="shift-bar absolute" style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%;">
        <div class="resize-handle-left"></div>
        <span class="shift-times">{{ $startTime }} - {{ $endTime }}</span>
        <div class="resize-handle-right"></div>
    </div>
</div>
```

**Position Calculation:**
```javascript
// Calculate percentage position from times
const dayStartHour = {{ $dayStartHour }};  // From TenantSettings (default: 6)
const dayEndHour = {{ $dayEndHour }};      // From TenantSettings (default: 22)
const numHours = dayEndHour - dayStartHour;

// Convert time to percentage
function timeToPercent(timeStr) {
    const [hours, minutes] = timeStr.split(':').map(Number);
    const totalMinutes = (hours - dayStartHour) * 60 + minutes;
    return (totalMinutes / (numHours * 60)) * 100;
}

const leftPercent = timeToPercent(startTime);
const rightPercent = timeToPercent(endTime);
const widthPercent = rightPercent - leftPercent;
```

### 11.3 Mouse-Based Drag Implementation

**State Management (Alpine.js):**
```javascript
dragState: {
    active: false,
    shiftId: null,
    shiftElement: null,
    initialMouseX: 0,
    initialLeft: 0,
    initialWidth: 0,
    containerRect: null,
    originalUserId: null,
    currentTargetRow: null
}
```

**Event Handlers:**

| Handler | Trigger | Purpose |
|---------|---------|---------|
| `handleDragStart(e, shiftId)` | mousedown on shift | Initialize drag state, store original position |
| `handleDragMove(e)` | mousemove (global) | Update shift position, detect target row |
| `handleDragEnd(e)` | mouseup (global) | Calculate new time/user, call API |

**Drag Flow:**
1. **Start**: Store initial mouse position, shift position, container bounds
2. **Move**: Calculate delta X, update shift's left position, detect row under cursor
3. **End**: Convert final position to time, determine target user, call API

**Row Detection During Drag:**
```javascript
// Find which user row the cursor is over
const rows = document.querySelectorAll('[data-user-id]');
let targetUserId = this.dragState.originalUserId;

rows.forEach(row => {
    const rowRect = row.getBoundingClientRect();
    if (event.clientY >= rowRect.top && event.clientY <= rowRect.bottom) {
        targetUserId = row.dataset.userId || null;
    }
});
```

### 11.4 Resize Functionality

**Resize Handles:**
```html
<div class="shift-bar relative">
    <div class="resize-handle-left absolute left-0 top-0 bottom-0 w-2 cursor-ew-resize"></div>
    <!-- Shift content -->
    <div class="resize-handle-right absolute right-0 top-0 bottom-0 w-2 cursor-ew-resize"></div>
</div>
```

**Resize State:**
```javascript
resizeState: {
    active: false,
    edge: null,        // 'left' or 'right'
    shiftId: null,
    shiftElement: null,
    initialMouseX: 0,
    initialLeft: 0,
    initialWidth: 0,
    containerRect: null
}
```

**Resize Behavior:**
- **Left edge**: Adjusts start time, shift bar moves left/right
- **Right edge**: Adjusts end time, shift bar width changes
- **15-minute snapping**: Times snap to nearest 15-minute interval

**Time Snapping:**
```javascript
function snapToQuarterHour(minutes) {
    return Math.round(minutes / 15) * 15;
}

// Convert percentage back to time
function percentToTime(percent) {
    const totalMinutes = (percent / 100) * numHours * 60;
    const snappedMinutes = snapToQuarterHour(totalMinutes + dayStartHour * 60);
    const hours = Math.floor(snappedMinutes / 60);
    const mins = snappedMinutes % 60;
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
}
```

### 11.5 CSS Classes

```css
/* Resize handles - visible on hover */
.resize-handle-left,
.resize-handle-right {
    @apply opacity-0 transition-opacity duration-150;
}

.shift-bar:hover .resize-handle-left,
.shift-bar:hover .resize-handle-right {
    @apply opacity-100 bg-white/30;
}

/* Dragging state */
.shift-bar.dragging {
    @apply opacity-75 ring-2 ring-purple-500;
}

/* Target row highlight during drag */
.timeline-row.drag-target {
    @apply bg-purple-500/10;
}
```

---

## 12. Published Shift Handling

### 12.1 Overview

When shifts are moved (via drag-and-drop, resize, or modal edit), the system checks if the shift was published and handles it appropriately. Published shifts that are moved revert to draft status, requiring re-publication.

### 12.2 Workflow Order

The validation and warning flow follows this order:

1. **Clash Check First**: Before applying changes, the API validates for overlapping shifts
2. **Apply Changes**: If no clash, the shift is updated
3. **Status Reversion**: If shift was published and moved, status reverts to draft
4. **Warning Modal**: User is shown a warning that they'll need to republish

**Rationale:** Checking clashes before showing the published warning prevents showing a warning for a move that would fail anyway.

### 12.3 Backend Implementation

**ShiftController::update():**
```php
public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse|JsonResponse
{
    $data = $request->validated();

    // If shift is published and movement fields are changing, revert to draft
    if ($shift->status === ShiftStatus::Published) {
        $movementFields = ['date', 'start_time', 'end_time', 'user_id'];
        $isMoving = false;

        foreach ($movementFields as $field) {
            if (array_key_exists($field, $data)) {
                $currentValue = $shift->{$field};
                $newValue = $data[$field];

                // Normalize for comparison (handle Carbon objects)
                if ($currentValue instanceof \Carbon\Carbon) {
                    $currentValue = $currentValue->format($field === 'date' ? 'Y-m-d' : 'H:i');
                }

                if ($currentValue != $newValue) {
                    $isMoving = true;
                    break;
                }
            }
        }

        if ($isMoving) {
            $data['status'] = ShiftStatus::Draft;
        }
    }

    $shift->update($data);
    // ... rest of method
}
```

**Movement Fields:**
| Field | Description |
|-------|-------------|
| `date` | Shift date |
| `start_time` | Shift start time |
| `end_time` | Shift end time |
| `user_id` | Assigned employee |

**Non-Movement Fields (don't trigger reversion):**
- `notes`
- `break_duration_minutes`
- `location_id`, `department_id`, `business_role_id`

### 12.4 Frontend Warning Modal

**After Successful Move:**
```javascript
// In handleDragEnd or handleResizeEnd
const wasPublished = shiftData.status === 'published';

// Make API call...
const response = await fetch(`/shifts/${shiftId}`, { ... });

if (response.ok && wasPublished) {
    this.showConfirm(
        'Shift Moved',
        'This shift is already published. If you move it you will need to publish it again.',
        () => window.location.reload(),
        () => window.location.reload()
    );
} else {
    window.location.reload();
}
```

**Important: Clear Drag State Before Async:**
```javascript
async handleDragEnd(event) {
    if (!this.dragState.active) return;

    // CRITICAL: Stop tracking mouse immediately
    this.dragState.active = false;

    // Now safe to do async operations
    // (prevents shift from following cursor while modal is shown)
}
```

### 12.5 Test Coverage

| Test | Description |
|------|-------------|
| `test_moving_published_shift_reverts_to_draft` | Changing date reverts to draft |
| `test_updating_non_movement_fields_keeps_published_status` | Changing notes doesn't revert |
| `test_moving_draft_shift_stays_draft` | Draft shifts remain draft |
| `test_reassigning_published_shift_reverts_to_draft` | Changing user_id reverts |

---

## 13. SuperAdmin Features

### 13.1 Overview

SuperAdmin is a platform-level role for Plannrly staff to manage tenants, users, and support operations. SuperAdmins have no tenant_id and can access all tenant data.

### 13.2 Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /super-admin/dashboard | SuperAdmin\DashboardController@index | SuperAdmin dashboard |
| GET | /super-admin/tenants | SuperAdmin\TenantController@index | List all tenants |
| GET | /super-admin/tenants/{tenant} | SuperAdmin\TenantController@show | View tenant details |
| GET | /super-admin/users | SuperAdmin\UserController@index | List all users across tenants |
| POST | /super-admin/impersonate/{user} | SuperAdmin\ImpersonationController@start | Start impersonating user |
| DELETE | /super-admin/impersonate | SuperAdmin\ImpersonationController@stop | Stop impersonation |

### 13.3 Middleware

**EnsureSuperAdmin Middleware:**
```php
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isSuperAdmin()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        return $next($request);
    }
}
```

**Route Registration:**
```php
Route::prefix('super-admin')
    ->middleware(['auth', 'super_admin'])
    ->group(function () {
        // SuperAdmin routes
    });
```

### 13.4 User Impersonation

Allows SuperAdmins to log in as any user to debug issues or provide support.

**Start Impersonation:**
```php
public function start(User $user): RedirectResponse
{
    // Store original user ID in session
    session(['impersonating_from' => auth()->id()]);

    // Login as target user
    Auth::login($user);

    return redirect()->route('dashboard')
        ->with('info', "Now impersonating {$user->full_name}");
}
```

**Stop Impersonation:**
```php
public function stop(): RedirectResponse
{
    $originalId = session('impersonating_from');

    if ($originalId) {
        $originalUser = User::find($originalId);
        Auth::login($originalUser);
        session()->forget('impersonating_from');
    }

    return redirect()->route('super-admin.dashboard')
        ->with('success', 'Impersonation ended');
}
```

**Impersonation Banner:**
When impersonating, a banner is shown at the top of every page:
```html
@if(session('impersonating_from'))
<div class="bg-amber-500 text-black text-center py-2">
    <span>Impersonating {{ auth()->user()->full_name }}</span>
    <form action="{{ route('super-admin.impersonate.stop') }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="underline ml-4">Stop Impersonating</button>
    </form>
</div>
@endif
```

### 13.5 Dashboard Metrics

The SuperAdmin dashboard displays:
- Total tenant count
- Total user count across all tenants
- Active users (last 30 days)
- Recent registrations
- System health indicators

### 13.6 Bypassing Tenant Scope

SuperAdmin queries bypass the TenantScope:
```php
// In SuperAdmin controllers
User::withoutGlobalScope(TenantScope::class)->get();
Tenant::with('users')->get();
```

---

## 14. Responsive Design (v2.0)

### 14.1 Architecture Change

In version 2.0, the separate mobile layouts and controllers were removed in favor of a unified responsive design. The main `app.blade.php` layout now handles all screen sizes.

**Removed Files:**
- `app/Http/Controllers/MyShiftsController.php`
- `app/Http/Controllers/MySwapsController.php`
- `app/Http/Controllers/TimeClockController.php`
- `app/Http/Controllers/ProfileController.php`
- `resources/views/components/layouts/mobile.blade.php`
- `resources/views/components/bottom-nav.blade.php`
- `resources/views/my-shifts/*`
- `resources/views/my-swaps/*`
- `resources/views/time-clock/*`
- `resources/views/profile/*`

**Removed Routes:**
- `/my-shifts`
- `/time-clock` and related clock in/out routes
- `/my-swaps`
- `/profile`

### 14.2 Current Approach

All views now use responsive Tailwind CSS classes for mobile support:
- Sidebar collapses on mobile (hidden by default, toggle button)
- Tables become scrollable horizontally
- Grid layouts adjust columns based on screen width
- Touch-friendly button sizes on smaller screens

---

## 15. Mobile Interface Architecture

This section details the technical implementation of Plannrly's seamless mobile experience, including Progressive Web App (PWA) capabilities, offline support, and mobile-optimized components.

### 15.1 Progressive Web App (PWA) Configuration

#### Web App Manifest

**File:** `public/manifest.json`

```json
{
  "name": "Plannrly - Workforce Scheduling",
  "short_name": "Plannrly",
  "description": "Smart workforce scheduling and time tracking",
  "start_url": "/dashboard",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#6366f1",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png",
      "purpose": "maskable any"
    },
    {
      "src": "/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png",
      "purpose": "maskable any"
    },
    {
      "src": "/icons/icon-128x128.png",
      "sizes": "128x128",
      "type": "image/png",
      "purpose": "maskable any"
    },
    {
      "src": "/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png",
      "purpose": "maskable any"
    },
    {
      "src": "/icons/icon-152x152.png",
      "sizes": "152x152",
      "type": "image/png",
      "purpose": "maskable any"
    },
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "maskable any"
    },
    {
      "src": "/icons/icon-384x384.png",
      "sizes": "384x384",
      "type": "image/png",
      "purpose": "maskable any"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable any"
    }
  ],
  "categories": ["business", "productivity"],
  "shortcuts": [
    {
      "name": "Clock In",
      "url": "/dashboard?action=clock-in",
      "icons": [{ "src": "/icons/clock-in.png", "sizes": "96x96" }]
    },
    {
      "name": "My Schedule",
      "url": "/schedule",
      "icons": [{ "src": "/icons/schedule.png", "sizes": "96x96" }]
    },
    {
      "name": "Request Leave",
      "url": "/leave-requests/create",
      "icons": [{ "src": "/icons/leave.png", "sizes": "96x96" }]
    }
  ]
}
```

#### Service Worker

**File:** `public/sw.js`

```javascript
const CACHE_VERSION = 'v1';
const STATIC_CACHE = `plannrly-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `plannrly-dynamic-${CACHE_VERSION}`;
const OFFLINE_CACHE = `plannrly-offline-${CACHE_VERSION}`;

// Assets to pre-cache on install
const STATIC_ASSETS = [
  '/',
  '/offline',
  '/css/app.css',
  '/js/app.js',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png'
];

// API routes to cache for offline
const CACHEABLE_API_ROUTES = [
  '/api/my-schedule',
  '/api/my-profile',
  '/api/notifications'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(
        keys.filter(key => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
          .map(key => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Handle API requests
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(networkFirstStrategy(request));
    return;
  }

  // Handle page navigation
  if (request.mode === 'navigate') {
    event.respondWith(networkFirstWithOfflineFallback(request));
    return;
  }

  // Handle static assets
  event.respondWith(cacheFirstStrategy(request));
});

// Network first, fall back to cache
async function networkFirstStrategy(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    return cached || new Response(JSON.stringify({ offline: true }), {
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

// Cache first, fall back to network
async function cacheFirstStrategy(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    return new Response('', { status: 404 });
  }
}

// Network first with offline page fallback
async function networkFirstWithOfflineFallback(request) {
  try {
    const response = await fetch(request);
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) return cached;
    return caches.match('/offline');
  }
}

// Handle background sync for offline actions
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-clock-events') {
    event.waitUntil(syncClockEvents());
  }
  if (event.tag === 'sync-leave-requests') {
    event.waitUntil(syncLeaveRequests());
  }
});

// Push notification handling
self.addEventListener('push', (event) => {
  const data = event.data?.json() || {};
  const options = {
    body: data.body,
    icon: '/icons/icon-192x192.png',
    badge: '/icons/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: { url: data.url || '/dashboard' },
    actions: data.actions || []
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Plannrly', options)
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(
    clients.openWindow(event.notification.data.url)
  );
});
```

### 15.2 Mobile API Endpoints

Additional API endpoints optimized for mobile consumption:

```php
// routes/api.php

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // Mobile-optimized schedule endpoint
    Route::get('/my-schedule', [MobileScheduleController::class, 'index']);
    Route::get('/my-schedule/today', [MobileScheduleController::class, 'today']);
    Route::get('/my-schedule/week', [MobileScheduleController::class, 'week']);

    // Clock in/out with offline support
    Route::post('/clock/in', [MobileClockController::class, 'clockIn']);
    Route::post('/clock/out', [MobileClockController::class, 'clockOut']);
    Route::post('/clock/sync', [MobileClockController::class, 'syncOfflineEvents']);

    // Mobile notifications
    Route::get('/notifications/unread', [MobileNotificationController::class, 'unread']);
    Route::post('/notifications/mark-read', [MobileNotificationController::class, 'markRead']);

    // Push subscription management
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe']);
    Route::delete('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe']);

    // Offline data sync
    Route::get('/sync/initial', [SyncController::class, 'initialData']);
    Route::post('/sync/changes', [SyncController::class, 'syncChanges']);
});
```

### 15.3 Database Tables for Mobile Features

#### push_subscriptions

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| user_id | bigint | FK → users.id | Subscriber |
| endpoint | text | NOT NULL | Push service endpoint URL |
| public_key | string(255) | NOT NULL | P-256 public key |
| auth_token | string(255) | NOT NULL | Auth secret |
| content_encoding | string(50) | DEFAULT 'aesgcm' | Encoding type |
| user_agent | string(255) | NULLABLE | Device user agent |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### offline_sync_queue

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| user_id | bigint | FK → users.id | User who queued action |
| action_type | string(50) | NOT NULL | clock_in, clock_out, leave_request, etc. |
| payload | json | NOT NULL | Action data |
| client_timestamp | timestamp | NOT NULL | When action occurred on device |
| synced_at | timestamp | NULLABLE | When successfully processed |
| error_message | text | NULLABLE | If sync failed |
| created_at | timestamp | | |

#### notification_preferences

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| user_id | bigint | FK → users.id, UNIQUE | User preferences |
| shift_published | boolean | DEFAULT true | New shift notifications |
| shift_changed | boolean | DEFAULT true | Shift modification alerts |
| shift_reminder | boolean | DEFAULT true | Upcoming shift reminders |
| shift_reminder_minutes | int | DEFAULT 60 | Minutes before shift |
| leave_status | boolean | DEFAULT true | Leave request updates |
| swap_requests | boolean | DEFAULT true | Swap request notifications |
| clock_reminders | boolean | DEFAULT true | Clock in/out reminders |
| quiet_hours_start | time | NULLABLE | Start of quiet hours |
| quiet_hours_end | time | NULLABLE | End of quiet hours |
| created_at | timestamp | | |
| updated_at | timestamp | | |

### 15.4 Mobile Controllers

#### MobileScheduleController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobileShiftResource;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $startDate = $request->query('start', now()->startOfWeek());
        $endDate = $request->query('end', now()->addDays(14));

        $shifts = Shift::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->published()
            ->with(['location:id,name', 'department:id,name,color', 'businessRole:id,name'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'shifts' => MobileShiftResource::collection($shifts),
            'sync_timestamp' => now()->toIso8601String(),
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->toDateString();

        $shifts = Shift::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->published()
            ->with(['location', 'department', 'businessRole', 'timeEntry'])
            ->orderBy('start_time')
            ->get();

        $activeTimeEntry = $user->timeEntries()
            ->whereNull('clock_out_at')
            ->first();

        return response()->json([
            'date' => $today,
            'shifts' => MobileShiftResource::collection($shifts),
            'active_clock' => $activeTimeEntry ? [
                'shift_id' => $activeTimeEntry->shift_id,
                'clock_in_at' => $activeTimeEntry->clock_in_at->toIso8601String(),
                'on_break' => $activeTimeEntry->on_break,
            ] : null,
        ]);
    }

    public function week(Request $request): JsonResponse
    {
        $user = $request->user();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $shifts = Shift::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->published()
            ->with(['location:id,name', 'department:id,name,color'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $totalHours = $shifts->sum('duration_hours');

        return response()->json([
            'week_start' => $startOfWeek->toDateString(),
            'week_end' => $endOfWeek->toDateString(),
            'shifts' => MobileShiftResource::collection($shifts),
            'total_hours' => round($totalHours, 2),
            'shift_count' => $shifts->count(),
        ]);
    }
}
```

#### MobileClockController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\TimeEntry;
use App\Services\ClockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileClockController extends Controller
{
    public function __construct(
        private ClockService $clockService
    ) {}

    public function clockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'client_timestamp' => 'nullable|date',
            'offline' => 'boolean',
        ]);

        $user = $request->user();
        $shift = Shift::findOrFail($validated['shift_id']);

        // Verify shift belongs to user
        if ($shift->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check for existing active clock
        $existingEntry = TimeEntry::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->first();

        if ($existingEntry) {
            return response()->json([
                'error' => 'Already clocked in',
                'active_entry' => $existingEntry->id,
            ], 422);
        }

        $timeEntry = $this->clockService->clockIn(
            user: $user,
            shift: $shift,
            latitude: $validated['latitude'] ?? null,
            longitude: $validated['longitude'] ?? null,
            clientTimestamp: $validated['client_timestamp'] ?? null
        );

        return response()->json([
            'success' => true,
            'time_entry_id' => $timeEntry->id,
            'clock_in_at' => $timeEntry->clock_in_at->toIso8601String(),
            'server_timestamp' => now()->toIso8601String(),
        ]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'time_entry_id' => 'required|exists:time_entries,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'client_timestamp' => 'nullable|date',
            'offline' => 'boolean',
        ]);

        $user = $request->user();
        $timeEntry = TimeEntry::findOrFail($validated['time_entry_id']);

        if ($timeEntry->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($timeEntry->clock_out_at) {
            return response()->json(['error' => 'Already clocked out'], 422);
        }

        $timeEntry = $this->clockService->clockOut(
            timeEntry: $timeEntry,
            latitude: $validated['latitude'] ?? null,
            longitude: $validated['longitude'] ?? null,
            clientTimestamp: $validated['client_timestamp'] ?? null
        );

        return response()->json([
            'success' => true,
            'clock_out_at' => $timeEntry->clock_out_at->toIso8601String(),
            'total_hours' => $timeEntry->total_hours,
            'server_timestamp' => now()->toIso8601String(),
        ]);
    }

    public function syncOfflineEvents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'events' => 'required|array',
            'events.*.type' => 'required|in:clock_in,clock_out,break_start,break_end',
            'events.*.shift_id' => 'required_if:events.*.type,clock_in|exists:shifts,id',
            'events.*.time_entry_id' => 'required_unless:events.*.type,clock_in|exists:time_entries,id',
            'events.*.client_timestamp' => 'required|date',
            'events.*.latitude' => 'nullable|numeric',
            'events.*.longitude' => 'nullable|numeric',
        ]);

        $user = $request->user();
        $results = [];

        DB::transaction(function () use ($validated, $user, &$results) {
            foreach ($validated['events'] as $event) {
                try {
                    $result = $this->processOfflineEvent($user, $event);
                    $results[] = [
                        'client_timestamp' => $event['client_timestamp'],
                        'success' => true,
                        'server_id' => $result->id,
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'client_timestamp' => $event['client_timestamp'],
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return response()->json([
            'results' => $results,
            'server_timestamp' => now()->toIso8601String(),
        ]);
    }

    private function processOfflineEvent($user, array $event)
    {
        return match ($event['type']) {
            'clock_in' => $this->clockService->clockIn(
                user: $user,
                shift: Shift::find($event['shift_id']),
                latitude: $event['latitude'] ?? null,
                longitude: $event['longitude'] ?? null,
                clientTimestamp: $event['client_timestamp']
            ),
            'clock_out' => $this->clockService->clockOut(
                timeEntry: TimeEntry::find($event['time_entry_id']),
                latitude: $event['latitude'] ?? null,
                longitude: $event['longitude'] ?? null,
                clientTimestamp: $event['client_timestamp']
            ),
            'break_start' => $this->clockService->startBreak(
                timeEntry: TimeEntry::find($event['time_entry_id']),
                clientTimestamp: $event['client_timestamp']
            ),
            'break_end' => $this->clockService->endBreak(
                timeEntry: TimeEntry::find($event['time_entry_id']),
                clientTimestamp: $event['client_timestamp']
            ),
        };
    }
}
```

### 15.5 Push Notification Service

```php
<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ]);
    }

    public function sendToUser(User $user, array $payload): void
    {
        $subscriptions = $user->pushSubscriptions;

        foreach ($subscriptions as $pushSubscription) {
            $this->send($pushSubscription, $payload);
        }
    }

    public function send(PushSubscription $pushSubscription, array $payload): bool
    {
        $subscription = Subscription::create([
            'endpoint' => $pushSubscription->endpoint,
            'publicKey' => $pushSubscription->public_key,
            'authToken' => $pushSubscription->auth_token,
            'contentEncoding' => $pushSubscription->content_encoding,
        ]);

        $report = $this->webPush->sendOneNotification(
            $subscription,
            json_encode($payload)
        );

        if ($report->isSuccess()) {
            return true;
        }

        // Handle expired subscriptions
        if ($report->isSubscriptionExpired()) {
            $pushSubscription->delete();
        }

        return false;
    }

    public function sendShiftReminder(User $user, $shift): void
    {
        $this->sendToUser($user, [
            'title' => 'Upcoming Shift',
            'body' => "Your shift starts at {$shift->start_time->format('g:i A')}",
            'url' => route('dashboard'),
            'actions' => [
                ['action' => 'view', 'title' => 'View Details'],
            ],
        ]);
    }

    public function sendShiftPublished(User $user, $shift): void
    {
        $this->sendToUser($user, [
            'title' => 'New Shift Published',
            'body' => "You have a new shift on {$shift->date->format('M j')}",
            'url' => route('schedule.index', ['start' => $shift->date->startOfWeek()]),
        ]);
    }

    public function sendLeaveApproved(User $user, $leaveRequest): void
    {
        $this->sendToUser($user, [
            'title' => 'Leave Request Approved',
            'body' => "Your leave request for {$leaveRequest->start_date->format('M j')} has been approved",
            'url' => route('leave-requests.index'),
        ]);
    }

    public function sendSwapRequest(User $user, $swapRequest): void
    {
        $this->sendToUser($user, [
            'title' => 'Shift Swap Request',
            'body' => "{$swapRequest->requester->name} wants to swap shifts with you",
            'url' => route('shift-swaps.index'),
            'actions' => [
                ['action' => 'approve', 'title' => 'Approve'],
                ['action' => 'deny', 'title' => 'Deny'],
            ],
        ]);
    }
}
```

### 15.6 Mobile-Optimized Blade Components

#### Bottom Navigation Component

**File:** `resources/views/components/mobile-nav.blade.php`

```blade
@props(['active' => null])

<nav class="fixed bottom-0 inset-x-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 md:hidden z-50 safe-area-inset-bottom">
    <div class="flex justify-around items-center h-16">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ $active === 'dashboard' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-heroicon-o-home class="w-6 h-6" />
            <span class="text-xs mt-1">Home</span>
        </a>

        {{-- Schedule --}}
        <a href="{{ route('schedule.index') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ $active === 'schedule' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-heroicon-o-calendar class="w-6 h-6" />
            <span class="text-xs mt-1">Schedule</span>
        </a>

        {{-- Clock (prominent center button) --}}
        <button type="button"
                x-data
                @click="$dispatch('open-clock-modal')"
                class="flex flex-col items-center justify-center w-full h-full -mt-6">
            <div class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center shadow-lg">
                <x-heroicon-o-clock class="w-7 h-7 text-white" />
            </div>
            <span class="text-xs mt-1 text-gray-500 dark:text-gray-400">Clock</span>
        </button>

        {{-- Requests --}}
        <a href="{{ route('leave-requests.index') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ $active === 'requests' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-heroicon-o-document-text class="w-6 h-6" />
            <span class="text-xs mt-1">Requests</span>
        </a>

        {{-- Profile --}}
        <a href="{{ route('profile.show') }}"
           class="flex flex-col items-center justify-center w-full h-full {{ $active === 'profile' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-heroicon-o-user-circle class="w-6 h-6" />
            <span class="text-xs mt-1">Profile</span>
        </a>
    </div>
</nav>
```

#### Pull-to-Refresh Component

**File:** `resources/views/components/pull-to-refresh.blade.php`

```blade
@props(['url' => null])

<div x-data="pullToRefresh('{{ $url ?? request()->url() }}')"
     x-on:touchstart="touchStart($event)"
     x-on:touchmove="touchMove($event)"
     x-on:touchend="touchEnd()"
     class="relative">

    {{-- Pull indicator --}}
    <div class="absolute inset-x-0 top-0 flex justify-center transform transition-transform duration-200"
         :style="{ transform: `translateY(${Math.min(pullDistance - 60, 20)}px)` }"
         x-show="pulling || refreshing">
        <div class="w-8 h-8 flex items-center justify-center">
            <svg x-show="!refreshing"
                 class="w-6 h-6 text-gray-400 transition-transform duration-200"
                 :style="{ transform: `rotate(${Math.min(pullDistance * 2, 180)}deg)` }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
            <svg x-show="refreshing" class="w-6 h-6 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    {{-- Content --}}
    <div :style="{ transform: `translateY(${pulling ? Math.min(pullDistance - 60, 60) : 0}px)` }"
         class="transition-transform duration-200">
        {{ $slot }}
    </div>
</div>

<script>
function pullToRefresh(url) {
    return {
        startY: 0,
        pullDistance: 0,
        pulling: false,
        refreshing: false,
        threshold: 80,

        touchStart(e) {
            if (window.scrollY === 0) {
                this.startY = e.touches[0].clientY;
                this.pulling = true;
            }
        },

        touchMove(e) {
            if (!this.pulling) return;
            this.pullDistance = e.touches[0].clientY - this.startY;
            if (this.pullDistance > 0) {
                e.preventDefault();
            }
        },

        async touchEnd() {
            if (this.pullDistance >= this.threshold && !this.refreshing) {
                this.refreshing = true;
                await this.refresh();
            }
            this.pulling = false;
            this.pullDistance = 0;
        },

        async refresh() {
            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (response.ok) {
                    window.location.reload();
                }
            } finally {
                this.refreshing = false;
            }
        }
    };
}
</script>
```

#### Mobile Clock Widget

**File:** `resources/views/components/mobile-clock-widget.blade.php`

```blade
@props(['shift' => null, 'activeEntry' => null])

<div x-data="clockWidget({{ json_encode([
    'shift' => $shift ? [
        'id' => $shift->id,
        'start' => $shift->start_time->format('H:i'),
        'end' => $shift->end_time->format('H:i'),
    ] : null,
    'activeEntry' => $activeEntry ? [
        'id' => $activeEntry->id,
        'clockInAt' => $activeEntry->clock_in_at->toIso8601String(),
        'onBreak' => $activeEntry->on_break,
    ] : null,
]) }})"
     class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">

    {{-- Current time --}}
    <div class="text-center mb-4">
        <p class="text-4xl font-bold text-gray-900 dark:text-white" x-text="currentTime"></p>
        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="currentDate"></p>
    </div>

    {{-- Shift info --}}
    <template x-if="shift">
        <div class="text-center mb-4 py-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-300">Today's Shift</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-white"
               x-text="`${shift.start} - ${shift.end}`"></p>
        </div>
    </template>

    {{-- Clock button --}}
    <template x-if="!activeEntry">
        <button @click="clockIn()"
                :disabled="loading || !shift"
                class="w-full py-4 px-6 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-xl text-lg transition-colors touch-manipulation">
            <span x-show="!loading">Clock In</span>
            <span x-show="loading" class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Processing...
            </span>
        </button>
    </template>

    <template x-if="activeEntry">
        <div class="space-y-3">
            {{-- Duration --}}
            <div class="text-center py-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-sm text-green-600 dark:text-green-400">Clocked In</p>
                <p class="text-2xl font-bold text-green-700 dark:text-green-300" x-text="elapsedTime"></p>
            </div>

            {{-- Break button --}}
            <button @click="toggleBreak()"
                    :disabled="loading"
                    class="w-full py-3 px-6 border-2 border-yellow-500 text-yellow-600 dark:text-yellow-400 font-semibold rounded-xl transition-colors touch-manipulation"
                    :class="activeEntry.onBreak ? 'bg-yellow-50 dark:bg-yellow-900/20' : ''">
                <span x-text="activeEntry.onBreak ? 'End Break' : 'Start Break'"></span>
            </button>

            {{-- Clock out button --}}
            <button @click="clockOut()"
                    :disabled="loading"
                    class="w-full py-4 px-6 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-semibold rounded-xl text-lg transition-colors touch-manipulation">
                <span x-show="!loading">Clock Out</span>
                <span x-show="loading" class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </div>
    </template>
</div>
```

### 15.7 Offline Data Storage (IndexedDB)

```javascript
// resources/js/offline-storage.js

const DB_NAME = 'plannrly-offline';
const DB_VERSION = 1;

class OfflineStorage {
    constructor() {
        this.db = null;
    }

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Shifts store
                if (!db.objectStoreNames.contains('shifts')) {
                    const shiftsStore = db.createObjectStore('shifts', { keyPath: 'id' });
                    shiftsStore.createIndex('date', 'date', { unique: false });
                    shiftsStore.createIndex('userId', 'user_id', { unique: false });
                }

                // Pending actions store
                if (!db.objectStoreNames.contains('pendingActions')) {
                    const actionsStore = db.createObjectStore('pendingActions', {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    actionsStore.createIndex('timestamp', 'timestamp', { unique: false });
                }

                // User profile store
                if (!db.objectStoreNames.contains('profile')) {
                    db.createObjectStore('profile', { keyPath: 'id' });
                }

                // Sync metadata
                if (!db.objectStoreNames.contains('syncMeta')) {
                    db.createObjectStore('syncMeta', { keyPath: 'key' });
                }
            };
        });
    }

    // Store shifts for offline access
    async storeShifts(shifts) {
        const tx = this.db.transaction('shifts', 'readwrite');
        const store = tx.objectStore('shifts');

        for (const shift of shifts) {
            await store.put(shift);
        }

        await this.setSyncMeta('shifts_last_sync', new Date().toISOString());
    }

    // Get shifts for date range
    async getShifts(startDate, endDate) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('shifts', 'readonly');
            const store = tx.objectStore('shifts');
            const index = store.index('date');
            const range = IDBKeyRange.bound(startDate, endDate);
            const request = index.getAll(range);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // Queue action for later sync
    async queueAction(action) {
        const tx = this.db.transaction('pendingActions', 'readwrite');
        const store = tx.objectStore('pendingActions');

        await store.add({
            ...action,
            timestamp: new Date().toISOString(),
            synced: false
        });
    }

    // Get pending actions
    async getPendingActions() {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('pendingActions', 'readonly');
            const store = tx.objectStore('pendingActions');
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result.filter(a => !a.synced));
            request.onerror = () => reject(request.error);
        });
    }

    // Mark actions as synced
    async markActionsSynced(ids) {
        const tx = this.db.transaction('pendingActions', 'readwrite');
        const store = tx.objectStore('pendingActions');

        for (const id of ids) {
            await store.delete(id);
        }
    }

    // Sync metadata helpers
    async setSyncMeta(key, value) {
        const tx = this.db.transaction('syncMeta', 'readwrite');
        const store = tx.objectStore('syncMeta');
        await store.put({ key, value });
    }

    async getSyncMeta(key) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('syncMeta', 'readonly');
            const store = tx.objectStore('syncMeta');
            const request = store.get(key);

            request.onsuccess = () => resolve(request.result?.value);
            request.onerror = () => reject(request.error);
        });
    }
}

export const offlineStorage = new OfflineStorage();
```

### 15.8 Mobile-Specific CSS Utilities

```css
/* resources/css/mobile.css */

/* Safe area insets for notched devices */
.safe-area-inset-top {
    padding-top: env(safe-area-inset-top);
}

.safe-area-inset-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}

/* Prevent text selection on touch */
.touch-manipulation {
    touch-action: manipulation;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}

/* Minimum touch target size */
.touch-target {
    min-height: 44px;
    min-width: 44px;
}

/* Hide scrollbar but keep functionality */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Momentum scrolling on iOS */
.scroll-momentum {
    -webkit-overflow-scrolling: touch;
}

/* Prevent pull-to-refresh on Chrome Android */
.overscroll-none {
    overscroll-behavior: none;
}

/* Full viewport height accounting for mobile browser chrome */
.h-screen-safe {
    height: 100vh;
    height: 100dvh;
}

/* Bottom sheet animation */
.bottom-sheet-enter {
    transform: translateY(100%);
}

.bottom-sheet-enter-active {
    transform: translateY(0);
    transition: transform 0.3s ease-out;
}

.bottom-sheet-leave-active {
    transform: translateY(100%);
    transition: transform 0.2s ease-in;
}

/* Swipe action indicators */
.swipe-action-left {
    background: linear-gradient(to right, #ef4444 0%, transparent 100%);
}

.swipe-action-right {
    background: linear-gradient(to left, #22c55e 0%, transparent 100%);
}
```

---

## Appendix A: Migration Commands

```bash
# Fresh migration with seeders
php artisan migrate:fresh --seed

# Rollback last batch
php artisan migrate:rollback

# Reset and re-run all migrations
php artisan migrate:reset
php artisan migrate

# Check migration status
php artisan migrate:status
```

## Appendix B: Useful Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# List all routes
php artisan route:list

# Regenerate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models

# Run code formatting
vendor/bin/pint

# Run tests with specific filter
php artisan test --filter=test_admin_can_create_location
```
