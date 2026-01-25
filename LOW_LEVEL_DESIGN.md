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

#### rotas
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| location_id | BIGINT UNSIGNED | FK → locations.id, NULLABLE | Location scope |
| department_id | BIGINT UNSIGNED | FK → departments.id, NULLABLE | Department scope |
| name | VARCHAR(255) | NOT NULL | Rota name |
| start_date | DATE | NOT NULL | Period start |
| end_date | DATE | NOT NULL | Period end |
| status | VARCHAR(255) | DEFAULT 'draft' | Enum: draft/published/archived |
| published_at | TIMESTAMP | NULLABLE | Publication time |
| published_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Publisher |
| created_by | BIGINT UNSIGNED | FK → users.id, NULLABLE | Creator |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (tenant_id)
- INDEX (location_id)
- INDEX (department_id)
- INDEX (status)
- INDEX (start_date, end_date)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- location_id → locations(id) ON DELETE SET NULL
- department_id → departments(id) ON DELETE SET NULL
- published_by → users(id) ON DELETE SET NULL
- created_by → users(id) ON DELETE SET NULL

---

#### shifts
| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| tenant_id | BIGINT UNSIGNED | FK → tenants.id | Tenant reference |
| rota_id | BIGINT UNSIGNED | FK → rotas.id | Parent rota |
| location_id | BIGINT UNSIGNED | FK → locations.id | Location |
| department_id | BIGINT UNSIGNED | FK → departments.id | Department |
| business_role_id | BIGINT UNSIGNED | FK → business_roles.id | Required role |
| user_id | BIGINT UNSIGNED | FK → users.id, NULLABLE | Assigned user |
| date | DATE | NOT NULL | Shift date |
| start_time | TIME | NOT NULL | Start time |
| end_time | TIME | NOT NULL | End time |
| break_duration_minutes | INTEGER | NULLABLE | Break duration |
| notes | TEXT | NULLABLE | Shift notes |
| status | VARCHAR(255) | DEFAULT 'scheduled' | Shift status |
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
- INDEX (rota_id)
- INDEX (user_id)
- INDEX (date)
- INDEX (status)
- INDEX (location_id, department_id)

**Foreign Keys:**
- tenant_id → tenants(id) ON DELETE CASCADE
- rota_id → rotas(id) ON DELETE CASCADE
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
| page | VARCHAR(255) | NOT NULL | Page identifier |
| filters | JSON | NOT NULL | Saved filter config |
| created_at | TIMESTAMP | NULLABLE | Creation timestamp |
| updated_at | TIMESTAMP | NULLABLE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, page)
- INDEX (user_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

---

## 2. Model Relationships

### 2.1 Tenant Model

```php
class Tenant extends Model
{
    // Has Many
    public function users(): HasMany
    public function locations(): HasMany
    public function departments(): HasMany
    public function businessRoles(): HasMany
    public function rotas(): HasMany
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
    public function shifts(): HasMany
    public function timeEntries(): HasMany
    public function leaveRequests(): HasMany
    public function leaveAllowances(): HasMany
    public function notificationPreferences(): HasMany

    // Accessors
    public function getFullNameAttribute(): string
    public function getInitialsAttribute(): string

    // Role Checking Methods
    public function isSuperAdmin(): bool
    public function isAdmin(): bool
    public function isLocationAdmin(?int $locationId = null): bool
    public function isDepartmentAdmin(?int $departmentId = null): bool
    public function getHighestRole(): ?SystemRole
    public function canManageLocation(Location $location): bool
    public function canManageDepartment(Department $department): bool

    // Scopes
    public function scopeActive(Builder $query): Builder
}
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
    public function rotas(): HasMany

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
    public function rota(): BelongsTo
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
}
```

### 2.7 LeaveRequest Model

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

### 4.2 Role Hierarchy

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

### 4.3 Policy Implementation

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

### 4.4 Middleware Implementation

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
- Rota
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

#### Rota Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /rotas | RotaController@index | List rotas |
| GET | /rotas/create | RotaController@create | Create form |
| POST | /rotas | RotaController@store | Store rota |
| GET | /rotas/{rota} | RotaController@show | View schedule |
| GET | /rotas/{rota}/edit | RotaController@edit | Edit form |
| PUT | /rotas/{rota} | RotaController@update | Update rota |
| DELETE | /rotas/{rota} | RotaController@destroy | Delete rota |
| POST | /rotas/{rota}/publish | RotaController@publish | Publish rota |

#### Shift Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /shifts | ShiftController@index | List shifts |
| POST | /shifts | ShiftController@store | Store shift |
| GET | /shifts/{shift}/edit | ShiftController@edit | Edit form |
| PUT | /shifts/{shift} | ShiftController@update | Update shift |
| DELETE | /shifts/{shift} | ShiftController@destroy | Delete shift |
| POST | /shifts/{shift}/assign | ShiftController@assign | Assign user |

#### Leave Request Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | /leave-requests | LeaveRequestController@index | List requests |
| GET | /leave-requests/create | LeaveRequestController@create | Create form |
| POST | /leave-requests | LeaveRequestController@store | Store request |
| GET | /leave-requests/{leaveRequest} | LeaveRequestController@show | View request |
| POST | /leave-requests/{leaveRequest}/review | LeaveRequestController@review | Approve/Reject |

### 6.2 API Routes (v1 - Placeholder)

```php
// routes/api.php
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Future API implementation
});
```

---

## 7. Event/Listener Architecture

### 7.1 Planned Events (Future Implementation)

| Event | Description | Listeners |
|-------|-------------|-----------|
| ShiftAssigned | User assigned to shift | SendShiftNotification |
| ShiftUpdated | Shift details changed | SendShiftUpdateNotification |
| RotaPublished | Rota made visible | NotifyAffectedEmployees |
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
    ->forRota($rota)
    ->assigned($user)
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
