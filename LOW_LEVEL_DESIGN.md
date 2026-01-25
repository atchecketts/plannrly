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
| actual_break_minutes | INTEGER | NULLABLE | Total break minutes |
| notes | TEXT | NULLABLE | Entry notes |
| clock_in_location | JSON | NULLABLE | GPS coordinates |
| clock_out_location | JSON | NULLABLE | GPS coordinates |
| status | VARCHAR(255) | DEFAULT 'clocked_in' | Entry status |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (user_id)
- INDEX (shift_id)
- INDEX (status)
- INDEX (clock_in_at)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE
- shift_id → shifts(id) ON DELETE SET NULL

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

## 2. Model Relationships

### 2.1 Tenant Model

```php
class Tenant extends Model
{
    // Has One
    public function tenantSettings(): HasOne

    // Has Many
    public function users(): HasMany
    public function locations(): HasMany
    public function departments(): HasMany
    public function businessRoles(): HasMany
    public function shifts(): HasMany
    public function leaveTypes(): HasMany
    public function leaveRequests(): HasMany
}
```

### 2.2 User Model

```php
class User extends Authenticatable
{
    use BelongsToTenant, SoftDeletes;

    // Belongs To
    public function tenant(): BelongsTo

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

    // Accessors
    public function getFullNameAttribute(): string
    public function getInitialsAttribute(): string

    // Role Checking Methods
    public function isSuperAdmin(): bool
    public function isAdmin(): bool
    public function isLocationAdmin(?int $locationId = null): bool
    public function isDepartmentAdmin(?int $departmentId = null): bool
    public function isEmployee(): bool
    public function getHighestRole(): ?SystemRole
    public function canManageLocation(Location $location): bool
    public function canManageDepartment(Department $department): bool

    // Scopes
    public function scopeActive(Builder $query): Builder
    public function scopeForTenant(Builder $query, int $tenantId): Builder
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

### 2.8 LeaveRequest Model

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

---

---

## 11. Mobile Implementation

### 11.1 Mobile Layout Foundation (Phase 1)

The mobile interface provides a touch-friendly, responsive experience for employees using smartphones. The layout uses a dedicated mobile layout component with bottom navigation.

**Mobile Layout Component:**

```php
// resources/views/components/layouts/mobile.blade.php
@props(['title', 'active' => 'home', 'showHeader' => true, 'headerTitle' => null])
```

**Key Features:**
- Maximum width container (`max-w-md`) for optimal mobile viewing
- Viewport meta tag with `maximum-scale=1.0, user-scalable=no` for PWA-like behavior
- Safe area inset support for devices with notches
- Bottom navigation with 5 main sections
- Branded header with user avatar and greeting

### 11.2 Mobile Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /my-shifts | MyShiftsController@index | View employee's weekly shifts |
| GET | /time-clock | TimeClockController@index | Time clock interface |
| POST | /time-clock/clock-in | TimeClockController@clockIn | Clock in for shift |
| POST | /time-clock/clock-out | TimeClockController@clockOut | Clock out from shift |
| POST | /time-clock/start-break | TimeClockController@startBreak | Start break |
| POST | /time-clock/end-break | TimeClockController@endBreak | End break |
| GET | /my-swaps | MySwapsController@index | View swap requests |
| GET | /my-swaps/create/{shift} | MySwapsController@create | Create swap request form |
| POST | /my-swaps | MySwapsController@store | Submit swap request |
| GET | /profile | ProfileController@show | View/edit profile |
| PUT | /profile | ProfileController@update | Update profile |
| GET | /my-leave | LeaveRequestController@myRequests | View employee's leave requests |

### 11.3 Mobile Controllers

#### MyShiftsController

Displays employee's shifts grouped by date for the current week with navigation.

```php
class MyShiftsController extends Controller
{
    public function index(Request $request): View
    {
        // Uses visibleToUser() scope - only shows published shifts
        // Groups shifts by date
        // Calculates total hours and shift count for week
    }
}
```

**Query Parameters:**
- `start` - Start date for week view (default: start of current week)

#### TimeClockController

Handles time tracking with clock in/out and break management.

```php
class TimeClockController extends Controller
{
    public function index(): View
    {
        // Shows today's shift and active time entry
        // Displays worked minutes today
    }

    public function clockIn(Request $request): RedirectResponse|JsonResponse
    {
        // Creates TimeEntry with ClockedIn status
        // Validates: not already clocked in, has shift today
    }

    public function clockOut(Request $request): RedirectResponse|JsonResponse
    {
        // Updates TimeEntry with ClockedOut status
        // Validates: is currently clocked in
    }

    public function startBreak(Request $request): RedirectResponse|JsonResponse
    {
        // Updates TimeEntry to OnBreak status
        // Validates: is clocked in (not on break)
    }

    public function endBreak(Request $request): RedirectResponse|JsonResponse
    {
        // Updates TimeEntry back to ClockedIn status
        // Calculates and records break duration
    }
}
```

**Dual Response Format:**
All clock actions support both redirect (form) and JSON (API) responses based on request type.

#### MySwapsController

Handles employee-initiated shift swap requests.

```php
class MySwapsController extends Controller
{
    public function index(): View
    {
        // Outgoing: Requests I made
        // Incoming: Requests targeting me (pending only)
    }

    public function create(Shift $shift): View
    {
        // Shows swap form for employee's own shift
        // Lists available users with matching business role
    }

    public function store(Request $request): RedirectResponse
    {
        // Creates ShiftSwapRequest
        // Validates: shift belongs to user
    }
}
```

#### ProfileController

Employee profile viewing and editing.

```php
class ProfileController extends Controller
{
    public function show(): View
    {
        // Displays user info with departments and roles
    }

    public function update(Request $request): RedirectResponse
    {
        // Updates name, email, phone
        // Optional password change with current password verification
    }
}
```

### 11.4 Bottom Navigation Component

```php
// resources/views/components/bottom-nav.blade.php
@props(['active' => 'home'])
```

**Navigation Items:**

| Item | Route | Active State |
|------|-------|--------------|
| Home | dashboard | `active === 'home'` |
| Shifts | my-shifts.index | `active === 'shifts'` |
| Clock | time-clock.index | Always prominent (center) |
| Swap | my-swaps.index | `active === 'swaps'` |
| Profile | profile.show | `active === 'profile'` |

**Clock Button:**
The center clock button has a floating design with a larger hit area:
- `-mt-8` negative margin to float above nav bar
- Larger icon size (`w-7 h-7`)
- Accent background (`bg-brand-600`)
- Border matching page background (`border-gray-950`)

### 11.5 Mobile View Files

| View | Description |
|------|-------------|
| `my-shifts/index.blade.php` | Weekly shift calendar with day cards |
| `time-clock/index.blade.php` | Clock in/out interface with live time |
| `my-swaps/index.blade.php` | Incoming and outgoing swap requests |
| `my-swaps/create.blade.php` | Create swap request form |
| `profile/show.blade.php` | Profile view and edit form |
| `my-leave/index.blade.php` | Leave requests and balances |

### 11.6 Test Coverage

| Test Class | Test Cases |
|------------|------------|
| MyShiftsControllerTest | View shifts, week navigation, draft visibility, tenant isolation |
| EmployeeDashboardTest | Today's shift, week summary, leave balances, pending requests |
| TimeClockControllerTest | Clock in/out, break management, validation, JSON responses |
| MySwapsControllerTest | View swaps, create requests, authorization |
| ProfileControllerTest | View profile, update info, change password, validation |

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
