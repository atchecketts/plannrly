# Plannrly - High Level Design Document

## Intelligent Workforce Scheduling for Modern Businesses

---

## Executive Summary

Plannrly is a comprehensive workforce scheduling and management platform designed to simplify the complexities of staff scheduling for businesses of all sizes. Whether you manage a single location or multiple sites across different regions, Plannrly provides the tools you need to create, manage, and communicate employee schedules efficiently.

### The Problem We Solve

Managing employee schedules is one of the most time-consuming tasks for business owners and managers. Traditional methods using spreadsheets, paper schedules, or basic tools lead to:

- **Communication gaps** - Employees miss schedule changes or updates
- **Scheduling conflicts** - Double-bookings and understaffed shifts
- **Administrative burden** - Hours spent creating and updating schedules
- **Leave management chaos** - Paper trails and email threads for time-off requests
- **Lack of visibility** - Difficulty seeing the big picture across locations

Plannrly eliminates these pain points by providing a centralized, intuitive platform where schedules are created once and instantly accessible to everyone who needs them.

---

## Key Features

### Multi-Tenant Architecture

Every business using Plannrly operates in a completely isolated environment. Your data, schedules, and employee information are entirely separate from other organizations. This means:

- Complete data privacy and security
- Customizable settings per organization
- No risk of data crossover between businesses

### Role-Based Access Control

Plannrly recognizes that different people in your organization need different levels of access. Our five-tier permission system ensures everyone sees exactly what they need - no more, no less:

- **Super Administrators** - Platform oversight and multi-organization management
- **Business Administrators** - Full control over your organization's settings
- **Location Administrators** - Manage specific locations within your business
- **Department Administrators** - Oversee specific departments
- **Employees** - View schedules and manage personal requests

### Location and Department Management

Organize your workforce the way your business actually operates:

- Create unlimited locations to represent your physical sites
- Define departments within each location
- Assign employees to specific locations and departments
- View and manage schedules at any level of granularity

### Business Role Management

Define the roles that make sense for your business, complete with:

- Custom role names that match your terminology
- Color-coded visual identification for easy schedule reading
- Role-based shift templates for faster scheduling

### Scheduling Delegation

As your business grows, you can't do everything yourself. Plannrly allows you to delegate scheduling responsibilities to the right people at the right level:

**Delegate to Location Administrators:**
- Assign store managers or site supervisors as Location Administrators
- They gain full scheduling control for their specific location
- They can create, edit, and publish shifts for employees at their site
- They can approve leave requests and shift swaps within their location
- The Business Administrator retains oversight and can view or adjust any location's schedule

**Delegate to Department Administrators:**
- Assign team leads or department heads as Department Administrators
- They manage scheduling for their specific department only
- Perfect for specialized teams (e.g., Kitchen Manager schedules kitchen staff)
- Reduces bottlenecks by distributing scheduling workload
- Department Admins can only see and manage employees in their department

**Why Delegation Matters:**
- **Reduce Your Workload** - Stop being the bottleneck for every schedule change
- **Local Knowledge** - Let managers who know their team best create the schedules
- **Faster Response** - Leave requests and swaps are handled by the nearest manager
- **Maintain Control** - Higher-level admins can always review and override decisions
- **Scale Effortlessly** - Add locations and departments without increasing your personal workload

### Comprehensive Schedule Management

Create professional schedules quickly with our powerful scheduling tools:

- **Week View** - See an entire week at a glance across all employees
- **Day View** - Detailed view of a single day's operations
- **Drag-and-Drop Interface** - Move shifts between employees with a simple drag
- **Copy and Paste** - Duplicate shifts or entire schedule patterns

### Draft and Publish Workflow

Perfect your schedule before anyone sees it:

- Create schedules in draft mode while you work out the details
- Make adjustments without confusing employees
- Publish when ready - employees are automatically notified
- Maintain version control and schedule history

### Leave Request Management

Streamline time-off requests with a comprehensive leave management system:

**Leave Types:**
- Configure custom leave types (Annual Leave, Sick Leave, Maternity, etc.)
- Set whether each type requires approval or is auto-approved
- Define whether leave is paid or unpaid
- Assign colors for visual distinction on schedules
- Set maximum consecutive days allowed per request

**Leave Allowances:**
- Track annual entitlements per employee per leave type
- View used vs remaining days at a glance
- Automatic deduction when leave is approved
- Carry-over rules for unused leave (configurable)
- Pro-rata calculations for part-year employees

**Leave Calendar Integration:**
- Approved leave displayed on schedule week and day views
- Visual indicators show who is off on any given day
- Prevents scheduling employees who are on approved leave
- At-a-glance visibility for managers when planning shifts

**Request Workflow:**
- Employees submit requests through the platform
- Managers review and approve with one click
- Automatic calendar integration prevents scheduling conflicts
- Complete audit trail of all requests and decisions
- Email notifications for request status changes

### Shift Swap Requests

Empower employees while maintaining oversight:

- Employees can request to swap shifts with colleagues
- Managers approve or deny swap requests
- System validates that both employees are qualified for the shifts
- Automatic schedule updates upon approval

### Time & Attendance (Clock In/Out)

Track actual working hours with precision:

**Employee Clock In/Out:**
- Employees clock in when arriving for their shift
- Clock out when leaving at the end of the shift
- Simple one-tap interface on mobile or desktop
- Optional GPS location capture for verification
- Break time tracking with start/end timestamps

**Scheduled vs Actual Time Tracking:**
- Compare scheduled shift times against actual clock in/out times
- Automatic calculation of early arrivals and late departures
- Track overtime hours beyond scheduled shifts
- Identify no-shows and missed shifts automatically
- Grace period configuration for minor time variations

**Manager Oversight:**
- Real-time visibility of who is currently clocked in
- Alerts for employees who haven't clocked in for their shift
- Ability to manually adjust time entries when needed
- Approve or flag time entries that require review
- View attendance patterns and trends

**Timesheet Management:**
- Weekly and bi-weekly timesheet views
- Automatic calculation of total hours worked
- Break time deduction from worked hours
- Export timesheets for payroll processing
- Historical timesheet access for auditing

**Attendance Reporting:**
- Schedule adherence metrics (actual vs scheduled)
- Punctuality reports (early/on-time/late arrivals)
- Overtime tracking and analysis
- Absence and no-show reports
- Department and location-level attendance summaries

### Kiosk Mode

Shared clock-in stations for locations where employees don't have individual devices:

**Dedicated Terminal Interface:**
- Simplified, touch-optimized interface for shared tablets or terminals
- Large buttons and clear visuals for quick clock actions
- Works on any tablet or touchscreen device
- Automatically stays logged in as the location kiosk

**Employee Authentication:**
- PIN code entry (4-6 digit personal PIN)
- Badge/card scan integration (optional hardware)
- QR code scan from employee's phone
- Photo capture on clock-in for verification (optional)

**Clock Actions:**
- One-tap clock in after authentication
- One-tap clock out
- Start/end break buttons
- View current shift details
- See who else is clocked in at this location

**Manager Features:**
- Register and configure kiosks per location
- Set authentication methods allowed
- Enable/disable photo capture requirement
- View kiosk activity logs
- Remote lock/unlock kiosks
- Manager PIN override for corrections

**Security & Compliance:**
- Auto-logout after each clock action
- Session timeout for idle kiosks
- Audit trail of all kiosk transactions
- Photo evidence for disputed time entries
- IP/device restrictions per kiosk

### Employee Profile & Self-Service

Empower employees to manage their own information:

**Employee Self-Service:**
- Update personal contact information (phone, email, address)
- Upload and change profile photo/avatar
- Change password securely
- Set availability preferences for scheduling
- View employment details (start date, role, pay information)

**Availability Management:**
- Set recurring weekly availability (e.g., "Available Mon-Fri 9am-5pm")
- Mark specific dates as unavailable
- Indicate preferred working hours
- Set maximum hours per week preference
- Request changes to availability

### Employee HR Records (Admin)

Comprehensive employee management for administrators:

**Employment Details:**
- Employment start date
- Employment end date (for fixed-term contracts)
- Final working day / leaving date
- Employment status (active, on leave, terminated)
- Probation end date

**Compensation Management:**
- Pay type: Hourly or Salaried
- Base hourly rate or annual salary
- Role-specific hourly rates (different pay for different roles)
- Pay rate effective dates for historical tracking
- Currency setting

**Scheduling Preferences:**
- Target/planned hours per week
- Minimum hours per week
- Maximum hours per week
- Overtime eligibility
- Preferred shift patterns

**Administrative Notes:**
- Private HR notes (visible to admins only)
- Performance notes
- Training records
- Document attachments

### Shift Preferences

Beyond basic availability, capture employee preferences to improve satisfaction:

**Preference Types:**
- **Preferred Times** - Morning person vs night owl, weighted scoring
- **Preferred Days** - Love Saturdays, avoid Mondays
- **Avoid If Possible** - Soft constraints vs hard unavailability
- **Preferred Coworkers** - Work better with certain colleagues
- **Preferred Locations** - For multi-location employees

**Preference Weighting:**
- Strong preference (prioritize highly)
- Mild preference (consider when possible)
- Neutral (no preference)
- Avoid if possible (soft constraint)
- Absolutely cannot (hard constraint - same as unavailability)

**Manager Visibility:**
- View employee preferences when scheduling
- Preference satisfaction score per schedule
- Suggestions to improve preference matching
- Override capability with reason logging

### Calendar Integration

Sync schedules with personal calendars for seamless life management:

**Export Options:**
- Personal iCal feed URL (subscribe from any calendar app)
- Google Calendar direct integration
- Microsoft Outlook / Office 365 integration
- Apple Calendar support via iCal

**What's Synced:**
- Scheduled shifts with start/end times
- Shift location and role details
- Break times (optional)
- Approved leave requests
- Shift changes update automatically

**Two-Way Sync (Optional):**
- Import personal calendar events as unavailability
- Block scheduling during personal commitments
- Privacy controls (only blocks time, doesn't share event details)

**Feed Security:**
- Unique, unguessable URL per employee
- Regenerate URL if compromised
- Optional authentication for enterprise

### Open Shift Marketplace

Empower employees to pick up additional shifts:

**For Employees:**
- View all unassigned shifts they're qualified for
- Filter by date, location, department, role
- One-click request to claim a shift
- See estimated hours impact on weekly total
- Notification when new open shifts are posted

**Claiming Process:**
- Employee requests to claim open shift
- Manager approval required (configurable)
- Auto-approve option for trusted employees
- First-come-first-served or bidding mode

**Priority Rules (Configurable):**
- Seniority-based priority
- Hours worked this period (favor those under target)
- Performance score
- Certification level
- Random selection (for fairness)

**Deadlines:**
- Auto-assign after deadline if only one applicant
- Escalation if shift remains unfilled
- Notification to managers for critical unfilled shifts

### Schedule Templates

Save time by reusing proven schedule patterns:

**Template Creation:**
- Save current week's schedule as a template
- Name and describe templates
- Assign to location, department, or role
- Mark as seasonal (summer, holiday, etc.)

**Template Library:**
- Browse templates by category
- Preview before applying
- Share templates across locations
- Import/export templates

**Applying Templates:**
- Select template and target week
- Smart employee matching (by role, not specific person)
- Conflict detection before applying
- Adjust after applying as needed

**Template Types:**
- Full week schedule
- Single day pattern
- Shift-only (times without assignments)
- Role-based (all waiters work these hours)

### Recurring Shifts

Create shifts that automatically repeat on a schedule:

**Frequency Options:**
- Daily (every day, every 2 days, etc.)
- Weekly (select specific days: Mon/Wed/Fri)
- Monthly (same date each month)

**End Conditions:**
- Never (auto-extends 12 weeks ahead)
- On specific date
- After N occurrences

**Edit Scope:**
- "This shift only" - Detaches from series
- "This and all future shifts" - Bulk update

**Delete Scope:**
- "Delete this shift only" - Single removal
- "Delete this and all future shifts" - Series deletion

**Visual Indicators:**
- Recurring icon on shift blocks
- Parent/child relationship tracked in database

**Automatic Extension:**
- Daily command extends "never-ending" series
- Maintains 12-week generation window

### Smart Fill (Auto-Scheduling Assistant)

One-click assistance to fill schedule gaps:

**How It Works:**
1. Manager creates some shifts or leaves gaps
2. Click "Smart Fill" button
3. System suggests employees for unassigned shifts
4. Manager reviews and adjusts
5. Publish when satisfied

**Smart Fill Logic:**
- Respect employee availability
- Balance hours toward target
- Consider recent shifts (fairness)
- Check qualifications/certifications
- Avoid overtime where possible
- Consider preferences (if data available)

**Manager Controls:**
- Fill all gaps or selected shifts only
- Exclude specific employees
- Prioritize certain employees
- Set maximum hours cap
- Preview before accepting

**Transparency:**
- Show why each suggestion was made
- Highlight any concerns (overtime, preferences)
- Alternative suggestions available

### Real-Time Operations Dashboard

Live visibility into current workforce status:

**Who's Working Now:**
- List of currently clocked-in employees
- Their role, location, and shift details
- Time since clock-in
- Expected clock-out time

**Expected Arrivals:**
- Employees scheduled to arrive in next 1-2 hours
- Countdown to shift start
- Quick contact options (call, message)

**Missing & Late:**
- Employees who should be clocked in but aren't
- Minutes late indicator
- One-click "Send Reminder" action
- Mark as "Notified" to track follow-up

**Location View:**
- Map view of all locations (for multi-location)
- Staff count per location
- Coverage status (under/adequately/over staffed)
- Drill down to location details

**Quick Actions:**
- Call or message employee
- Adjust shift (extend, shorten)
- Find replacement for no-show
- Add unscheduled shift

### Shift Notes & Handover

Enable seamless communication between shifts:

**Shift Instructions (Manager → Employee):**
- Manager adds notes when creating shift
- Special instructions for the day
- Task checklists to complete
- Important reminders

**Shift Notes (Employee → Next Shift):**
- Employee adds notes during or after shift
- What happened, what needs attention
- Visible to next shift employee
- Visible to managers

**Handover Workflow:**
- Previous shift employee completes handover notes
- Next shift employee acknowledges receipt
- Manager can require handover for certain shifts
- Historical handover notes searchable

**Task Checklists:**
- Define standard tasks per shift type
- Employee checks off completed tasks
- Incomplete tasks flagged for next shift
- Completion rate tracking

### Conflict Detection & Warnings

Proactive alerts when scheduling creates issues:

**Conflict Types Detected:**
- **Leave Conflicts** - Scheduling during approved leave
- **Availability Conflicts** - Outside employee's availability
- **Rest Period Violations** - Insufficient rest between shifts
- **Overtime Warnings** - Approaching or exceeding limits
- **Qualification Gaps** - Missing required certifications
- **Maximum Hours** - Exceeding weekly/daily limits

**Warning Levels:**
- 🔴 **Error** - Cannot save (e.g., double-booking same time)
- 🟠 **Warning** - Can save but needs attention
- 🟡 **Info** - Suggestion for improvement

**Resolution Assistance:**
- Explain what the conflict is
- Suggest alternative employees
- Suggest alternative times
- "Fix All" button for batch resolution

**Manager Override:**
- Override warnings with reason
- Logged in audit trail
- Report on override frequency

### Working Time Compliance

Monitor and warn about working time regulation violations:

**EU Working Time Directive (2003/88/EC) Monitoring:**

| Rule | Monitoring | Action |
|------|------------|--------|
| 11 hours rest between shifts | ✅ Tracked | Warning when violated |
| 48 hours max/week (average over 17 weeks) | ✅ Tracked | Warning when exceeded |
| 24 hours rest per 7 days | ✅ Tracked | Warning when violated |
| 20 minute break after 6 hours | ✅ Tracked | Warning if not scheduled |

**Compliance Approach:**
- **Warn, Don't Block** - Managers receive warnings but can proceed
- **Acknowledge Required** - Must acknowledge violation before saving
- **Reason Logging** - Record why override was necessary
- **Full Audit Trail** - All violations logged for reporting

**Admin Dashboard Compliance Report:**
- Summary of violations this period
- Trend over time (improving or worsening)
- Violations by location/department
- Employees most affected
- Manager override patterns

**Configurable Rules:**
- Enable/disable specific rule checks
- Adjust thresholds (e.g., 10 hours rest instead of 11)
- Set warning vs blocking behavior
- Jurisdiction presets (EU, UK, custom)

### Schedule Fairness Analytics

Ensure equitable treatment across all employees:

**Fairness Metrics:**
- **Weekend Distribution** - Who works most/least weekends
- **Holiday Distribution** - Fair rotation of holiday shifts
- **Preferred Shift Access** - Morning vs evening shift allocation
- **Hours Variance** - Deviation from target hours
- **Short-Notice Changes** - Who gets changed most often

**Fairness Score:**
- Per-employee fairness index (0-100)
- Team-wide fairness score
- Trend over time
- Comparison to team average

**Visibility:**
- Managers see fairness dashboard
- Employees see their own metrics
- Anonymous team comparisons
- Suggestions for improvement

**Alerts:**
- Warning when scheduling creates unfairness
- Identify employees consistently disadvantaged
- Recommendations to rebalance

### Predictive Absence Analytics

Identify absence patterns and predict potential issues:

**Pattern Detection:**
- Monday/Friday absence clustering
- Pre/post holiday patterns
- Correlation with schedule types (night shifts, weekends)
- Seasonal patterns
- Weather correlation (optional)

**Risk Indicators:**
- Employees with increasing absence trend
- Unusual absence patterns flagged
- Comparison to historical baseline
- Team absence rates

**Manager Insights:**
- Early warning for potential issues
- Suggested interventions (schedule adjustment, conversation)
- Historical pattern visualization
- Anonymized for privacy where appropriate

**Reporting:**
- Absence trend reports
- Department comparisons
- Cost impact analysis
- Patterns by day, month, season

### AI-Powered Scheduling *(Premium Add-On)*

> **Note:** AI-Powered Scheduling is available as a premium subscription add-on. Contact us to enable this feature for your organization.

Let artificial intelligence help create optimal schedules:

**Smart Schedule Generation:**
- Automatically generate schedules based on business needs
- Consider employee availability and preferences
- Respect target hours per employee
- Balance workload fairly across team members
- Fill shifts based on role qualifications

**Scheduling Constraints:**
The AI respects all configured constraints:
- Employee availability windows
- Minimum/maximum hours per week per employee
- Required rest periods between shifts
- Role qualifications (only assign qualified employees)
- Leave requests and time-off
- Employee preferences and seniority
- Staffing requirements (min/max employees per role per time slot)

**AI Scheduling Workflow:**
1. **Define Requirements** - Set how many staff needed per role, per shift
2. **Review Suggestions** - AI proposes a draft schedule
3. **Adjust as Needed** - Manager can modify AI suggestions
4. **Publish** - Finalize and notify employees

**Optimization Goals:**
- Meet staffing requirements for all shifts
- Distribute hours fairly based on target hours
- Minimize overtime costs
- Maximize employee preference satisfaction
- Reduce scheduling conflicts

**AI Scheduling Features:**
- One-click schedule generation for a week
- Fill unassigned shifts automatically
- Suggest replacements for call-outs
- Identify understaffed periods
- Warn about potential compliance issues

### Staffing Requirements

Define minimum and maximum staffing levels for each role throughout the day:

**Coverage Rules:**
- Set rules per location, department, or business role
- Define time windows with staffing requirements
- Specify minimum employees needed (understaffed warning)
- Specify maximum employees allowed (overstaffed warning)
- Configure rules by day of week (e.g., more staff on weekends)

**Example Configuration:**
| Day | Time Window | Role | Minimum | Maximum |
|-----|-------------|------|---------|---------|
| Monday | 09:00 - 10:00 | Waiter | 1 | 2 |
| Monday | 10:00 - 14:00 | Waiter | 2 | 4 |
| Monday | 14:00 - 17:00 | Waiter | 1 | 3 |
| Saturday | 11:00 - 15:00 | Waiter | 4 | 6 |
| Saturday | 11:00 - 15:00 | Chef | 2 | 3 |

**Schedule View Warnings:**
- Visual indicators when shifts don't meet staffing requirements
- Understaffed periods highlighted in red/orange
- Overstaffed periods highlighted in yellow
- Hover to see details (current count vs required)
- Summary widget showing coverage gaps for the day/week

**Integration with AI Scheduling:**
- Staffing requirements feed directly into AI schedule generation
- AI prioritizes filling understaffed periods
- AI avoids creating overstaffed periods
- Coverage analysis in schedule recommendations

### Labor Cost Budgeting

Control labor costs with budget planning and real-time tracking:

**Budget Configuration:**
- Set weekly or monthly labor budgets per location
- Set budgets per department within a location
- Define budget periods (weekly, bi-weekly, monthly)
- Configure budget amounts in currency
- Set budget thresholds for warnings (e.g., 80%, 90%, 100%)

**Real-Time Cost Tracking:**
- View scheduled labor cost as shifts are created
- Compare scheduled cost vs budget in real-time
- Track actual labor cost from time entries
- See cost breakdown by department and role
- Monitor budget consumption percentage

**Schedule View Integration:**
- Budget indicator on weekly schedule view
- Color-coded warnings (green/yellow/red) based on budget status
- Tooltip showing: Budget, Scheduled, Remaining
- Warning when adding shifts would exceed budget
- Overtime cost projections included

**Cost Calculations:**
- Uses employee hourly rates from HR records
- Accounts for role-specific pay rates
- Includes overtime multipliers where applicable
- Factors in scheduled break deductions
- Supports different currencies per location

**Budget Reports:**
- Weekly/monthly budget vs actual reports
- Variance analysis (over/under budget)
- Cost trends over time
- Department and location comparisons
- Export for financial systems

**Manager Alerts:**
- Notification when budget reaches threshold (e.g., 80%)
- Alert when schedule exceeds budget
- Weekly budget summary emails
- Escalation to admins for over-budget locations

### Real-Time Notifications

Keep everyone informed automatically:

- Schedule publication alerts
- Shift change notifications
- Leave request status updates
- Swap request notifications
- Clock in/out reminders
- Missed shift alerts
- Customizable notification preferences

### Advanced Geofencing *(Premium)*

Ensure employees clock in from the right location:

- Define virtual boundaries around each work location
- Configurable radius from 100 meters to 1 kilometer
- Automatic clock-in when entering geofence (optional)
- Automatic clock-out when leaving geofence (optional)
- Block clock-in attempts from outside the geofence
- GPS trail for mobile and field workers
- Manager alerts for geofence violations

### Labor Demand Forecasting *(Premium)*

Predict your staffing needs before creating schedules:

- AI analyzes historical scheduling patterns and business activity
- Factors in seasonality, day of week, and special events
- Generates recommended staffing levels per role per hour
- Integrates with AI Scheduling for optimal schedule generation
- Continuously improves predictions based on actual outcomes
- Reduces over-staffing costs and under-staffing issues

### Payroll Integrations *(Premium)*

Seamlessly connect scheduling and time tracking to payroll:

- Export approved timesheets directly to payroll systems
- Supported providers: ADP, Paychex, Gusto, QuickBooks, Xero
- Automatic calculation of regular, overtime, and premium hours
- Sync employee data between systems
- Eliminate manual data entry and reduce errors

### Team Messaging & Announcements *(Premium)*

Keep your team connected and informed:

- Post announcements to entire organization, locations, or departments
- Direct messaging between managers and employees
- Require acknowledgment for important announcements
- Track who has read each message
- Shift handover notes for seamless transitions
- Searchable message history

### Document & Certification Management *(Premium)*

Manage employee documents and track certifications:

- Secure storage for employee documents (contracts, IDs, etc.)
- Track certifications and licenses with expiry dates
- Automatic email alerts before certifications expire
- Define required certifications per role
- Employees can upload documents via self-service
- Access controls based on user role

### Multi-Location Analytics *(Enterprise)*

Gain insights across your entire organization:

- Compare performance metrics across all locations
- Consolidated labor cost reporting
- Identify best-performing locations and practices
- Centralized compliance and attendance monitoring
- Executive dashboards for leadership

### Organization Settings

Business Administrators can configure organization-wide settings to customize Plannrly for their business:

**Scheduling Settings:**
- Week start day (Sunday, Monday, etc.)
- Default shift duration
- Minimum time between shifts
- Maximum hours per week warning threshold

**Time & Attendance Settings:**
- Enable/disable clock in/out features
- Clock-in grace period (minutes before/after shift start)
- Missed shift grace period
- Require GPS location on clock-in
- Auto clock-out time
- Require manager approval for time entries

**Display Settings:**
- Date format preference
- Time format (12-hour or 24-hour)
- Timezone selection
- Currency for cost calculations

**Leave Management Settings:**
- Leave year start date
- Require approval for all leave types
- Allow leave requests for past dates
- Default notice period for leave requests

---

## User Roles Explained

### Super Administrator

The Super Administrator has complete oversight of the Plannrly platform. This role is typically reserved for:

- Business owners managing multiple organizations
- IT administrators responsible for platform management
- Corporate oversight personnel

**Capabilities:**
- Access all organizations within the platform
- Create and manage organizations
- View platform-wide analytics
- Manage billing and subscriptions

### Business Administrator

The primary administrator for a single organization. Ideal for:

- Business owners
- Operations managers
- HR directors

**Capabilities:**
- Full control over organization settings
- Create and manage all locations and departments
- Add, edit, and remove employees
- Create and publish schedules for any location
- Approve all leave and swap requests
- Access all reports and analytics

### Location Administrator

Manages operations at a specific physical location. When a Business Administrator delegates scheduling to a Location Administrator, that manager gains autonomy over their site while the business owner maintains oversight. Perfect for:

- Store managers
- Site supervisors
- Branch managers

**Capabilities:**
- Full scheduling control for their assigned location
- Create, edit, and publish schedules without waiting for head office
- Manage employees at their assigned location
- Approve leave requests for location employees
- Handle shift swaps within their location
- View location-specific reports

**Delegation Benefits:**
- Respond quickly to local staffing needs
- Make scheduling decisions based on firsthand team knowledge
- Free up Business Administrators for strategic work

### Department Administrator

Oversees a specific department within a location. This role allows scheduling responsibilities to be distributed even further, giving team leads control over their own area. Suited for:

- Department heads
- Team leads
- Shift supervisors

**Capabilities:**
- Manage employees in their department
- Create schedules for department staff
- Approve department-specific leave requests and swaps
- View department reports

**Delegation Benefits:**
- Specialized managers schedule their specialized teams
- Reduces complexity for Location Administrators managing multiple departments
- Employees get faster responses from their direct supervisor

### Employee

The front-line users who need to see their schedules and manage personal requests:

**Capabilities:**
- View personal schedule
- Clock in and out of shifts
- Track break times
- View personal timesheet and hours worked
- Submit leave requests
- Request shift swaps with colleagues
- Update personal profile (contact information, avatar)
- Set and update personal availability
- Receive notifications about schedule changes

---

## Workflow Overview

### Onboarding Wizard

New users are guided through setup with an interactive wizard:

**Welcome & Account Setup:**
- Welcome message explaining what Plannrly does
- Business name and basic details
- Timezone and locale settings
- Industry selection (helps customize experience)

**Step-by-Step Progress:**
- Visual progress indicator (Step 2 of 6)
- Skip option for non-essential steps
- Save progress and continue later
- Estimated time to complete each step

**Setup Steps:**
1. **Organization Details** - Name, address, industry
2. **First Location** - Create your primary location
3. **Departments** - Define departments (or skip for simple businesses)
4. **Business Roles** - Create roles with colors
5. **Invite Team** - Add first employees
6. **First Schedule** - Create a sample schedule

**Sample Data Option:**
- Populate with demo data to explore features
- See how a working schedule looks
- Try features before adding real data
- Clear demo data with one click

**Contextual Help:**
- Video tutorials at each step (optional)
- "Why this matters" explanations
- Best practice tips
- Link to full documentation

**Completion:**
- Celebration message on completion
- Checklist of recommended next steps
- Quick links to common actions
- Option to schedule a demo call

**Persistent Checklist:**
- Setup checklist remains until dismissed
- Shows uncompleted recommended actions
- Links directly to each action
- Progress percentage displayed

### Data Import & Migration

Easily migrate from other scheduling systems:

**Import Wizard:**
- Guided import process
- File upload (CSV, Excel)
- Field mapping interface
- Preview before import
- Validation with error reporting

**Competitor Data Import:**
- Pre-built importers for popular systems:
  - Deputy
  - When I Work
  - 7shifts
  - Homebase
  - Sling
- Automatic field mapping
- Historical data preservation (where possible)

**What Can Be Imported:**
- Employee list with details
- Locations and departments
- Roles and qualifications
- Historical schedules (optional)
- Leave balances

**Import Validation:**
- Duplicate detection
- Required field validation
- Format validation (emails, phones)
- Error report with line numbers
- Partial import option (skip errors)

**Professional Migration Service:**
- Hands-on migration assistance
- Data cleanup and transformation
- Parallel running support
- Verification and sign-off

### Phase 1: Initial Setup

Getting started with Plannrly is straightforward:

1. **Create Your Organization**
   - Enter your business name and details
   - Configure your time zone and working hours
   - Set up notification preferences

2. **Define Your Locations**
   - Add each physical location where you operate
   - Set location-specific details (address, operating hours)

3. **Create Departments**
   - Define departments within each location
   - Example: Kitchen, Front of House, Management

4. **Set Up Business Roles**
   - Create roles that match your business
   - Assign colors for visual scheduling
   - Example: Server (blue), Host (green), Manager (purple)

5. **Add Employees**
   - Import or manually add your team
   - Assign them to locations and departments
   - Set their permission levels
   - Define their roles and availability

### Phase 2: Creating Schedules

Once your organization is set up, creating schedules becomes effortless:

1. **Start a New Schedule**
   - Select the week and location
   - Choose to start fresh or copy from a previous week

2. **Add Shifts**
   - Click to add shifts to the schedule grid
   - Drag to adjust start and end times
   - Assign employees to shifts based on their roles

3. **Review and Adjust**
   - Use the draft mode to perfect your schedule
   - Check for conflicts or gaps in coverage
   - Make adjustments using drag-and-drop

4. **Publish**
   - When satisfied, publish the schedule
   - Employees receive instant notifications
   - Schedule becomes visible to all relevant staff

### Phase 3: Ongoing Management

Day-to-day operations are simplified:

1. **Handle Requests**
   - Review incoming leave requests
   - Approve or deny with one click
   - System automatically updates the schedule

2. **Manage Swaps**
   - Employees initiate swap requests
   - Review and approve valid swaps
   - Schedule updates automatically

3. **Make Adjustments**
   - Update schedules as needed
   - Employees are notified of changes
   - Maintain a clear audit trail

### Phase 4: Employee Self-Service

Employees can help themselves:

- View their upcoming shifts anytime
- Submit leave requests in advance
- Find colleagues to swap shifts
- Update their availability preferences
- Receive timely notifications

### Phase 5: Time & Attendance Tracking

Track actual working hours:

1. **Clock In/Out**
   - Employees clock in when starting their shift
   - System records actual start time
   - Optional location verification via GPS
   - Clock out when shift ends

2. **Break Management**
   - Start break when taking a break
   - End break when returning to work
   - Automatic break time calculation
   - Compare against scheduled break duration

3. **Timesheet Review**
   - Employees view their own timesheets
   - Managers review and approve time entries
   - Flag discrepancies for investigation
   - Export for payroll processing

4. **Variance Analysis**
   - Compare scheduled hours vs actual hours worked
   - Identify patterns of early/late arrivals
   - Track overtime automatically
   - Generate attendance reports

---

## Security and Data Isolation

### Your Data is Protected

Plannrly takes security seriously. Here's how we protect your information:

**Complete Data Isolation**
- Each organization's data is stored separately
- No possibility of accidental data sharing between businesses
- Individual database schemas per tenant

**Access Control**
- Role-based permissions limit data access
- Users only see information relevant to their role
- Administrative actions are logged and auditable

**Secure Communication**
- All data transmitted over encrypted connections (HTTPS)
- Password protection with secure hashing
- Session management and automatic timeouts

**Data Backup**
- Regular automated backups
- Point-in-time recovery capability
- Disaster recovery procedures in place

### Two-Factor Authentication (2FA)

Add an extra layer of security to user accounts:

**Authentication Methods:**
- TOTP (Time-based One-Time Password) - Works with Google Authenticator, Authy, etc.
- SMS backup codes (with security advisory about SIM-swap risks)
- Recovery codes for account recovery

**Configuration Options:**
- Business Administrators can enable/disable 2FA for their organization
- Optional or mandatory per role (e.g., require for admins, optional for employees)
- Remember trusted devices for 30 days
- Grace period for setup after enabling

**User Experience:**
- Simple setup wizard with QR code
- Clear instructions for authenticator apps
- Backup codes generated during setup
- Easy recovery process if device is lost

### Single Sign-On (SSO) *(Premium Add-On)*

Enterprise-grade authentication for larger organizations:

**Supported Providers:**
- Google Workspace
- Microsoft Entra ID (Azure AD)
- Okta
- Generic SAML 2.0

**Features:**
- One-click login from identity provider
- Automatic user provisioning (SCIM)
- Role mapping from identity provider groups
- Just-in-time user creation
- Enforced SSO (disable password login)

**Benefits:**
- Centralized access management
- Reduced password fatigue
- Instant deprovisioning when employees leave
- Compliance with enterprise security policies

### Session Management

Give users control over their account security:

**Active Session Visibility:**
- View all currently active sessions
- See device type, browser, and location
- Last activity timestamp for each session
- Current session highlighted

**Session Controls:**
- Remote logout from any session
- "Log out all other sessions" option
- Automatic session expiry (configurable timeout)
- Force re-authentication for sensitive actions

**Security Alerts:**
- Email notification on new device login
- Alert for login from new location
- Suspicious activity warnings
- Failed login attempt notifications

### Audit Log

Complete visibility into system activity for compliance and security:

**What's Logged:**
- User authentication events (login, logout, failed attempts)
- Schedule changes (create, update, delete, publish)
- Employee data modifications
- Leave request actions
- Shift swap approvals/rejections
- Settings changes
- Role and permission changes
- Data exports and deletions

**Audit Log Features:**
- Searchable and filterable interface
- Filter by user, action type, date range, entity
- Export to CSV/JSON for compliance
- Configurable retention period per tenant
- Immutable log entries (cannot be deleted)

**Access Control:**
- Business Administrators can view all logs
- Location/Department Admins see logs for their scope
- Employees can view their own activity

### GDPR & Data Privacy Compliance

Full compliance with EU data protection regulations:

**Right to Access (Article 15):**
- Employees can request a copy of all their personal data
- One-click data export in machine-readable format (JSON/CSV)
- Includes: profile, schedules, time entries, leave history, communications
- Delivered within 30 days as required by law

**Right to Erasure (Article 17):**
- Employees can request deletion of their personal data
- Workflow for processing deletion requests
- Manager approval required (to handle employment law retention)
- Anonymization option for historical data needed for reporting
- Audit trail of deletion requests and actions

**Right to Portability (Article 20):**
- Export personal data in standard formats
- Transfer data to another service provider
- Structured, commonly used format (JSON, CSV)

**Data Retention:**
- Configurable retention policies per data type
- Automatic deletion after retention period
- Legal hold capability for disputes/litigation
- Clear visibility of what data is retained and why

**Consent Management:**
- Track consent for optional data processing
- GPS location tracking consent
- Marketing communications consent
- Third-party integration consent
- Easy withdrawal of consent

**Privacy Settings:**
- Tenant-level privacy configuration
- Data Processing Agreement (DPA) acceptance
- Sub-processor list visibility
- Privacy policy acknowledgment tracking

---

## Subscription & Pricing

### Base Subscription

Every Plannrly subscription includes all core features:

- Multi-tenant organization setup
- Unlimited locations and departments
- Role-based access control (all 5 tiers)
- Schedule management (week/day views, draft/publish)
- Leave request management
- Shift swap requests
- Staffing requirements with coverage warnings
- Time & attendance tracking (clock in/out)
- Employee self-service profile
- HR records management
- Basic reports and analytics
- Email notifications

### Premium Add-Ons

Enhance your Plannrly experience with optional premium features:

**AI-Powered Scheduling** *(Add-On)*
- Automatic schedule generation based on requirements
- Smart shift assignment considering availability and target hours
- One-click fill for unassigned shifts
- Replacement suggestions for call-outs
- Schedule analysis and optimization recommendations

**Advanced Analytics & Reports** *(Add-On)*
- Custom report builder with drag-and-drop
- Labor cost analysis and trends
- Schedule efficiency metrics
- Exportable dashboards (PDF, CSV)
- Scheduled report delivery via email

**Advanced Geofencing** *(Add-On)*
- Configurable geofence radius per location (100m - 1km)
- Automatic clock-in/out when entering/leaving geofence
- Location verification enforcement
- GPS trail tracking for mobile workers
- Geofence violation alerts for managers

**Labor Demand Forecasting** *(Add-On)*
- AI-predicted staffing needs based on historical patterns
- Factor in seasonality, holidays, and events
- Suggested optimal staff levels per time slot
- Integration with scheduling for proactive planning
- Accuracy tracking and model improvement

**Payroll Integrations** *(Add-On)*
- Direct export to major payroll providers
- Supported: ADP, Paychex, Gusto, QuickBooks, Xero
- Automatic timesheet sync
- Wage and hour calculations
- Overtime and premium pay exports

**Team Messaging & Announcements** *(Add-On)*
- Company-wide and location/department announcements
- Direct messaging between staff and managers
- Read receipts and acknowledgment tracking
- Shift-specific notes and handover messages
- Message history and search

**Document & Certification Management** *(Add-On)*
- Upload and store employee documents
- Track certifications with expiry dates
- Automatic alerts before certification expires
- Required training checklist per role
- Secure document access controls

**Single Sign-On (SSO)** *(Add-On)*
- Google Workspace integration
- Microsoft Entra ID (Azure AD)
- Okta
- Generic SAML 2.0 support
- Automatic user provisioning (SCIM)

### Freemium Tier

A limited free tier to let small businesses get started:

**Free Plan Includes:**
- 1 location
- Up to 5 active employees
- Basic scheduling (week view)
- Leave request management
- Employee self-service
- Plannrly branding on communications
- Community support only

**Free Plan Limitations:**
- No time & attendance
- No reports or analytics
- No API access
- No premium add-ons
- Limited notification channels

**Upgrade Path:**
- Seamless upgrade to paid plans
- No data loss when upgrading
- Prorated billing from upgrade date

### Pricing Model

**Per-Employee Pricing:**
- Base fee per location per month
- Additional fee per active employee per month
- "Active" = scheduled or clocked in during billing period
- Volume discounts for larger organizations

**Example Pricing Structure:**
| Tier | Base Fee | Per Employee | Includes |
|------|----------|--------------|----------|
| Starter | €29/mo | €2/employee | Core features |
| Professional | €79/mo | €4/employee | + Analytics, API |
| Enterprise | Custom | Custom | + SSO, Priority Support |

**Add-On Pricing:**
- Premium features priced separately
- Can be added to any tier
- Monthly or annual billing
- Bundle discounts available

### Implementation & Professional Services

Support for organizations needing hands-on assistance:

**Onboarding Packages:**
- Guided account setup
- Data migration assistance
- Initial schedule configuration
- Admin training sessions

**Training Services:**
- Live virtual training for managers
- Recorded training library access
- Custom training for enterprise
- Train-the-trainer programs

**Migration Services:**
- Data import from existing systems
- Competitor data migration (Deputy, When I Work, 7shifts, etc.)
- Historical data preservation
- Parallel running support

**Custom Development:**
- Custom integrations
- Workflow customizations
- API implementation support
- Dedicated technical resources

### Partner & Reseller Program

Grow with partners who serve your target customers:

**Partner Types:**
- **Referral Partners** - Earn commission for referrals
- **Resellers** - Sell and support Plannrly directly
- **Integration Partners** - Build complementary products
- **Consulting Partners** - HR consultants, accountants

**Partner Benefits:**
- Commission on referred revenue
- Partner portal for lead tracking
- Co-marketing opportunities
- Early access to new features
- Partner certification program

**Reseller Features:**
- Multi-tenant management console
- Bulk tenant provisioning
- White-label options (Enterprise)
- Consolidated billing
- Partner support channel

### Enterprise Features

For larger organizations with advanced needs:

**Multi-Location Analytics Dashboard**
- Cross-location performance comparison
- Consolidated labor cost reporting
- Benchmark locations against each other
- Centralized compliance monitoring
- Executive summary dashboards

**Custom Branding / White Label**
- Custom logo and brand colors
- Branded email notifications
- Custom login page
- Remove Plannrly branding
- Custom domain support (optional)

**API Access**
- RESTful API for custom integrations
- Comprehensive developer documentation
- API sandbox for testing
- SDKs for popular languages

**Webhook System**
Real-time notifications for integration with external systems:

*Supported Events:*
- `shift.created`, `shift.updated`, `shift.deleted`
- `shift.published` (batch event)
- `employee.clocked_in`, `employee.clocked_out`
- `leave.requested`, `leave.approved`, `leave.rejected`
- `swap.requested`, `swap.approved`, `swap.rejected`
- `schedule.published`
- `employee.created`, `employee.updated`

*Webhook Features:*
- Configurable endpoint URLs per event type
- Secret key for payload verification (HMAC)
- Retry logic with exponential backoff
- Webhook delivery logs with response status
- Test webhook button for debugging

**API Rate Limiting**
Fair usage policies with visibility:

*Rate Limits by Plan:*
| Plan | Requests/Minute | Requests/Day |
|------|-----------------|--------------|
| Starter | 60 | 10,000 |
| Professional | 300 | 100,000 |
| Enterprise | 1,000 | Unlimited |

*Rate Limit Visibility:*
- Headers in every response (`X-RateLimit-Remaining`, `X-RateLimit-Reset`)
- API usage dashboard in settings
- Email alerts when approaching limits
- Burst allowance for occasional spikes

**Priority Support**
- Dedicated account manager
- Priority ticket handling
- Phone support
- Custom onboarding and training

### Subscription Management

Administrators can manage their subscription from the Settings area:

- View current subscription plan and features
- See enabled add-ons
- View billing history
- Upgrade or add premium features
- Manage payment methods

### Payment Processing (Stripe)

Secure, reliable payment processing powered by Stripe:

**For Customers:**
- Credit/debit card payments
- Automatic recurring billing
- Invoice history and PDF downloads
- Update payment method anytime
- Cancel or pause subscription
- Proration for mid-cycle upgrades

**Payment Features:**
- PCI-compliant payment handling (Stripe manages card data)
- Support for major credit cards (Visa, Mastercard, Amex, Discover)
- Automatic retry for failed payments
- Dunning emails for expiring cards
- Tax calculation and invoicing
- Multiple currency support

**Subscription Lifecycle:**
- Free trial period (configurable)
- Automatic conversion to paid after trial
- Grace period for failed payments
- Immediate access on successful payment
- Prorated refunds on downgrade (optional)

### Invoicing & Billing Compliance

Professional invoicing with regulatory compliance:

**Platform Company Details (Invoice Issuer):**
```
Company Name: Checketts Propiedad SL
Tax Number: ESB42691550
Address: Calle Francisco Salzillo 9
         Orihuela Costa, Alicante, 03189
         Spain
```

These details appear on all invoices issued by the platform and are configurable via environment variables or platform settings.

**Invoice Numbering:**
- Sequential invoice numbers per calendar year
- Format: YYYY-NNN (e.g., 2026-001, 2026-018, 2026-247)
- Automatic increment for each invoice generated
- Numbers never reused or skipped
- Separate sequences for different invoice types (optional)

**Invoice Types:**
- Subscription invoices (auto-generated from Stripe)
- Ad-hoc invoices (manually created)
- Credit notes / refund invoices
- Proforma invoices (quotes)

**Ad-Hoc Invoices:**
Create manual invoices for services outside the subscription:
- Custom consulting or implementation services
- Training and onboarding fees
- Custom development work
- One-time setup fees
- Hardware or equipment sales
- Support packages

**Invoice Details:**
- Full legal business name
- Tax identification numbers (VAT, GST, ABN, etc.)
- Billing address
- Invoice date and due date
- Line items with descriptions
- Tax calculations (configurable rates)
- Payment terms and instructions
- Bank account details for wire transfers

**PDF Invoice Generation:**
- Professional, branded invoice template
- Company logo and colors
- All required tax information
- Digital signature (optional)
- QR code for payment (optional)

### Tenant Billing Information

Comprehensive financial details for each tenant:

**Company Information:**
- Legal company name
- Trading name (if different)
- Company registration number
- Date of incorporation

**Tax Information:**
- Tax identification number (TIN/EIN)
- VAT/GST registration number
- Tax-exempt status (with certificate upload)
- Tax region/jurisdiction

**Billing Address:**
- Street address (line 1 and 2)
- City
- State/Province/Region
- Postal/ZIP code
- Country

**Billing Contact:**
- Billing contact name
- Billing email address
- Billing phone number
- Accounts payable email (for invoice delivery)

**Payment Preferences:**
- Preferred currency
- Payment terms (Net 7, Net 14, Net 30, etc.)
- Purchase order required (yes/no)
- Default PO number
    
**Bank Details (for refunds):**
- Bank name
- Account name
- Account number / IBAN
- Sort code / SWIFT / BIC

### EU Intra-Community VAT Compliance

As Checketts Propiedad SL is based in Spain, the platform must comply with EU VAT regulations for cross-border B2B transactions.

**VAT Scenarios:**

| Customer Type | Location | VAT Treatment |
|---------------|----------|---------------|
| Business (B2B) | Spain | 21% IVA (Spanish VAT) |
| Business (B2B) | Other EU country | 0% (Reverse Charge) |
| Business (B2B) | Non-EU | 0% (Export - outside scope) |
| Consumer (B2C) | Spain | 21% IVA |
| Consumer (B2C) | Other EU | 21% IVA (or OSS if applicable) |
| Consumer (B2C) | Non-EU | 0% (Export - outside scope) |

**VIES VAT Number Validation:**
- Real-time validation against EU VIES database
- Validation required before applying 0% reverse charge
- Store validation timestamp and reference
- Re-validate periodically (quarterly recommended)
- Flag invalid/expired VAT numbers for review

**Reverse Charge Requirements (B2B Intra-EU):**
When supplying services to VAT-registered businesses in other EU countries:
- Supplier (Plannrly) charges 0% VAT
- Customer accounts for VAT in their country (reverse charge)
- Invoice must include specific text:
  - "Reverse charge - Article 196 Council Directive 2006/112/EC"
  - "The Client is responsible for tax payment to their local tax authority"
- Both VAT numbers must appear on invoice (supplier and customer)

**Invoice Requirements for EU Transactions:**
- Supplier VAT number: ESB42691550
- Customer VAT number (validated via VIES)
- Customer's full business name and address
- Clear indication of reverse charge where applicable
- Invoice date and sequential number
- Description of services supplied
- Net amount and VAT amount (or reverse charge notice)

**Tax Determination Logic:**
```
1. Check if customer has valid EU VAT number (VIES validated)
2. If valid EU VAT AND different country from Spain → 0% Reverse Charge
3. If Spanish VAT number OR no VAT number → 21% Spanish IVA
4. If non-EU country → 0% (outside EU VAT scope)
5. Store the tax determination reason for audit trail
```

**Reporting Requirements:**
- **Modelo 303**: Quarterly VAT return (Spanish tax authority)
- **Modelo 349**: Intra-community transactions report (quarterly)
- **SII (Suministro Inmediato de Información)**: Real-time invoice reporting to Spanish tax authority (if applicable based on turnover)

**Audit Trail:**
- Record VAT determination for each invoice
- Store VIES validation results with timestamp
- Maintain evidence of customer's business status
- Keep copies of VAT certificates where applicable

---

## Marketing & Public Pages

### Landing Page

The public-facing website that converts visitors to customers:

**Hero Section:**
- Clear value proposition headline
- Subheading explaining the core benefit
- Call-to-action buttons (Start Free Trial, View Demo)
- Hero image/illustration of the product

**Features Section:**
- Visual showcase of key features
- Icons and brief descriptions
- Screenshots or animations of the interface
- Highlight what makes Plannrly different

**Pricing Section:**
- Clear pricing tiers (Starter, Professional, Enterprise)
- Feature comparison table
- Monthly/annual toggle with savings displayed
- Call-to-action for each tier

**Social Proof:**
- Customer testimonials with photos
- Company logos of notable customers
- Statistics (employees scheduled, hours saved, etc.)
- Star ratings and review excerpts

**FAQ Section:**
- Common questions answered
- Expandable/collapsible format
- Links to detailed help articles

### Additional Public Pages

**Pricing Page:**
- Detailed feature comparison
- Add-on pricing
- Volume discounts
- Enterprise custom pricing inquiry

**Features Page:**
- In-depth feature descriptions
- Use case examples
- Screenshots and videos
- Industry-specific benefits

**About Page:**
- Company story and mission
- Team introduction (optional)
- Values and commitments
- Contact information

**Contact Page:**
- Contact form
- Email and phone support
- Office location (if applicable)
- Social media links

**Legal Pages:**
- Terms of Service
- Privacy Policy
- Cookie Policy
- Data Processing Agreement (DPA)
- Service Level Agreement (SLA)

**Resources:**
- Help Center / Knowledge Base
- Getting Started Guide
- Video Tutorials
- Blog (optional)
- System Status Page

---

## SuperAdmin Platform Management

### Subscription Analytics Dashboard

Comprehensive reporting for platform administrators:

**Revenue Metrics:**
- Monthly Recurring Revenue (MRR)
- Annual Recurring Revenue (ARR)
- MRR growth rate (month-over-month)
- Average Revenue Per User (ARPU)
- Lifetime Value (LTV) estimates

**Subscription Metrics:**
- Total active subscriptions
- Subscriptions by plan (Starter/Professional/Enterprise)
- Trial conversions rate
- Churn rate (monthly/annual)
- Net revenue retention

**Growth Metrics:**
- New subscriptions this period
- Upgrades and downgrades
- Cancellations with reasons
- Reactivations
- Trial sign-ups

**Financial Reports:**
- Revenue by period (daily/weekly/monthly)
- Revenue by plan type
- Revenue by feature add-on
- Failed payment tracking
- Refunds and credits issued

**Tenant Health:**
- Active vs inactive tenants
- Feature adoption rates
- Usage patterns (shifts created, users active)
- Tenants approaching limits
- At-risk accounts (low activity)

**Export & Reporting:**
- Export reports to CSV/Excel
- Scheduled email reports
- Custom date range selection
- Comparison periods (vs last month/year)

---

## Technical Overview

### Built for Reliability

Plannrly is built on proven, enterprise-grade technology:

**Laravel Framework**
- Industry-leading PHP framework used by millions of applications
- Battle-tested security features
- Regular updates and long-term support

**Modern Architecture**
- Scalable design that grows with your business
- High availability infrastructure
- Optimized for performance

### Works Everywhere

**Responsive Design**
- Full functionality on desktop computers
- Optimized experience on tablets
- Mobile-friendly interface for on-the-go access

**Browser Compatibility**
- Works with all modern browsers
- No special software installation required
- Access from any internet-connected device

---

## Mobile Experience

Plannrly delivers a seamless mobile experience designed specifically for employees and managers who need to access scheduling features on the go. The mobile interface is not just responsive—it's purposefully designed for touch interactions and mobile workflows.

### Progressive Web App (PWA)

Plannrly functions as a Progressive Web App, providing a native app-like experience:

- **Install to Home Screen** - Add Plannrly to your device's home screen for instant access
- **Offline Support** - View your upcoming schedule even without internet connectivity
- **Push Notifications** - Receive real-time alerts for schedule changes, shift reminders, and approvals
- **Fast Loading** - Cached resources ensure rapid load times on mobile networks
- **No App Store Required** - Works directly in the browser with full functionality

### Employee Mobile Features

The mobile interface prioritizes the features employees use most:

**Schedule at a Glance**
- Today's shifts prominently displayed on dashboard
- Week view optimized for vertical scrolling
- Swipe gestures for navigating between days/weeks
- Color-coded shifts match desktop for consistency

**One-Tap Clock In/Out**
- Large, accessible clock button on dashboard
- GPS location automatically captured
- Confirmation with shift details shown
- Break start/end buttons during active shifts
- Works offline (syncs when connection restored)

**Quick Actions**
- Request time off in 3 taps or fewer
- Initiate shift swap with colleagues
- View and respond to swap requests
- See leave request status instantly

**Personal Information**
- Update availability preferences
- View upcoming schedule PDF
- Access pay stubs and documents
- Update contact information

### Manager Mobile Features

Managers can handle approvals and oversight from anywhere:

**Approval Workflow**
- Push notifications for pending requests
- Swipe to approve or deny leave requests
- Quick review of shift swap requests
- Bulk approval for multiple requests

**Real-Time Monitoring**
- See who's currently clocked in
- Attendance alerts for missed clock-ins
- View today's coverage at a glance
- Contact employees directly from the app

**Quick Schedule Adjustments**
- Assign open shifts to available employees
- Publish draft shifts with one tap
- Send shift reminders to individuals or teams
- View staffing levels by location

### Mobile-Optimized Interface

**Touch-First Design**
- Large tap targets (minimum 44x44 pixels)
- Swipe gestures for common actions
- Pull-to-refresh for data updates
- Bottom navigation for thumb-friendly access

**Adaptive Layouts**
- Schedule grid transforms to list view on mobile
- Collapsible sections to reduce scrolling
- Full-screen modals for focused tasks
- Smart keyboard handling for forms

**Performance Optimized**
- Minimal data transfer on mobile networks
- Image optimization and lazy loading
- Efficient API calls with data caching
- Background sync for offline changes

### Offline Capabilities

Plannrly works even when internet connectivity is limited:

**Available Offline**
- View upcoming schedule (next 14 days)
- See shift details and notes
- View personal profile and availability
- Access saved documents

**Queued for Sync**
- Clock in/out actions queued automatically
- Leave requests saved and submitted when online
- Availability changes synchronized
- Form data preserved if connection lost

**Sync Indicators**
- Clear visual status of online/offline state
- Pending sync count displayed
- Automatic retry when connection restored
- Conflict resolution for simultaneous edits

### Push Notifications

Stay informed with timely mobile notifications:

**Schedule Notifications**
- New shift published or assigned
- Shift changes or cancellations
- Upcoming shift reminders (configurable timing)
- Schedule conflict alerts

**Request Notifications**
- Leave request approved or denied
- Shift swap request received
- Swap request approved or denied
- Document expiry reminders

**Manager Notifications**
- Employee clock-in/out alerts
- Missed shift notifications
- Pending approval reminders
- Staffing shortage alerts

**Notification Preferences**
- Granular control per notification type
- Quiet hours configuration
- Channel preference (push, email, both)
- Manager vs employee defaults

### Cloud-Ready Deployment

**Flexible Hosting Options**
- Cloud deployment for maximum reliability
- Automatic scaling during peak usage
- Geographic redundancy available

**Integration Ready**
- API access for custom integrations
- Export capabilities for reporting
- Compatible with common business tools

---

## User Interface Overview

### Dashboard

The dashboard provides an at-a-glance view of everything that needs attention:

- **Today's Schedule Summary** - Quick view of who's working today
- **Currently Clocked In** - Real-time count of employees on the clock
- **Pending Requests** - Leave and swap requests awaiting approval
- **Upcoming Shifts** - Preview of the coming days
- **Attendance Alerts** - Employees who haven't clocked in for their shift
- **Quick Actions** - One-click access to common tasks (including clock in/out)

### Schedule View

The heart of Plannrly is the schedule interface:

**Week View**
- Full week displayed in a grid format
- Employees listed on the left, days across the top
- Color-coded shifts by role for easy reading
- Drag-and-drop functionality for quick changes

**Day View**
- Detailed timeline of a single day
- See exactly who is working at any given time
- Identify coverage gaps easily
- Perfect for reviewing busy periods

**Print Schedule**
- Print-optimized layout in landscape orientation
- Clean, professional format suitable for posting
- Options to print week view or day view
- Include or exclude employee contact details
- Filter by location, department, or role before printing
- PDF export option for digital distribution
- Automatic page breaks for large schedules

### Employee Management

Manage your team efficiently:

- Searchable employee directory
- Individual employee profiles with HR details
- Employment records (start date, end date, leaving date)
- Compensation management (hourly rates, salary)
- Role and location assignments
- Availability settings and preferences
- Target hours per week configuration
- Employment history and schedule patterns
- Administrative notes and documents

### Employee Profile (Self-Service)

Employees manage their own information:

- Update contact details (phone, email)
- Upload profile photo
- Set availability preferences
- View employment information
- Change password

### AI Scheduling Assistant

Intelligent schedule generation:

- One-click auto-fill for empty schedules
- Suggests optimal employee assignments
- Respects availability and target hours
- Warns about conflicts and understaffing
- Balances workload across team

### Leave Management

Handle time-off with ease:

- Calendar view of all leave requests
- Status indicators (pending, approved, denied)
- One-click approval workflow
- Conflict warnings when approving leave

### Reports and Analytics

Gain insights into your operations:

- Hours worked by employee (scheduled vs actual)
- Labor costs by department (based on actual hours)
- Schedule adherence metrics (punctuality, attendance rate)
- Overtime analysis and trends
- Leave usage patterns
- Timesheet summaries for payroll

---

## Data Retention & Archival

Tenant-configurable data retention policies:

**Configurable Retention:**
- Set retention periods per data type (shifts, time entries, messages, audit logs)
- Archive old data to cold storage before deletion
- Automatic or approval-required deletion workflows
- GDPR-compliant data export on request

**Archive Management:**
- Move data older than threshold to archive tables
- Archived data remains accessible but read-only
- Scheduled deletion of expired archives
- Full audit trail of retention actions

---

## Multi-Channel Notifications

Reach employees through their preferred channels:

**Supported Channels:**
- **Email** - Standard notifications with rich formatting
- **Push Notifications** - PWA and mobile instant alerts
- **Slack Integration** - Post to user DMs or team channels
- **Microsoft Teams** - Native Teams messaging integration
- **WhatsApp Business** - SMS-style messaging for mobile-first users

**Per-User Preferences:**
- Employees choose their preferred channels
- Set quiet hours per channel
- Notification type filtering (shifts only, leave only, etc.)
- Verification required for external channels

---

## Internationalization (i18n)

Full EU language support:

**Supported Languages:**
- English (EN)
- Spanish (ES)
- French (FR)
- German (DE)
- Italian (IT)
- Portuguese (PT)

**Locale Settings:**
- Tenant default locale with employee override
- Configurable date/time formats per tenant
- Currency formatting based on tenant region
- First day of week preference (Monday/Sunday)
- All system messages and emails localized

---

## Offline Support

Full PWA offline capabilities:

**Offline Features:**
- View upcoming schedule without connection
- Clock in/out with offline queue
- Submit availability when back online
- Complete shift tasks offline
- View coworker contacts and schedules

**Sync Mechanism:**
- Automatic background sync when online
- Conflict detection and resolution
- Timestamp preservation for offline actions
- Visual indicator of sync status
- Queue management for pending actions

---

## Employee Self-Service Portal

Empower employees to manage their own information:

**Document Management:**
- Upload certifications, ID documents, contracts
- Track document expiry dates with reminders
- Manager verification workflow
- Configurable document types per tenant

**Availability & Preferences:**
- Submit weekly availability patterns
- Set shift preferences and priorities
- Bid on preferred shifts
- Coworker preferences (work with/avoid)

**Employee Dashboard:**
- Next shift countdown with role and location details
- Clock in/out widget for quick time tracking (when enabled)
- Upcoming shifts for the next 7 days with date, time, and role
- Hours scheduled this week
- Pending leave requests and their status
- Active shift swap requests (sent and received)
- Missed shift alerts with manager contact prompt
- Quick actions for common tasks (request leave, view schedule, swap shifts)

---

## Manager Delegation

Flexible delegation system for coverage and scaling:

**Delegation Types:**
- **Temporary Coverage** - Assign someone during vacation
- **Approval Delegation** - Delegate leave/swap approvals
- **Publishing Rights** - Allow team leads to publish schedules
- **Report Access** - Share reporting capabilities

**Controls:**
- Tenant admin can require approval for delegations
- Scope limitation (specific locations/departments)
- Time-bound delegations with auto-expiry
- Full audit trail of delegated actions
- Configurable per-tenant delegation rules

---

## Team Messaging

Built-in communication for teams:

**Conversation Types:**
- **Direct Messages** - 1:1 private conversations
- **Group Chats** - Team or project-based groups
- **Shift Conversations** - Discussion tied to specific shifts
- **Location Channels** - All staff at a location

**Announcements:**
- Broadcast to entire teams, locations, or departments
- Priority levels (normal, high, urgent)
- Optional acknowledgement requirement
- Scheduled publishing and expiry
- Pinned announcements

**Features:**
- Real-time messaging with read receipts
- Reply threading
- @mentions for notifications
- File and image attachments
- Message search and history

---

## Custom Report Builder

Create tailored reports without code:

**Report Builder:**
- Drag-and-drop column selection
- Filter builder with conditions
- Grouping and aggregation options
- Multiple visualization types (table, bar, line, pie)

**Data Sources:**
- Shifts and schedules
- Time entries and attendance
- Leave requests and balances
- Employee data and roles
- Labor costs and budgets

**Output Options:**
- On-screen interactive view
- Export to PDF or Excel
- Scheduled email delivery (daily, weekly, monthly)
- Shared reports for team access

---

## Billing & Payment Handling

Robust payment failure management:

**Dunning Process:**
- Automatic retry of failed payments (days 1, 3, 7)
- Email notifications at each retry attempt
- 14-day grace period before service suspension
- Clear communication of payment status in dashboard
- Easy payment method update flow

**Billing Features:**
- Proration for mid-cycle plan changes
- Invoice history with downloadable PDFs
- Multiple payment methods (card, SEPA for EU)
- Automatic receipt emails
- Tax handling per region (VAT, reverse charge)

---

## Bulk Operations

Efficient management of large-scale actions:

**Bulk Shift Management:**
- Create multiple shifts from template
- Bulk assign/unassign employees
- Bulk publish/unpublish schedules
- Bulk delete with confirmation
- Copy entire week to another week

**Bulk Employee Management:**
- CSV import for new employees
- Bulk update employee details
- Bulk role assignment
- Bulk deactivation for offboarding
- Export employee data to CSV

**Bulk Approvals:**
- Approve/reject multiple leave requests
- Bulk process shift swap requests
- Batch approve timesheets
- Select all with filters applied

---

## Global Search

Full-text search across all content:

**Searchable Content:**
- Employees (name, email, phone, employee ID)
- Shifts (by date, location, role, status)
- Messages and announcements
- Shift notes and handover content
- Documents and certificates
- Leave requests

**Search Features:**
- Global search bar in navigation
- Instant results as you type
- Filters to narrow by type
- Recent searches saved
- Search within date ranges
- Keyboard shortcuts (Cmd/Ctrl + K)

---

## Employee Invitation & Onboarding

Flexible employee setup options:

**Invitation Methods:**
- **Email Invitation** - Send invite, employee sets password and completes profile
- **Manager Setup** - Manager enters all details, employee receives login credentials
- **Self-Registration** - Employee signs up, manager approves and assigns roles
- **Bulk Import** - CSV upload with automatic invite emails

**Employee Onboarding Flow:**
1. Receive invitation/credentials
2. Set password (if invited)
3. Complete profile (photo, contact details)
4. Set availability preferences
5. Upload required documents
6. Review assigned roles and locations
7. Acknowledge policies (if required)

---

## Advanced Shift Patterns

Support for complex scheduling needs:

**Split Shifts:**
- Morning and evening shifts with unpaid gap
- Example: 9:00-12:00 + 17:00-21:00
- Separate clock in/out for each segment
- Correct calculation of total hours

**Rotating Schedules:**
- Define rotation patterns (Week A/B/C)
- Auto-assign based on rotation
- Fair distribution of shift types
- Visual rotation calendar

**On-Call Shifts:**
- Standby shifts with different pay rates
- Call-out logging when activated
- Minimum guaranteed hours
- On-call allowance tracking

**Overnight Shifts:**
- Shifts crossing midnight handled correctly
- Proper date attribution
- Night differential pay rates (if configured)

**Multi-Day Shifts:**
- Shifts spanning multiple days (e.g., 24-hour care)
- Correct hour calculations
- Break requirements per duration

---

## Performance & SLA Tiers

Service level commitments by plan:

| Metric | Basic | Professional | Enterprise |
|--------|-------|--------------|------------|
| Uptime SLA | 99.0% | 99.5% | 99.9% |
| Page Load | <3s | <2s | <1s |
| API Response | <1s | <500ms | <200ms |
| Support Response | 48h | 24h | 4h |
| Data Backup | Daily | Hourly | Real-time |

**Performance Monitoring:**
- Real-time status page
- Incident communication
- Scheduled maintenance windows
- Performance metrics dashboard (Enterprise)

---

## In-App Help & Support

Contextual assistance throughout the platform:

**Help System:**
- Contextual help tooltips on complex features
- Searchable knowledge base
- Video tutorials for key workflows
- Getting started guides

**AI Chat Assistant (Professional & Enterprise):**
- Available to business admins only
- Answer questions about features
- Help with configuration
- Suggest best practices
- Available during business hours or 24/7 (Enterprise)

**Support Channels:**
- Email support (all plans)
- Priority email (Professional)
- Live chat (Enterprise)
- Phone support (Enterprise)
- Dedicated account manager (Enterprise)

---

## Tenant Branding

Customization options by tier:

**Basic Plan:**
- Upload company logo
- Logo appears in header and emails

**Professional Plan:**
- Custom logo
- Primary brand color
- Secondary/accent color
- Custom email footer

**Enterprise Plan (Full White-Label):**
- Everything in Professional
- Custom domain (app.yourcompany.com)
- Remove Plannrly branding
- Custom favicon
- Custom email templates
- Custom login page background
- CSS overrides for advanced styling

---

## Time Zone Handling

Full multi-timezone support:

**Location Timezone:**
- Each location has its own timezone
- Shifts display in location's timezone
- Clock in/out recorded in location timezone
- Reports respect location timezone

**User Timezone:**
- Users can set preferred display timezone
- Personal calendar shows user's timezone
- Notifications sent in user's timezone
- Clear timezone indicators on all times

**Cross-Timezone Features:**
- Timezone conversion in shift details
- "Your local time" helper text
- Timezone-aware reminders
- DST transitions handled automatically

---

## Employee Offboarding

Graceful handling of departing employees:

**Offboarding Process:**
- Deactivate account (soft delete)
- Remove from future schedules
- Reassign pending tasks/handovers
- Revoke system access immediately
- Keep all historical data for records

**Data Retention:**
- Historical shifts preserved
- Time entries maintained for payroll
- Leave history retained
- Messages archived
- Documents retained per policy

**Compliance:**
- Audit trail of deactivation
- GDPR deletion requests supported separately
- Configurable retention periods
- Data anonymization option (future)

---

## Configurable Approval Workflows

Tenant-defined approval requirements:

**Configurable Approvals:**
- Leave requests (by type)
- Shift swaps
- Overtime (when exceeding contracted hours)
- Schedule changes (after publication)
- Expense claims
- Document uploads
- Availability changes

**Workflow Options:**
- Single approver
- Multi-level approval (manager → HR)
- Auto-approve under threshold (e.g., <2 days leave)
- Require reason for certain types
- Escalation after timeout

**Approval Features:**
- Approve/reject with comments
- Bulk approval
- Delegation during absence
- Mobile-friendly approval UI
- Notification at each stage

---

## Employee Personal Insights

Comprehensive self-service analytics:

**Hours & Earnings:**
- Hours worked this period
- Estimated earnings (if hourly)
- Overtime hours breakdown
- Comparison to contracted hours

**Attendance Metrics:**
- Punctuality rate (on-time arrivals)
- Attendance rate (shifts worked vs scheduled)
- Late arrival patterns
- Early departure patterns

**Trends & Predictions:**
- Hours trend over time
- Leave balance projection
- Upcoming shift load
- Personal schedule fairness score

**Recommendations:**
- Suggested availability updates
- Open shifts matching preferences
- Leave balance reminders
- Training/certification reminders

---

## Public Holiday Management

Comprehensive holiday handling:

**Holiday Import:**
- Auto-import public holidays by country/region
- Support for regional holidays (e.g., Spanish autonomous communities)
- Custom company holidays

**Holiday Pay:**
- Configurable holiday pay rates (e.g., 1.5x, 2x)
- Automatic rate application for shifts on holidays
- Holiday allowance tracking

**Scheduling Features:**
- Visual holiday markers on calendar
- Optional location closures
- Reduced staffing templates for holidays
- Holiday request workflow (work/off preference)

**Notifications:**
- Upcoming holiday reminders
- Holiday schedule publication alerts
- Holiday pay confirmation

---

## Team Availability View

Full visibility for planning:

**Availability Dashboard:**
- Calendar view of team availability
- Filter by location, department, role
- Color-coded availability status
- Week and month views

**Visible Information:**
- Availability patterns (can work/prefer not/cannot)
- Approved leave periods
- Shift preferences
- Skills and certifications
- Current assignments
- Weekly hours committed

**Planning Tools:**
- Gap analysis (understaffed periods)
- Skill coverage check
- Quick assign from availability view
- Export for external planning

---

## Urgent Shift Coverage

Rapid response to last-minute changes:

**Escalated Notifications:**
- Changes within 24h trigger urgent alerts
- Push + SMS for immediate notification
- Multiple notification attempts
- Escalation to manager if no response

**Find Cover Workflow:**
1. Shift becomes vacant (no-show, illness)
2. System identifies qualified available staff
3. Broadcast request to eligible employees
4. First responder claims shift
5. Manager approves or auto-approve option
6. Confirmation to all parties

**Manager Dashboard Alerts:**
- Real-time vacant shift warnings
- No-show alerts
- Coverage status by location
- Quick actions (find cover, reassign, cancel)

**Automatic Features:**
- Auto-post to open shift marketplace
- Priority notification to preferred employees
- Overtime warning if coverage affects hours
- Record of coverage attempts for audit

---

## Future Roadmap: Native Mobile Apps

PWA for launch, native apps planned:

**Current (PWA):**
- Install on any device
- Offline support
- Push notifications
- Full feature parity

**Planned (Native Apps v2):**
- iOS App Store presence
- Google Play Store presence
- Enhanced push notifications
- Biometric authentication
- Better offline performance
- Widget support (shift countdown, etc.)
- Apple Watch / Wear OS (future)

---

## Summary

Plannrly transforms workforce scheduling from a time-consuming chore into a streamlined process. By centralizing scheduling, leave management, time tracking, and employee communication in one intuitive platform, businesses can:

- **Save Time** - Reduce scheduling time by up to 80% with AI-powered automation
- **Improve Communication** - Eliminate missed shifts and miscommunication
- **Empower Employees** - Give staff visibility, self-service options, and profile management
- **Maintain Control** - Keep full oversight while delegating appropriately
- **Track Accurately** - Compare scheduled vs actual hours for precise payroll
- **Scale Confidently** - Grow from one location to many without changing tools
- **Schedule Smarter** - Let AI suggest optimal schedules based on availability and targets

Whether you're a single-location business or a multi-site enterprise, Plannrly adapts to your needs while keeping your data secure and your operations running smoothly.

---

*For more information or to schedule a demonstration, contact us today.*

---

Document Version: 1.14
Last Updated: January 2026
Changelog:
- v1.14: Enhanced Leave Request Management (leave types, allowances, calendar integration), added Organization Settings section, enhanced Employee Dashboard description
- v1.13: Added Billing & Dunning, Bulk Operations, Global Search, Employee Invitation/Onboarding, Advanced Shift Patterns (split, rotating, on-call, overnight, multi-day), Performance SLA Tiers, In-App Help & AI Chat, Tenant Branding Tiers, Full Timezone Support, Employee Offboarding, Configurable Approval Workflows, Employee Personal Insights, Public Holiday Management, Team Availability View, Urgent Shift Coverage, Native Mobile App Roadmap
- v1.12: Added Data Retention & Archival (tenant-configurable), Multi-Channel Notifications (Slack, Teams, WhatsApp), Internationalization (6 EU languages), Offline PWA Support, Employee Self-Service Portal (documents, availability, preferences), Manager Delegation System, Team Messaging Suite (DMs, groups, announcements), Custom Report Builder
- v1.11: Major feature expansion - Added Security (2FA, SSO, Session Management, Audit Logs, GDPR Compliance), Scheduling Enhancements (Shift Preferences, Calendar Integration, Open Shift Marketplace, Schedule Templates, Smart Fill, Real-Time Dashboard, Shift Notes, Conflict Detection, Working Time Compliance, Fairness Analytics, Absence Prediction), Business Model (Freemium Tier, Per-Employee Pricing, Professional Services, Partner Program), and Technical Features (Webhooks, API Rate Limiting, Competitor Import, Onboarding Wizard)
- v1.10: Added EU Intra-Community VAT Compliance section (VIES validation, reverse charge, B2B/B2C tax scenarios, Modelo 349 reporting)
- v1.9: Added Invoicing & Billing Compliance (sequential invoice numbering, ad-hoc invoices) and Tenant Billing Information sections
- v1.8: Added Print Schedule, Marketing & Public Pages, Stripe Payment Processing, and SuperAdmin Analytics Dashboard sections
- v1.7: Added Labor Cost Budgeting and Kiosk Mode sections
- v1.6: Added comprehensive Mobile Experience section (PWA, offline support, push notifications, employee/manager mobile features, touch interface)
- v1.5: Added Premium Add-On Features (Advanced Analytics, Geofencing, Labor Forecasting, Payroll Integrations, Team Messaging, Document Management) and Enterprise Features (Multi-Location Analytics, Custom Branding, API Access)
- v1.4: Added Subscription & Pricing section; AI Scheduling marked as premium add-on
- v1.3: Added Staffing Requirements section with coverage warnings
- v1.2: Added Employee Profile, HR Records, and AI Scheduling sections
- v1.1: Added Time & Attendance (Clock In/Out) feature section
