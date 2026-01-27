<x-layouts.app title="Schedule - Day View">
    <style>
        [x-cloak] { display: none !important; }
        .schedule-cell:hover .add-shift-btn { opacity: 1; }
        .add-shift-btn { opacity: 0; transition: opacity 0.15s; }
        .filter-select:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background-color: #1f2937;
        }
        /* Draft shift styling */
        .shift-bar.is-draft {
            border: 2px dashed rgba(255, 255, 255, 0.4) !important;
            opacity: 0.75;
        }
        .shift-bar.is-draft:hover {
            opacity: 1;
        }
        /* Timeline grid lines */
        .hour-column {
            border-left: 1px solid rgba(55, 65, 81, 0.5);
        }
        .hour-column:first-child {
            border-left: none;
        }
        /* Resize handles */
        .resize-handle {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 8px;
            cursor: ew-resize;
            opacity: 0;
            transition: opacity 0.15s;
            z-index: 10;
        }
        .resize-handle-left {
            left: 0;
            border-radius: 8px 0 0 8px;
        }
        .resize-handle-right {
            right: 0;
            border-radius: 0 8px 8px 0;
        }
        .shift-bar:hover .resize-handle {
            opacity: 1;
            background: rgba(255, 255, 255, 0.3);
        }
        .resize-handle:hover {
            background: rgba(255, 255, 255, 0.5) !important;
        }
        /* Time preview tooltip */
        .time-preview {
            position: fixed;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            pointer-events: none;
            z-index: 1000;
            white-space: nowrap;
        }
        /* Prevent text selection during drag */
        .dragging-active {
            user-select: none;
        }
        .dragging-active * {
            user-select: none;
        }
    </style>

    @php
        $numHours = count($hours);
        $defaultColors = ['#6366f1', '#8b5cf6', '#06b6d4', '#22c55e', '#f59e0b', '#14b8a6', '#f43f5e', '#f97316', '#3b82f6', '#ec4899'];
        $colorIndex = 0;

        // Helper to check if a shift is editable by the current user
        $isShiftEditable = function($shift) use ($canEditShifts, $editableLocationIds, $editableDepartmentIds) {
            if (!$canEditShifts) {
                return false;
            }
            // null means admin - can edit all
            if ($editableLocationIds === null) {
                return true;
            }
            // Location admin can edit shifts in their locations
            if (in_array($shift->location_id, $editableLocationIds)) {
                return true;
            }
            // Department admin can edit shifts in their departments
            if (in_array($shift->department_id, $editableDepartmentIds)) {
                return true;
            }
            return false;
        };

        // Helper to check if user can create shifts for a department
        $canCreateInDepartment = function($departmentId, $locationId) use ($canEditShifts, $editableLocationIds, $editableDepartmentIds) {
            if (!$canEditShifts) {
                return false;
            }
            if ($editableLocationIds === null) {
                return true;
            }
            if (in_array($locationId, $editableLocationIds)) {
                return true;
            }
            if (in_array($departmentId, $editableDepartmentIds)) {
                return true;
            }
            return false;
        };
    @endphp

    <div x-data="scheduleDayApp()" x-init="init()">
        <!-- Top Header -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Previous Day -->
                        <a href="{{ route('schedule.day', ['date' => $selectedDate->copy()->subDay()->format('Y-m-d'), 'group_by' => $groupBy]) }}"
                           class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        @php
                            $singleLocation = $locations->count() === 1;
                            $singleDepartment = $departments->count() === 1;
                            $singleRole = $businessRoles->count() === 1;
                        @endphp
                        <div>
                            @if($singleLocation || $singleDepartment)
                                <p class="text-xs text-gray-500 mb-0.5">
                                    @if($singleLocation){{ $locations->first()->name }}@endif
                                    @if($singleLocation && $singleDepartment) › @endif
                                    @if($singleDepartment){{ $departments->first()->name }}@endif
                                </p>
                            @endif
                            <h1 class="text-xl font-bold text-white">{{ $selectedDate->format('l, F j, Y') }}</h1>
                            <p class="text-sm text-gray-500">{{ $selectedDate->isToday() ? 'Today' : $selectedDate->diffForHumans() }}</p>
                        </div>
                        <!-- Next Day -->
                        <a href="{{ route('schedule.day', ['date' => $selectedDate->copy()->addDay()->format('Y-m-d'), 'group_by' => $groupBy]) }}"
                           class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <!-- Today Button -->
                        @if(!$selectedDate->isToday())
                            <a href="{{ route('schedule.day', ['group_by' => $groupBy]) }}"
                               class="px-3 py-1.5 text-sm font-medium text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                                Today
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-800 rounded-lg p-1">
                            <a href="{{ route('schedule.day', ['date' => $selectedDate->format('Y-m-d'), 'group_by' => $groupBy]) }}"
                               class="px-3 py-1.5 text-sm font-medium text-white bg-brand-900 rounded-md">Day</a>
                            <a href="{{ route('schedule.index', ['start' => $selectedDate->copy()->startOfWeek()->format('Y-m-d'), 'group_by' => $groupBy]) }}"
                               class="px-3 py-1.5 text-sm font-medium text-gray-400 hover:text-white rounded-md transition-colors">Week</a>
                        </div>

                        @if(!auth()->user()->isEmployee())
                            <!-- Publish Button -->
                            <button type="button"
                                    id="publish-all-btn"
                                    @click="publishAll()"
                                    :disabled="draftCount === 0 || publishing"
                                    class="flex items-center gap-2 px-4 py-1.5 text-sm font-medium rounded-lg transition-colors"
                                    :class="draftCount > 0 ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-700 text-gray-500 cursor-not-allowed'">
                                <svg x-show="!publishing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg x-show="publishing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="publishing ? 'Publishing...' : 'Publish (' + draftCount + ')'"></span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Filters Row -->
                <!-- Hidden inputs for single-option filters (for JavaScript compatibility) -->
                @if($singleLocation)
                    <input type="hidden" id="filter-location" value="{{ $locations->first()->id }}">
                @endif
                @if($singleDepartment)
                    <input type="hidden" id="filter-department" value="{{ $departments->first()->id }}">
                @endif
                @if($singleRole)
                    <input type="hidden" id="filter-role" value="{{ $businessRoles->first()->id }}">
                @endif

                <div class="flex items-center gap-4 mt-4">
                    @if(!$singleLocation)
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-500">Location:</label>
                            <select id="filter-location" class="text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(!$singleDepartment)
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-500">Department:</label>
                            <select id="filter-department" {{ $singleLocation ? '' : 'disabled' }} class="filter-select text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                                @if($singleLocation)
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" data-location-id="{{ $department->location_id }}">{{ $department->name }}</option>
                                    @endforeach
                                @else
                                    <option value="">Select Location First</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" data-location-id="{{ $department->location_id }}">{{ $department->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    @if(!$singleRole)
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-500">Role:</label>
                            <select id="filter-role" {{ $singleDepartment ? '' : 'disabled' }} class="filter-select text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                                @if($singleDepartment)
                                    <option value="">All Roles</option>
                                    @foreach($businessRoles as $role)
                                        <option value="{{ $role->id }}" data-department-id="{{ $role->department_id }}">{{ $role->name }}</option>
                                    @endforeach
                                @else
                                    <option value="">Select Department First</option>
                                    @foreach($businessRoles as $role)
                                        <option value="{{ $role->id }}" data-department-id="{{ $role->department_id }}">{{ $role->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Group By:</label>
                        <select id="filter-group-by" class="text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option value="department">Department</option>
                            <option value="role">Role</option>
                        </select>
                    </div>
                    <!-- Make Default Button -->
                    <button type="button" id="make-default-btn" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-brand-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        Make Default
                    </button>
                    <div class="flex items-center gap-2 ml-auto">
                        <input type="text" id="filter-search" placeholder="Search employees..." class="text-sm bg-gray-800 border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-brand-500 focus:border-brand-500 w-48 px-3 py-1.5">
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Timeline Grid -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <div class="min-w-[1200px]">
                    <!-- Hour Headers -->
                    <div class="grid bg-gray-900 border-b border-gray-800 sticky top-0 z-10" style="grid-template-columns: 200px repeat({{ $numHours }}, minmax(60px, 1fr));">
                        <div class="p-4 border-r border-gray-800">
                            <span class="text-sm font-medium text-gray-500">Employee</span>
                        </div>
                        @foreach($hours as $hour)
                            <div class="hour-column p-2 text-center">
                                <div class="text-sm font-medium text-gray-400">{{ sprintf('%02d:00', $hour) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Unassigned Shifts Row -->
                    @if($unassignedShifts->count() > 0)
                        <div class="unassigned-row grid bg-amber-900/10 border-b border-amber-700/30 hover:bg-amber-900/20 transition-colors"
                             style="grid-template-columns: 200px 1fr;">
                            <!-- Unassigned Label -->
                            <div class="p-3 border-r border-amber-700/30 flex items-center gap-3">
                                <div class="w-8 h-8 bg-amber-500/20 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-amber-400">Unassigned</div>
                                    <div class="text-xs text-amber-500/70">{{ $unassignedShifts->count() }} {{ Str::plural('shift', $unassignedShifts->count()) }}</div>
                                </div>
                            </div>

                            <!-- Timeline Area -->
                            <div class="relative h-16 bg-gray-800/20 schedule-cell"
                                 data-user-id=""
                                 data-date="{{ $selectedDate->format('Y-m-d') }}">
                                @foreach($unassignedShifts as $shift)
                                    @php
                                        $startHour = $shift->start_time->hour + ($shift->start_time->minute / 60);
                                        $endHour = $shift->end_time->hour + ($shift->end_time->minute / 60);
                                        if ($endHour < $startHour) $endHour += 24; // Overnight
                                        $leftPercent = (($startHour - $dayStartHour) / $numHours) * 100;
                                        $widthPercent = (($endHour - $startHour) / $numHours) * 100;
                                        $leftPercent = max(0, min(100, $leftPercent));
                                        $widthPercent = max(0, min(100 - $leftPercent, $widthPercent));
                                        $shiftEditable = $isShiftEditable($shift);
                                    @endphp
                                    <div class="shift-bar unassigned-shift absolute top-2 bottom-2 rounded-lg p-2 text-xs text-white {{ $shiftEditable ? 'cursor-pointer' : 'cursor-default' }} hover:brightness-110 transition-colors border border-amber-500/30 {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                         style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $shift->businessRole?->color ?? '#f59e0b' }};"
                                         data-shift-id="{{ $shift->id }}"
                                         data-location-id="{{ $shift->location_id }}"
                                         data-department-id="{{ $shift->department_id }}"
                                         data-role-id="{{ $shift->business_role_id }}"
                                         data-start-time="{{ $shift->start_time->format('H:i') }}"
                                         data-end-time="{{ $shift->end_time->format('H:i') }}"
                                         data-user-id="{{ $shift->user_id }}"
                                         data-status="{{ $shift->status->value }}"
                                         @if($shiftEditable)
                                         @mousedown="startDrag($event, {{ $shift->id }})"
                                         @click.stop="if (!dragState.active) editModal.open({{ $shift->id }})"
                                         @endif>
                                        @if($shiftEditable)
                                        <div class="resize-handle resize-handle-left" @mousedown.stop="startResize($event, {{ $shift->id }}, 'left')"></div>
                                        <div class="resize-handle resize-handle-right" @mousedown.stop="startResize($event, {{ $shift->id }}, 'right')"></div>
                                        @endif
                                        <div class="flex items-center justify-between h-full pointer-events-none">
                                            <div class="flex items-center gap-2 truncate">
                                                @if($shift->isDraft())
                                                    <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wide">Draft</span>
                                                @endif
                                                <span class="font-medium shift-time-display">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                <span class="text-white/70 text-[10px]">{{ $shift->businessRole?->name ?? 'No role' }}</span>
                                            </div>
                                            <span class="text-white/60 ml-2 shrink-0 shift-hours-display">{{ $shift->duration_hours }} hrs</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($groupBy === 'department')
                    {{-- Group by Department --}}
                    @forelse($departments as $department)
                        @php
                            $deptUsers = $usersByDepartment->get($department->id, collect());
                            $deptColor = $department->color ?? '#6366f1';
                        @endphp

                        @if($deptUsers->isNotEmpty())
                            <!-- Department Header -->
                            <div class="group-header border-b px-4 py-2"
                                 data-department-id="{{ $department->id }}"
                                 data-location-id="{{ $department->location_id }}"
                                 style="background-color: {{ $deptColor }}20; border-color: {{ $deptColor }}40;">
                                <span class="text-sm font-semibold" style="color: {{ $deptColor }};">{{ $department->name }}</span>
                                <span class="group-count text-xs ml-2" style="color: {{ $deptColor }}80;">{{ $deptUsers->count() }} {{ Str::plural('employee', $deptUsers->count()) }}</span>
                            </div>

                            @foreach($deptUsers as $user)
                                @php
                                    $primaryRole = $user->businessRoles->where('pivot.is_primary', true)->first();
                                    $userColor = $primaryRole?->color ?? $defaultColors[$colorIndex % count($defaultColors)];
                                    $colorIndex++;
                                    $userRoleIds = $user->businessRoles->pluck('id')->implode(',');
                                    $userShifts = $shiftsLookup[$user->id] ?? [];
                                    $leave = $leaveLookup[$user->id] ?? null;
                                @endphp

                                <!-- Employee Row -->
                                <div class="employee-row grid bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
                                     data-user-id="{{ $user->id }}"
                                     data-department-id="{{ $department->id }}"
                                     data-location-id="{{ $department->location_id }}"
                                     data-role-ids="{{ $userRoleIds }}"
                                     data-name="{{ strtolower($user->full_name) }}"
                                     style="grid-template-columns: 200px 1fr;">
                                    <!-- Employee Info -->
                                    <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs" style="background-color: {{ $userColor }}30; color: {{ $userColor }};">
                                            {{ $user->initials }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-white employee-name">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $userDailyHours[$user->id] ?? 0 }} hrs today</div>
                                        </div>
                                    </div>

                                    <!-- Timeline Area -->
                                    @php $cellEditable = $canCreateInDepartment($department->id, $department->location_id); @endphp
                                    <div class="relative h-16 schedule-cell {{ $cellEditable ? 'cursor-pointer hover:bg-gray-800/30' : '' }} transition-colors"
                                         data-user-id="{{ $user->id }}"
                                         data-date="{{ $selectedDate->format('Y-m-d') }}"
                                         data-location-id="{{ $department->location_id }}"
                                         data-department-id="{{ $department->id }}"
                                         @if($cellEditable)
                                         @click.self="editModal.create({{ $user->id }}, '{{ $selectedDate->format('Y-m-d') }}', {{ $department->location_id }}, {{ $department->id }})"
                                         @endif>

                                        <!-- Hour grid lines -->
                                        <div class="absolute inset-0 grid pointer-events-none" style="grid-template-columns: repeat({{ $numHours }}, 1fr);">
                                            @foreach($hours as $hour)
                                                <div class="hour-column"></div>
                                            @endforeach
                                        </div>

                                        @if($leave)
                                            <!-- Leave Block -->
                                            <div class="absolute inset-2 bg-amber-500/20 border border-amber-500/50 rounded-lg flex items-center justify-center">
                                                <span class="font-medium text-amber-400 text-sm">{{ $leave->leaveType->name ?? 'Leave' }}</span>
                                            </div>
                                        @else
                                            @foreach($userShifts as $shift)
                                                @php
                                                    $startHour = $shift->start_time->hour + ($shift->start_time->minute / 60);
                                                    $endHour = $shift->end_time->hour + ($shift->end_time->minute / 60);
                                                    if ($endHour < $startHour) $endHour += 24; // Overnight
                                                    $leftPercent = (($startHour - $dayStartHour) / $numHours) * 100;
                                                    $widthPercent = (($endHour - $startHour) / $numHours) * 100;
                                                    $leftPercent = max(0, min(100, $leftPercent));
                                                    $widthPercent = max(0, min(100 - $leftPercent, $widthPercent));
                                                    $shiftEditable = $isShiftEditable($shift);
                                                @endphp
                                                <div class="shift-bar absolute top-2 bottom-2 rounded-lg p-2 text-xs text-white {{ $shiftEditable ? 'cursor-pointer' : 'cursor-default' }} hover:brightness-110 transition-colors {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                                     style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $shift->businessRole?->color ?? $userColor }};"
                                                     data-shift-id="{{ $shift->id }}"
                                                     data-start-time="{{ $shift->start_time->format('H:i') }}"
                                                     data-end-time="{{ $shift->end_time->format('H:i') }}"
                                                     data-user-id="{{ $shift->user_id }}"
                                                     data-status="{{ $shift->status->value }}"
                                                     @if($shiftEditable)
                                                     @mousedown="startDrag($event, {{ $shift->id }})"
                                                     @click.stop="if (!dragState.active) editModal.open({{ $shift->id }})"
                                                     @endif>
                                                    @if($shiftEditable)
                                                    <div class="resize-handle resize-handle-left" @mousedown.stop="startResize($event, {{ $shift->id }}, 'left')"></div>
                                                    <div class="resize-handle resize-handle-right" @mousedown.stop="startResize($event, {{ $shift->id }}, 'right')"></div>
                                                    @endif
                                                    <div class="flex items-center justify-between h-full pointer-events-none">
                                                        <div class="flex items-center gap-2 truncate">
                                                            @if($shift->isDraft())
                                                                <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wide">Draft</span>
                                                            @endif
                                                            <span class="font-medium shift-time-display">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                            <span class="text-white/70 text-[10px]">{{ $shift->businessRole?->name ?? 'No role' }}</span>
                                                        </div>
                                                        <span class="text-white/60 ml-2 shrink-0 shift-hours-display">{{ $shift->duration_hours }} hrs</span>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(count($userShifts) === 0 && $cellEditable)
                                                <!-- Empty state - show add button on hover -->
                                                <div class="add-shift-btn absolute inset-2 border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center hover:border-brand-500 hover:bg-brand-500/10 transition-colors">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            No departments found. Create departments and assign employees to see the schedule grid.
                        </div>
                    @endforelse

                    @else
                    {{-- Group by Role --}}
                    @forelse($businessRoles as $role)
                        @php
                            $roleUsers = $usersByRole->get($role->id, collect());
                            $roleColor = $role->color ?? '#6366f1';
                        @endphp

                        @if($roleUsers->isNotEmpty())
                            <!-- Role Header -->
                            <div class="group-header border-b px-4 py-2"
                                 data-role-id="{{ $role->id }}"
                                 data-department-id="{{ $role->department_id }}"
                                 data-location-id="{{ $role->department?->location_id }}"
                                 style="background-color: {{ $roleColor }}20; border-color: {{ $roleColor }}40;">
                                <span class="text-sm font-semibold" style="color: {{ $roleColor }};">{{ $role->name }}</span>
                                <span class="text-xs ml-2" style="color: {{ $roleColor }}60;">{{ $role->department?->name }}</span>
                                <span class="group-count text-xs ml-2" style="color: {{ $roleColor }}80;">{{ $roleUsers->count() }} {{ Str::plural('employee', $roleUsers->count()) }}</span>
                            </div>

                            @foreach($roleUsers as $user)
                                @php
                                    $userColor = $role->color ?? $defaultColors[$colorIndex % count($defaultColors)];
                                    $colorIndex++;
                                    $userRoleIds = $user->businessRoles->pluck('id')->implode(',');
                                    $userDepartment = $role->department;
                                    $userShifts = $shiftsLookup[$user->id] ?? [];
                                    $leave = $leaveLookup[$user->id] ?? null;
                                @endphp

                                <!-- Employee Row -->
                                <div class="employee-row grid bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
                                     data-user-id="{{ $user->id }}"
                                     data-department-id="{{ $userDepartment?->id }}"
                                     data-location-id="{{ $userDepartment?->location_id }}"
                                     data-role-ids="{{ $userRoleIds }}"
                                     data-name="{{ strtolower($user->full_name) }}"
                                     style="grid-template-columns: 200px 1fr;">
                                    <!-- Employee Info -->
                                    <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs" style="background-color: {{ $userColor }}30; color: {{ $userColor }};">
                                            {{ $user->initials }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-white employee-name">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $userDailyHours[$user->id] ?? 0 }} hrs today</div>
                                        </div>
                                    </div>

                                    <!-- Timeline Area -->
                                    @php $cellEditable = $canCreateInDepartment($userDepartment?->id, $userDepartment?->location_id); @endphp
                                    <div class="relative h-16 schedule-cell {{ $cellEditable ? 'cursor-pointer hover:bg-gray-800/30' : '' }} transition-colors"
                                         data-user-id="{{ $user->id }}"
                                         data-date="{{ $selectedDate->format('Y-m-d') }}"
                                         data-location-id="{{ $userDepartment?->location_id }}"
                                         data-department-id="{{ $userDepartment?->id }}"
                                         @if($cellEditable)
                                         @dragover.prevent
                                         @dragenter="handleDragEnter($event)"
                                         @dragleave="handleDragLeave($event)"
                                         @drop="handleDrop($event, {{ $user->id }}, '{{ $selectedDate->format('Y-m-d') }}')"
                                         @click.self="editModal.create({{ $user->id }}, '{{ $selectedDate->format('Y-m-d') }}', {{ $userDepartment?->location_id ?? 'null' }}, {{ $userDepartment?->id ?? 'null' }})"
                                         @endif>

                                        <!-- Hour grid lines -->
                                        <div class="absolute inset-0 grid pointer-events-none" style="grid-template-columns: repeat({{ $numHours }}, 1fr);">
                                            @foreach($hours as $hour)
                                                <div class="hour-column"></div>
                                            @endforeach
                                        </div>

                                        @if($leave)
                                            <!-- Leave Block -->
                                            <div class="absolute inset-2 bg-amber-500/20 border border-amber-500/50 rounded-lg flex items-center justify-center">
                                                <span class="font-medium text-amber-400 text-sm">{{ $leave->leaveType->name ?? 'Leave' }}</span>
                                            </div>
                                        @else
                                            @foreach($userShifts as $shift)
                                                @php
                                                    $startHour = $shift->start_time->hour + ($shift->start_time->minute / 60);
                                                    $endHour = $shift->end_time->hour + ($shift->end_time->minute / 60);
                                                    if ($endHour < $startHour) $endHour += 24; // Overnight
                                                    $leftPercent = (($startHour - $dayStartHour) / $numHours) * 100;
                                                    $widthPercent = (($endHour - $startHour) / $numHours) * 100;
                                                    $leftPercent = max(0, min(100, $leftPercent));
                                                    $widthPercent = max(0, min(100 - $leftPercent, $widthPercent));
                                                    $shiftEditable = $isShiftEditable($shift);
                                                @endphp
                                                <div class="shift-bar absolute top-2 bottom-2 rounded-lg p-2 text-xs text-white {{ $shiftEditable ? 'cursor-pointer' : 'cursor-default' }} hover:brightness-110 transition-colors {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                                     style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $shift->businessRole?->color ?? $userColor }};"
                                                     data-shift-id="{{ $shift->id }}"
                                                     data-start-time="{{ $shift->start_time->format('H:i') }}"
                                                     data-end-time="{{ $shift->end_time->format('H:i') }}"
                                                     data-user-id="{{ $shift->user_id }}"
                                                     data-status="{{ $shift->status->value }}"
                                                     @if($shiftEditable)
                                                     @mousedown="startDrag($event, {{ $shift->id }})"
                                                     @click.stop="if (!dragState.active) editModal.open({{ $shift->id }})"
                                                     @endif>
                                                    @if($shiftEditable)
                                                    <div class="resize-handle resize-handle-left" @mousedown.stop="startResize($event, {{ $shift->id }}, 'left')"></div>
                                                    <div class="resize-handle resize-handle-right" @mousedown.stop="startResize($event, {{ $shift->id }}, 'right')"></div>
                                                    @endif
                                                    <div class="flex items-center justify-between h-full pointer-events-none">
                                                        <div class="flex items-center gap-2 truncate">
                                                            @if($shift->isDraft())
                                                                <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wide">Draft</span>
                                                            @endif
                                                            <span class="font-medium shift-time-display">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                            <span class="text-white/70 text-[10px]">{{ $shift->businessRole?->name ?? 'No role' }}</span>
                                                        </div>
                                                        <span class="text-white/60 ml-2 shrink-0 shift-hours-display">{{ $shift->duration_hours }} hrs</span>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(count($userShifts) === 0 && $cellEditable)
                                                <!-- Empty state - show add button on hover -->
                                                <div class="add-shift-btn absolute inset-2 border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center hover:border-brand-500 hover:bg-brand-500/10 transition-colors">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            No roles found. Create business roles and assign employees to see the schedule grid.
                        </div>
                    @endforelse
                    @endif

                    @if($usersByDepartment->has(0) && $usersByDepartment->get(0)->isNotEmpty())
                        <!-- Unassigned Employees -->
                        <div class="bg-gray-700/30 border-b border-gray-600/30 px-4 py-2">
                            <span class="text-sm font-semibold text-gray-400">Unassigned</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $usersByDepartment->get(0)->count() }} {{ Str::plural('employee', $usersByDepartment->get(0)->count()) }}</span>
                        </div>

                        @foreach($usersByDepartment->get(0) as $user)
                            @php
                                $userShifts = $shiftsLookup[$user->id] ?? [];
                            @endphp
                            <div class="employee-row grid bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
                                 data-user-id="{{ $user->id }}"
                                 data-name="{{ strtolower($user->full_name) }}"
                                 style="grid-template-columns: 200px 1fr;">
                                <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gray-500/20 rounded-full flex items-center justify-center text-gray-400 font-medium text-xs">
                                        {{ $user->initials }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-white">{{ $user->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $userDailyHours[$user->id] ?? 0 }} hrs today</div>
                                    </div>
                                </div>

                                <!-- Timeline Area -->
                                @php $cellEditable = $canCreateInDepartment($departments->first()?->id, $locations->first()?->id); @endphp
                                <div class="relative h-16 schedule-cell {{ $cellEditable ? 'cursor-pointer hover:bg-gray-800/30' : '' }} transition-colors"
                                     data-user-id="{{ $user->id }}"
                                     data-date="{{ $selectedDate->format('Y-m-d') }}"
                                     @if($cellEditable)
                                     @dragover.prevent
                                     @dragenter="handleDragEnter($event)"
                                     @dragleave="handleDragLeave($event)"
                                     @drop="handleDrop($event, {{ $user->id }}, '{{ $selectedDate->format('Y-m-d') }}')"
                                     @click.self="editModal.create({{ $user->id }}, '{{ $selectedDate->format('Y-m-d') }}', {{ $locations->first()?->id ?? 'null' }}, {{ $departments->first()?->id ?? 'null' }})"
                                     @endif>

                                    <!-- Hour grid lines -->
                                    <div class="absolute inset-0 grid pointer-events-none" style="grid-template-columns: repeat({{ $numHours }}, 1fr);">
                                        @foreach($hours as $hour)
                                            <div class="hour-column"></div>
                                        @endforeach
                                    </div>

                                    @foreach($userShifts as $shift)
                                        @php
                                            $startHour = $shift->start_time->hour + ($shift->start_time->minute / 60);
                                            $endHour = $shift->end_time->hour + ($shift->end_time->minute / 60);
                                            if ($endHour < $startHour) $endHour += 24;
                                            $leftPercent = (($startHour - $dayStartHour) / $numHours) * 100;
                                            $widthPercent = (($endHour - $startHour) / $numHours) * 100;
                                            $leftPercent = max(0, min(100, $leftPercent));
                                            $widthPercent = max(0, min(100 - $leftPercent, $widthPercent));
                                            $shiftEditable = $isShiftEditable($shift);
                                        @endphp
                                        <div class="shift-bar absolute top-2 bottom-2 rounded-lg p-2 text-xs text-white {{ $shiftEditable ? 'cursor-pointer' : 'cursor-default' }} hover:brightness-110 transition-colors {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                             style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $shift->businessRole?->color ?? '#4b5563' }};"
                                             data-shift-id="{{ $shift->id }}"
                                             data-start-time="{{ $shift->start_time->format('H:i') }}"
                                             data-end-time="{{ $shift->end_time->format('H:i') }}"
                                             data-user-id="{{ $shift->user_id }}"
                                             data-status="{{ $shift->status->value }}"
                                             @if($shiftEditable)
                                             @mousedown="startDrag($event, {{ $shift->id }})"
                                             @click.stop="if (!dragState.active) editModal.open({{ $shift->id }})"
                                             @endif>
                                            @if($shiftEditable)
                                            <div class="resize-handle resize-handle-left" @mousedown.stop="startResize($event, {{ $shift->id }}, 'left')"></div>
                                            <div class="resize-handle resize-handle-right" @mousedown.stop="startResize($event, {{ $shift->id }}, 'right')"></div>
                                            @endif
                                            <div class="flex items-center justify-between h-full pointer-events-none">
                                                <div class="flex items-center gap-2 truncate">
                                                    @if($shift->isDraft())
                                                        <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wide">Draft</span>
                                                    @endif
                                                    <span class="font-medium shift-time-display">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                    <span class="text-white/70 text-[10px]">{{ $shift->businessRole?->name ?? 'No role' }}</span>
                                                </div>
                                                <span class="text-white/60 ml-2 shrink-0 shift-hours-display">{{ $shift->duration_hours }} hrs</span>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if(count($userShifts) === 0 && $cellEditable)
                                        <div class="add-shift-btn absolute inset-2 border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center hover:border-brand-500 hover:bg-brand-500/10 transition-colors">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Footer Stats -->
            <div class="bg-gray-900 border-t border-gray-800 px-6 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6 text-sm">
                        <div>
                            <span class="text-gray-500">Total hours:</span>
                            <span class="font-semibold text-white ml-1" x-text="stats.totalHours + 'h'"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Shifts:</span>
                            <span class="font-semibold text-white ml-1" x-text="stats.totalShifts"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Unassigned:</span>
                            <span class="font-semibold text-amber-400 ml-1" x-text="stats.unassignedShifts"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Employees:</span>
                            <span class="font-semibold text-white ml-1">{{ $users->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <x-shift-edit-modal
            :shiftStatuses="\App\Enums\ShiftStatus::cases()"
            :locations="$locations"
            :departments="$departments"
            :businessRoles="$businessRoles"
            :users="$users"
        />

        <!-- Notification Modal -->
        <div x-show="notification.isOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="closeNotification()">
            <div class="fixed inset-0 bg-black/60" @click="closeNotification()"></div>

            <div class="flex min-h-full items-center justify-center p-6">
                <div x-show="notification.isOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop
                     class="relative w-96 bg-gray-900 rounded-xl border shadow-xl"
                     :class="notification.type === 'error' ? 'border-red-500/50' : notification.type === 'success' ? 'border-green-500/50' : 'border-gray-700'">

                    <!-- Header -->
                    <div class="flex items-center gap-3 px-6 py-4 border-b"
                         :class="notification.type === 'error' ? 'border-red-500/30' : notification.type === 'success' ? 'border-green-500/30' : 'border-gray-700'">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <template x-if="notification.type === 'error'">
                                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </template>
                            <template x-if="notification.type === 'success'">
                                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </template>
                            <template x-if="notification.type === 'info'">
                                <div class="w-10 h-10 rounded-full bg-brand-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <!-- Title -->
                        <div class="flex-1">
                            <h3 class="text-base font-semibold"
                                :class="notification.type === 'error' ? 'text-red-400' : notification.type === 'success' ? 'text-green-400' : 'text-white'"
                                x-text="notification.title"></h3>
                        </div>
                        <!-- Close Button -->
                        <button @click="closeNotification()" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-300" x-text="notification.message"></p>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-3 border-t border-gray-800 flex justify-end">
                        <button @click="closeNotification()"
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                                :class="notification.type === 'error' ? 'bg-red-600 hover:bg-red-700' : notification.type === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-brand-600 hover:bg-brand-700'">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="confirmModal.isOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="handleCancel()">
            <div class="fixed inset-0 bg-black/60" @click="handleCancel()"></div>

            <div class="flex min-h-full items-center justify-center p-6">
                <div x-show="confirmModal.isOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop
                     class="relative w-96 bg-gray-900 rounded-xl border border-amber-500/50 shadow-xl">

                    <!-- Header -->
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-amber-500/30">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-amber-400" x-text="confirmModal.title"></h3>
                        </div>
                        <button @click="handleCancel()" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-300" x-text="confirmModal.message"></p>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-3 border-t border-gray-800 flex justify-end gap-3">
                        <button @click="handleCancel()"
                                class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button @click="handleConfirm()"
                                class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors">
                            Move Anyway
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal data for cascading filters
        const modalData = {
            locations: @json($locations->map(fn($l) => ['id' => $l->id, 'name' => $l->name])),
            departments: @json($departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'location_id' => $d->location_id])),
            roles: @json($businessRoles->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'department_id' => $r->department_id])),
            users: @json($users->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->full_name,
                'role_ids' => $u->businessRoles->pluck('id')->toArray()
            ]))
        };

        // Helper function to calculate shift hours
        function calculateShiftHours(startTime, endTime) {
            const [startH, startM] = startTime.split(':').map(Number);
            const [endH, endM] = endTime.split(':').map(Number);
            let hours = endH - startH + (endM - startM) / 60;
            if (hours < 0) hours += 24; // Overnight shift
            return Math.round(hours * 100) / 100;
        }

        function scheduleDayApp() {
            return {
                modalData: modalData,
                draftCount: {{ $draftShiftsCount }},
                publishing: false,
                selectedDate: '{{ $selectedDate->format('Y-m-d') }}',
                dayStartHour: {{ $dayStartHour }},
                numHours: {{ $numHours }},
                stats: {
                    totalShifts: {{ $totalShifts }},
                    totalHours: {{ $totalHours }},
                    unassignedShifts: {{ $unassignedShiftsCount }}
                },

                // Notification modal state
                notification: {
                    isOpen: false,
                    type: 'info',
                    title: '',
                    message: ''
                },

                showNotification(type, title, message) {
                    this.notification.type = type;
                    this.notification.title = title;
                    this.notification.message = message;
                    this.notification.isOpen = true;
                },

                showError(message, title = 'Error') {
                    this.showNotification('error', title, message);
                },

                showSuccess(message, title = 'Success') {
                    this.showNotification('success', title, message);
                },

                closeNotification() {
                    this.notification.isOpen = false;
                },

                showConfirm(title, message, onConfirm, onCancel) {
                    this.confirmModal.title = title;
                    this.confirmModal.message = message;
                    this.confirmModal.onConfirm = onConfirm;
                    this.confirmModal.onCancel = onCancel;
                    this.confirmModal.isOpen = true;
                },

                handleConfirm() {
                    this.confirmModal.isOpen = false;
                    if (this.confirmModal.onConfirm) {
                        this.confirmModal.onConfirm();
                    }
                },

                handleCancel() {
                    this.confirmModal.isOpen = false;
                    if (this.confirmModal.onCancel) {
                        this.confirmModal.onCancel();
                    }
                },

                // Drag and drop state
                dragState: {
                    active: false,
                    shiftId: null,
                    shiftElement: null,
                    originalStartTime: null,
                    originalEndTime: null,
                    originalUserId: null,
                    originalLeft: null,
                    originalParent: null,
                    containerRect: null,
                    dragOffsetX: 0,
                    dragOffsetY: 0,
                    targetUserId: null,
                    targetRow: null,
                    shiftStatus: null
                },

                // Confirmation modal state
                confirmModal: {
                    isOpen: false,
                    title: '',
                    message: '',
                    onConfirm: null,
                    onCancel: null
                },

                // Resize state
                resizeState: {
                    active: false,
                    shiftId: null,
                    shiftElement: null,
                    edge: null,
                    containerRect: null,
                    originalStartTime: null,
                    originalEndTime: null,
                    currentStartTime: null,
                    currentEndTime: null,
                    timePreview: null
                },

                init() {
                    this.initFilters();

                    // Listen for stat changes
                    window.addEventListener('stats-change', (e) => {
                        this.stats.totalShifts = Math.max(0, this.stats.totalShifts + (e.detail.shifts || 0));
                        this.stats.totalHours = Math.max(0, this.stats.totalHours + (e.detail.hours || 0));
                        this.stats.unassignedShifts = Math.max(0, this.stats.unassignedShifts + (e.detail.unassigned || 0));
                    });

                    // Listen for draft count changes
                    window.addEventListener('draft-count-change', (e) => {
                        this.draftCount = Math.max(0, this.draftCount + e.detail.delta);
                    });

                    // Set up global mouse event listeners for drag and resize
                    document.addEventListener('mousemove', (e) => {
                        this.handleDragMove(e);
                        this.handleResizeMove(e);
                    });
                    document.addEventListener('mouseup', (e) => {
                        this.handleDragEnd(e);
                        this.handleResizeEnd(e);
                    });
                },

                // ========================================
                // Drag Handlers (mouse-based for visual feedback)
                // ========================================

                startDrag(event, shiftId) {
                    // Only respond to left mouse button
                    if (event.button !== 0) return;

                    const shiftElement = event.target.closest('.shift-bar');
                    if (!shiftElement) return;

                    event.preventDefault();

                    const container = shiftElement.parentElement;
                    const containerRect = container.getBoundingClientRect();
                    const shiftRect = shiftElement.getBoundingClientRect();

                    this.dragState.active = true;
                    this.dragState.shiftId = shiftId;
                    this.dragState.shiftElement = shiftElement;
                    this.dragState.originalStartTime = shiftElement.dataset.startTime;
                    this.dragState.originalEndTime = shiftElement.dataset.endTime;
                    this.dragState.originalUserId = shiftElement.dataset.userId;
                    this.dragState.originalLeft = shiftElement.style.left;
                    this.dragState.originalParent = container;
                    this.dragState.containerRect = containerRect;
                    this.dragState.dragOffsetX = event.clientX - shiftRect.left;
                    this.dragState.dragOffsetY = event.clientY - shiftRect.top;
                    this.dragState.targetUserId = shiftElement.dataset.userId;
                    this.dragState.targetRow = null;
                    this.dragState.shiftStatus = shiftElement.dataset.status;

                    shiftElement.classList.add('dragging');
                    document.body.classList.add('dragging-active');
                },

                handleDragMove(event) {
                    if (!this.dragState.active) return;

                    const element = this.dragState.shiftElement;

                    // Find which schedule-cell we're hovering over
                    const elementsUnderCursor = document.elementsFromPoint(event.clientX, event.clientY);
                    const targetCell = elementsUnderCursor.find(el => el.classList.contains('schedule-cell'));

                    // Clear previous row highlight
                    if (this.dragState.targetRow && this.dragState.targetRow !== targetCell) {
                        this.dragState.targetRow.classList.remove('drag-over');
                    }

                    if (targetCell) {
                        const targetRect = targetCell.getBoundingClientRect();
                        this.dragState.containerRect = targetRect;
                        this.dragState.targetUserId = targetCell.dataset.userId || null;
                        this.dragState.targetRow = targetCell;

                        // Highlight target row if different from original
                        if (targetCell !== this.dragState.originalParent) {
                            targetCell.classList.add('drag-over');
                        }

                        // Move shift element to target cell if different
                        if (element.parentElement !== targetCell) {
                            targetCell.appendChild(element);
                        }
                    }

                    const rect = this.dragState.containerRect;

                    // Calculate new left position
                    const newLeftPx = event.clientX - rect.left - this.dragState.dragOffsetX;
                    const newLeftPercent = (newLeftPx / rect.width) * 100;

                    // Get shift width to clamp properly
                    const widthPercent = parseFloat(element.style.width);

                    // Clamp to container bounds
                    const clampedLeft = Math.max(0, Math.min(newLeftPercent, 100 - widthPercent));

                    element.style.left = `${clampedLeft}%`;

                    // Update time display as shift moves
                    const targetHour = this.dayStartHour + (clampedLeft / 100) * this.numHours;
                    const snappedHour = Math.round(targetHour * 4) / 4;
                    const hours = Math.floor(snappedHour);
                    const minutes = Math.round((snappedHour - hours) * 60);

                    // Calculate end time
                    const [startH, startM] = this.dragState.originalStartTime.split(':').map(Number);
                    const [endH, endM] = this.dragState.originalEndTime.split(':').map(Number);
                    let duration = (endH + endM / 60) - (startH + startM / 60);
                    if (duration < 0) duration += 24;

                    const endDecimal = snappedHour + duration;
                    const endHours = Math.floor(endDecimal) % 24;
                    const endMinutes = Math.round((endDecimal - Math.floor(endDecimal)) * 60);

                    const newStartStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                    const newEndStr = `${String(endHours).padStart(2, '0')}:${String(endMinutes).padStart(2, '0')}`;

                    // Update display
                    const timeDisplay = element.querySelector('.shift-time-display');
                    if (timeDisplay) {
                        timeDisplay.textContent = `${newStartStr} - ${newEndStr}`;
                    }
                },

                async handleDragEnd(event) {
                    if (!this.dragState.active) return;

                    // Immediately stop tracking mouse movements
                    this.dragState.active = false;

                    const element = this.dragState.shiftElement;
                    element.classList.remove('dragging');
                    document.body.classList.remove('dragging-active');

                    // Clear row highlight
                    if (this.dragState.targetRow) {
                        this.dragState.targetRow.classList.remove('drag-over');
                    }

                    // Calculate final time from current position
                    const currentLeft = parseFloat(element.style.left);
                    const targetHour = this.dayStartHour + (currentLeft / 100) * this.numHours;
                    const snappedHour = Math.round(targetHour * 4) / 4;

                    // Calculate duration
                    const [startH, startM] = this.dragState.originalStartTime.split(':').map(Number);
                    const [endH, endM] = this.dragState.originalEndTime.split(':').map(Number);
                    let duration = (endH + endM / 60) - (startH + startM / 60);
                    if (duration < 0) duration += 24;

                    const newStartHours = Math.floor(snappedHour);
                    const newStartMinutes = Math.round((snappedHour - newStartHours) * 60);

                    const newEndDecimal = snappedHour + duration;
                    const newEndHours = Math.floor(newEndDecimal) % 24;
                    const newEndMinutes = Math.round((newEndDecimal - Math.floor(newEndDecimal)) * 60);

                    const newStartTime = `${String(newStartHours).padStart(2, '0')}:${String(newStartMinutes).padStart(2, '0')}`;
                    const newEndTime = `${String(newEndHours).padStart(2, '0')}:${String(newEndMinutes).padStart(2, '0')}`;

                    // Check if time or user changed
                    const userChanged = this.dragState.targetUserId !== this.dragState.originalUserId;
                    const timeChanged = newStartTime !== this.dragState.originalStartTime;

                    if (timeChanged || userChanged) {
                        // Determine the new user_id (could be null for unassigned, or a number)
                        const newUserId = userChanged ? (this.dragState.targetUserId ? parseInt(this.dragState.targetUserId) : null) : undefined;
                        const isPublished = this.dragState.shiftStatus === 'published';

                        const success = await this.updateShiftTime(
                            this.dragState.shiftId,
                            newStartTime,
                            newEndTime,
                            newUserId,
                            isPublished
                        );

                        // If update failed (e.g., overlap), revert position
                        if (!success) {
                            this.revertDragPosition();
                        }
                        this.resetDragState();
                    } else {
                        // Reset position if no change (snap back)
                        this.revertDragPosition();
                        this.resetDragState();
                    }
                },

                revertDragPosition() {
                    const element = this.dragState.shiftElement;
                    if (!element) return;

                    // Move back to original parent
                    if (this.dragState.originalParent && element.parentElement !== this.dragState.originalParent) {
                        this.dragState.originalParent.appendChild(element);
                    }

                    // Reset position
                    element.style.left = this.dragState.originalLeft;

                    // Reset time display
                    const timeDisplay = element.querySelector('.shift-time-display');
                    if (timeDisplay) {
                        timeDisplay.textContent = `${this.dragState.originalStartTime} - ${this.dragState.originalEndTime}`;
                    }
                },

                resetDragState() {
                    this.dragState.active = false;
                    this.dragState.shiftId = null;
                    this.dragState.shiftElement = null;
                    this.dragState.originalStartTime = null;
                    this.dragState.originalEndTime = null;
                    this.dragState.originalUserId = null;
                    this.dragState.originalLeft = null;
                    this.dragState.originalParent = null;
                    this.dragState.containerRect = null;
                    this.dragState.dragOffsetX = 0;
                    this.dragState.dragOffsetY = 0;
                    this.dragState.targetUserId = null;
                    this.dragState.targetRow = null;
                    this.dragState.shiftStatus = null;
                },

                // ========================================
                // Resize Handlers
                // ========================================

                startResize(event, shiftId, edge) {
                    event.preventDefault();
                    event.stopPropagation();

                    const shiftElement = event.target.closest('.shift-bar');
                    if (!shiftElement) return;

                    const container = shiftElement.parentElement;
                    if (!container) return;

                    this.resizeState.active = true;
                    this.resizeState.shiftId = shiftId;
                    this.resizeState.shiftElement = shiftElement;
                    this.resizeState.edge = edge;
                    this.resizeState.containerRect = container.getBoundingClientRect();
                    this.resizeState.originalStartTime = shiftElement.dataset.startTime;
                    this.resizeState.originalEndTime = shiftElement.dataset.endTime;
                    this.resizeState.currentStartTime = shiftElement.dataset.startTime;
                    this.resizeState.currentEndTime = shiftElement.dataset.endTime;

                    // Create time preview tooltip
                    this.resizeState.timePreview = document.createElement('div');
                    this.resizeState.timePreview.className = 'time-preview';
                    this.resizeState.timePreview.textContent = `${this.resizeState.currentStartTime} - ${this.resizeState.currentEndTime}`;
                    document.body.appendChild(this.resizeState.timePreview);
                    this.updateTimePreviewPosition(event);

                    // Add resizing class
                    document.body.classList.add('resizing');
                    document.body.classList.add('dragging-active');
                },

                handleResizeMove(event) {
                    if (!this.resizeState.active) return;

                    const rect = this.resizeState.containerRect;
                    const relativeX = Math.max(0, Math.min(event.clientX - rect.left, rect.width));
                    const percentX = (relativeX / rect.width) * 100;

                    // Calculate hour from position
                    const targetHour = this.dayStartHour + (percentX / 100) * this.numHours;
                    // Snap to 15-minute intervals
                    const snappedHour = Math.round(targetHour * 4) / 4;
                    const hours = Math.floor(snappedHour);
                    const minutes = Math.round((snappedHour - hours) * 60);
                    const timeStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;

                    if (this.resizeState.edge === 'left') {
                        // Ensure start time is before end time (minimum 15 min shift)
                        const [endH, endM] = this.resizeState.currentEndTime.split(':').map(Number);
                        const endDecimal = endH + endM / 60;
                        if (snappedHour < endDecimal - 0.25) {
                            this.resizeState.currentStartTime = timeStr;
                        }
                    } else {
                        // Ensure end time is after start time (minimum 15 min shift)
                        const [startH, startM] = this.resizeState.currentStartTime.split(':').map(Number);
                        const startDecimal = startH + startM / 60;
                        if (snappedHour > startDecimal + 0.25) {
                            this.resizeState.currentEndTime = timeStr;
                        }
                    }

                    // Update visual preview
                    this.updateShiftVisual();
                    this.updateTimePreviewPosition(event);
                },

                handleResizeEnd(event) {
                    if (!this.resizeState.active) return;

                    // Immediately stop tracking mouse movements
                    this.resizeState.active = false;

                    // Remove time preview
                    if (this.resizeState.timePreview) {
                        this.resizeState.timePreview.remove();
                        this.resizeState.timePreview = null;
                    }

                    // Remove resizing classes
                    document.body.classList.remove('resizing');
                    document.body.classList.remove('dragging-active');

                    // Check if times actually changed
                    if (this.resizeState.currentStartTime !== this.resizeState.originalStartTime ||
                        this.resizeState.currentEndTime !== this.resizeState.originalEndTime) {
                        // Check if shift was published
                        const wasPublished = this.resizeState.shiftElement?.dataset.status === 'published';
                        // Save the changes
                        this.updateShiftTime(
                            this.resizeState.shiftId,
                            this.resizeState.currentStartTime,
                            this.resizeState.currentEndTime,
                            undefined, // Don't change user
                            wasPublished
                        );
                    }

                    // Reset remaining state
                    this.resizeState.shiftId = null;
                    this.resizeState.shiftElement = null;
                    this.resizeState.edge = null;
                    this.resizeState.containerRect = null;
                },

                updateShiftVisual() {
                    const element = this.resizeState.shiftElement;
                    if (!element) return;

                    const [startH, startM] = this.resizeState.currentStartTime.split(':').map(Number);
                    const [endH, endM] = this.resizeState.currentEndTime.split(':').map(Number);

                    let startDecimal = startH + startM / 60;
                    let endDecimal = endH + endM / 60;
                    if (endDecimal < startDecimal) endDecimal += 24; // Overnight

                    const leftPercent = ((startDecimal - this.dayStartHour) / this.numHours) * 100;
                    const widthPercent = ((endDecimal - startDecimal) / this.numHours) * 100;

                    element.style.left = `${Math.max(0, Math.min(100, leftPercent))}%`;
                    element.style.width = `${Math.max(0, Math.min(100 - leftPercent, widthPercent))}%`;

                    // Update time display and preview tooltip
                    const timeDisplay = element.querySelector('.shift-time-display');
                    if (timeDisplay) {
                        timeDisplay.textContent = `${this.resizeState.currentStartTime} - ${this.resizeState.currentEndTime}`;
                    }

                    // Update hours display
                    const hoursDisplay = element.querySelector('.shift-hours-display');
                    if (hoursDisplay) {
                        const hours = endDecimal - startDecimal;
                        hoursDisplay.textContent = `${Math.round(hours * 100) / 100} hrs`;
                    }

                    // Update preview tooltip
                    if (this.resizeState.timePreview) {
                        const hours = endDecimal - startDecimal;
                        this.resizeState.timePreview.textContent = `${this.resizeState.currentStartTime} - ${this.resizeState.currentEndTime} (${Math.round(hours * 100) / 100}h)`;
                    }
                },

                updateTimePreviewPosition(event) {
                    if (!this.resizeState.timePreview) return;
                    this.resizeState.timePreview.style.left = `${event.clientX + 10}px`;
                    this.resizeState.timePreview.style.top = `${event.clientY - 30}px`;
                },

                // ========================================
                // API Call to Update Shift
                // ========================================

                async updateShiftTime(shiftId, startTime, endTime, newUserId, wasPublished = false) {
                    try {
                        const body = {
                            date: this.selectedDate,
                            start_time: startTime,
                            end_time: endTime
                        };

                        // Only include user_id if explicitly changing it (undefined means don't change)
                        if (newUserId !== undefined) {
                            body.user_id = newUserId; // null means unassign, number means assign
                        }

                        const response = await fetch(`/shifts/${shiftId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(body)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            console.error('Failed to update shift:', data);
                            // Get the error message - could be validation error or general error
                            let errorMessage = data.message || 'Failed to update shift';
                            if (data.errors) {
                                // Get first validation error
                                const firstError = Object.values(data.errors)[0];
                                errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                            }
                            this.showError(errorMessage, 'Cannot Move Shift');
                            return false;
                        }

                        // Show warning if shift was published, then reload
                        if (wasPublished) {
                            this.showConfirm(
                                'Shift Moved',
                                'This shift is already published. If you move it you will need to publish it again.',
                                () => window.location.reload(),
                                () => window.location.reload()
                            );
                        } else {
                            // Reload page to reflect changes
                            window.location.reload();
                        }
                        return true;
                    } catch (error) {
                        console.error('Error updating shift:', error);
                        this.showError('An error occurred while updating the shift', 'Error');
                        return false;
                    }
                },

                // Get departments filtered by selected location
                getAvailableDepartments() {
                    const locationId = this.editModal.shift.location_id;
                    if (!locationId) return [];
                    return modalData.departments.filter(d => d.location_id == locationId);
                },

                // Get roles filtered by selected department
                getAvailableRoles() {
                    const departmentId = this.editModal.shift.department_id;
                    if (!departmentId) return [];
                    return modalData.roles.filter(r => r.department_id == departmentId);
                },

                // Get employees filtered by selected role
                getAvailableEmployees() {
                    const roleId = this.editModal.shift.business_role_id;
                    if (!roleId) return modalData.users;
                    return modalData.users.filter(u => u.role_ids.includes(parseInt(roleId)));
                },

                // Cascading filter handlers
                onLocationChange() {
                    const depts = this.getAvailableDepartments();
                    if (depts.length > 0) {
                        this.editModal.shift.department_id = depts[0].id;
                        this.onDepartmentChange();
                    } else {
                        this.editModal.shift.department_id = null;
                        this.editModal.shift.business_role_id = null;
                        this.editModal.shift.user_id = null;
                    }
                },

                onDepartmentChange() {
                    const roles = this.getAvailableRoles();
                    if (roles.length > 0) {
                        this.editModal.shift.business_role_id = roles[0].id;
                        this.onRoleChange();
                    } else {
                        this.editModal.shift.business_role_id = null;
                        this.editModal.shift.user_id = null;
                    }
                },

                onRoleChange() {
                    const employees = this.getAvailableEmployees();
                    const currentUserId = this.editModal.shift.user_id;
                    if (currentUserId && !employees.some(u => u.id == currentUserId)) {
                        this.editModal.shift.user_id = null;
                    }
                },

                editModal: {
                    isOpen: false,
                    isCreateMode: false,
                    loading: false,
                    saving: false,
                    publishing: false,
                    deleting: false,
                    confirmDelete: false,
                    error: null,
                    errors: {},
                    shiftId: null,
                    originalUserId: null,
                    originalDate: null,
                    originalStartTime: null,
                    originalEndTime: null,
                    shift: {
                        id: null,
                        location_id: null,
                        department_id: null,
                        business_role_id: null,
                        user_id: null,
                        date: '',
                        start_time: '',
                        end_time: '',
                        break_duration_minutes: 0,
                        notes: '',
                        status: 'draft'
                    },

                    create(userId, date, locationId, departmentId) {
                        this.isCreateMode = true;
                        this.isOpen = true;
                        this.loading = false;
                        this.error = null;
                        this.errors = {};
                        this.confirmDelete = false;
                        this.shiftId = null;
                        this.originalUserId = null;
                        this.originalDate = null;

                        let roleId = null;

                        if (userId) {
                            const user = modalData.users.find(u => u.id == userId);
                            if (user && user.role_ids.length > 0) {
                                const deptRoles = modalData.roles.filter(r => r.department_id == departmentId);
                                const userDeptRole = deptRoles.find(r => user.role_ids.includes(r.id));
                                roleId = userDeptRole ? userDeptRole.id : user.role_ids[0];
                            }
                        } else {
                            const deptRoles = modalData.roles.filter(r => r.department_id == departmentId);
                            if (deptRoles.length > 0) {
                                roleId = deptRoles[0].id;
                            }
                        }

                        this.shift = {
                            id: null,
                            location_id: locationId,
                            department_id: departmentId,
                            business_role_id: roleId,
                            user_id: userId,
                            date: date,
                            start_time: '09:00',
                            end_time: '17:00',
                            break_duration_minutes: 30,
                            notes: '',
                            status: 'draft'
                        };
                    },

                    async open(shiftId) {
                        this.isCreateMode = false;
                        this.shiftId = shiftId;
                        this.isOpen = true;
                        this.loading = true;
                        this.error = null;
                        this.errors = {};
                        this.confirmDelete = false;

                        try {
                            const response = await fetch(`/shifts/${shiftId}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            if (!response.ok) {
                                throw new Error('Failed to load shift');
                            }

                            const data = await response.json();
                            const shift = data.shift;

                            const dateStr = shift.date.includes('T') ? shift.date.split('T')[0] : shift.date;

                            this.originalUserId = shift.user_id;
                            this.originalDate = dateStr;
                            this.originalStartTime = shift.start_time.substring(0, 5);
                            this.originalEndTime = shift.end_time.substring(0, 5);

                            this.shift = {
                                id: shift.id,
                                location_id: shift.location_id,
                                department_id: shift.department_id,
                                business_role_id: shift.business_role_id,
                                user_id: shift.user_id,
                                date: dateStr,
                                start_time: shift.start_time.substring(0, 5),
                                end_time: shift.end_time.substring(0, 5),
                                break_duration_minutes: shift.break_duration_minutes || 0,
                                notes: shift.notes || '',
                                status: shift.status
                            };
                        } catch (error) {
                            this.error = error.message || 'Failed to load shift';
                        } finally {
                            this.loading = false;
                        }
                    },

                    close() {
                        this.isOpen = false;
                        this.isCreateMode = false;
                        this.shiftId = null;
                        this.error = null;
                        this.errors = {};
                        this.confirmDelete = false;
                    },

                    async save() {
                        this.saving = true;
                        this.error = null;
                        this.errors = {};

                        const isCreate = this.isCreateMode;
                        const url = isCreate ? '/shifts' : `/shifts/${this.shiftId}`;
                        const method = isCreate ? 'POST' : 'PUT';

                        try {
                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    location_id: this.shift.location_id,
                                    department_id: this.shift.department_id,
                                    business_role_id: this.shift.business_role_id,
                                    user_id: this.shift.user_id,
                                    date: this.shift.date,
                                    start_time: this.shift.start_time,
                                    end_time: this.shift.end_time,
                                    break_duration_minutes: this.shift.break_duration_minutes,
                                    notes: this.shift.notes,
                                    status: this.shift.status
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422 && data.errors) {
                                    this.errors = data.errors;
                                    return;
                                }
                                throw new Error(data.message || (isCreate ? 'Failed to create shift' : 'Failed to save shift'));
                            }

                            // Reload the page to reflect changes
                            window.location.reload();
                        } catch (error) {
                            this.error = error.message || 'Failed to save shift';
                        } finally {
                            this.saving = false;
                        }
                    },

                    async deleteShift() {
                        this.deleting = true;
                        this.error = null;

                        try {
                            const response = await fetch(`/shifts/${this.shiftId}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            if (!response.ok) {
                                const data = await response.json();
                                throw new Error(data.message || 'Failed to delete shift');
                            }

                            // Reload the page to reflect changes
                            window.location.reload();
                        } catch (error) {
                            this.error = error.message || 'Failed to delete shift';
                        } finally {
                            this.deleting = false;
                        }
                    },

                    async publishShift() {
                        if (this.shift.status !== 'draft') return;

                        this.publishing = true;
                        this.error = null;

                        try {
                            const response = await fetch(`/shifts/${this.shiftId}/publish`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Failed to publish shift');
                            }

                            // Reload the page to reflect changes
                            window.location.reload();
                        } catch (error) {
                            this.error = error.message || 'Failed to publish shift';
                        } finally {
                            this.publishing = false;
                        }
                    }
                },

                // Publish all draft shifts
                async publishAll() {
                    if (this.draftCount === 0 || this.publishing) return;

                    const confirmed = confirm(`Publish ${this.draftCount} draft shift(s) for this day?`);
                    if (!confirmed) return;

                    this.publishing = true;

                    try {
                        const response = await fetch('{{ route("schedule.publish") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                start_date: this.selectedDate,
                                end_date: this.selectedDate
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to publish shifts');
                        }
                    } catch (error) {
                        console.error('Publish error:', error);
                        alert('An error occurred while publishing shifts');
                    } finally {
                        this.publishing = false;
                    }
                },

                // Filter functionality
                initFilters() {
                    const locationFilter = document.getElementById('filter-location');
                    const departmentFilter = document.getElementById('filter-department');
                    const roleFilter = document.getElementById('filter-role');
                    const groupByFilter = document.getElementById('filter-group-by');
                    const searchFilter = document.getElementById('filter-search');
                    const makeDefaultBtn = document.getElementById('make-default-btn');

                    const allDepartmentOptions = Array.from(departmentFilter.querySelectorAll('option'));
                    const allRoleOptions = Array.from(roleFilter.querySelectorAll('option'));

                    const filterEmployees = () => {
                        const selectedLocationId = locationFilter.value;
                        const selectedDepartmentId = departmentFilter.value;
                        const selectedRoleId = roleFilter.value;
                        const searchTerm = searchFilter.value.toLowerCase().trim();

                        const employeeRows = document.querySelectorAll('.employee-row');
                        const groupHeaders = document.querySelectorAll('.group-header');

                        const visibleByDept = {};
                        const visibleByRole = {};

                        employeeRows.forEach(row => {
                            const rowLocationId = row.dataset.locationId;
                            const rowDepartmentId = row.dataset.departmentId;
                            const rowRoleIds = row.dataset.roleIds ? row.dataset.roleIds.split(',') : [];
                            const rowName = row.dataset.name || '';

                            let visible = true;

                            if (selectedLocationId && rowLocationId !== selectedLocationId) {
                                visible = false;
                            }

                            if (visible && selectedDepartmentId && rowDepartmentId !== selectedDepartmentId) {
                                visible = false;
                            }

                            if (visible && selectedRoleId && !rowRoleIds.includes(selectedRoleId)) {
                                visible = false;
                            }

                            if (visible && searchTerm && !rowName.includes(searchTerm)) {
                                visible = false;
                            }

                            row.style.display = visible ? '' : 'none';

                            // Track visibility by department
                            if (!visibleByDept[rowDepartmentId]) {
                                visibleByDept[rowDepartmentId] = 0;
                            }
                            if (visible) {
                                visibleByDept[rowDepartmentId]++;
                            }

                            // Track visibility by role (for role grouping)
                            rowRoleIds.forEach(roleId => {
                                if (!visibleByRole[roleId]) {
                                    visibleByRole[roleId] = 0;
                                }
                                if (visible) {
                                    visibleByRole[roleId]++;
                                }
                            });
                        });

                        groupHeaders.forEach(header => {
                            const deptId = header.dataset.departmentId;
                            const roleId = header.dataset.roleId;
                            const locationId = header.dataset.locationId;

                            // Use role count if this is a role header, otherwise use dept count
                            const visibleCount = roleId ? (visibleByRole[roleId] || 0) : (visibleByDept[deptId] || 0);

                            let showHeader = visibleCount > 0;

                            if (selectedLocationId && locationId !== selectedLocationId) {
                                showHeader = false;
                            }

                            // For role headers, also check department filter
                            if (roleId && selectedDepartmentId && deptId !== selectedDepartmentId) {
                                showHeader = false;
                            }

                            // For role headers, also check role filter
                            if (roleId && selectedRoleId && roleId !== selectedRoleId) {
                                showHeader = false;
                            }

                            header.style.display = showHeader ? '' : 'none';

                            const countSpan = header.querySelector('.group-count');
                            if (countSpan && showHeader) {
                                const word = visibleCount === 1 ? 'employee' : 'employees';
                                countSpan.textContent = `${visibleCount} ${word}`;
                            }
                        });

                        // Filter unassigned shifts
                        const unassignedShifts = document.querySelectorAll('.unassigned-shift');
                        let visibleUnassignedCount = 0;

                        unassignedShifts.forEach(shift => {
                            const shiftLocationId = shift.dataset.locationId;
                            const shiftDepartmentId = shift.dataset.departmentId;
                            const shiftRoleId = shift.dataset.roleId;

                            let visible = true;

                            if (selectedLocationId && shiftLocationId !== selectedLocationId) {
                                visible = false;
                            }

                            if (visible && selectedDepartmentId && shiftDepartmentId !== selectedDepartmentId) {
                                visible = false;
                            }

                            if (visible && selectedRoleId && shiftRoleId !== selectedRoleId) {
                                visible = false;
                            }

                            shift.style.display = visible ? '' : 'none';

                            if (visible) {
                                visibleUnassignedCount++;
                            }
                        });

                        const unassignedRow = document.querySelector('.unassigned-row');
                        if (unassignedRow) {
                            const hasFilters = selectedLocationId || selectedDepartmentId || selectedRoleId;
                            unassignedRow.style.display = (!hasFilters || visibleUnassignedCount > 0) ? '' : 'none';
                        }
                    };

                    locationFilter.addEventListener('change', function() {
                        const selectedLocationId = this.value;

                        departmentFilter.innerHTML = '';
                        roleFilter.innerHTML = '';

                        if (selectedLocationId) {
                            departmentFilter.disabled = false;

                            const selectDeptOption = document.createElement('option');
                            selectDeptOption.value = '';
                            selectDeptOption.textContent = 'Select Department';
                            departmentFilter.appendChild(selectDeptOption);

                            allDepartmentOptions.forEach(option => {
                                if (option.value === '') return;
                                const locationId = option.dataset.locationId;
                                if (locationId === selectedLocationId) {
                                    departmentFilter.appendChild(option.cloneNode(true));
                                }
                            });
                        } else {
                            departmentFilter.disabled = true;
                            const selectDeptOption = document.createElement('option');
                            selectDeptOption.value = '';
                            selectDeptOption.textContent = 'Select Location First';
                            departmentFilter.appendChild(selectDeptOption);
                        }

                        roleFilter.disabled = true;
                        const selectRoleOption = document.createElement('option');
                        selectRoleOption.value = '';
                        selectRoleOption.textContent = 'Select Department First';
                        roleFilter.appendChild(selectRoleOption);

                        filterEmployees();
                    });

                    departmentFilter.addEventListener('change', function() {
                        const selectedDepartmentId = this.value;

                        roleFilter.innerHTML = '';

                        if (selectedDepartmentId) {
                            roleFilter.disabled = false;

                            const selectRoleOption = document.createElement('option');
                            selectRoleOption.value = '';
                            selectRoleOption.textContent = 'Select Role';
                            roleFilter.appendChild(selectRoleOption);

                            allRoleOptions.forEach(option => {
                                if (option.value === '') return;
                                const deptId = option.dataset.departmentId;
                                if (deptId === selectedDepartmentId) {
                                    roleFilter.appendChild(option.cloneNode(true));
                                }
                            });
                        } else {
                            roleFilter.disabled = true;
                            const selectRoleOption = document.createElement('option');
                            selectRoleOption.value = '';
                            selectRoleOption.textContent = 'Select Department First';
                            roleFilter.appendChild(selectRoleOption);
                        }

                        filterEmployees();
                    });

                    roleFilter.addEventListener('change', filterEmployees);

                    let searchTimeout;
                    searchFilter.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(filterEmployees, 200);
                    });

                    // Make Default button click handler
                    makeDefaultBtn.addEventListener('click', async function() {
                        const btn = this;
                        const originalText = btn.innerHTML;

                        btn.innerHTML = `
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        `;
                        btn.disabled = true;

                        try {
                            const response = await fetch('{{ route("user.filter-defaults.store") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    filter_context: 'schedule_day',
                                    location_id: locationFilter.value || null,
                                    department_id: departmentFilter.value || null,
                                    business_role_id: roleFilter.value || null,
                                    group_by: groupByFilter.value || 'department',
                                }),
                            });

                            const data = await response.json();

                            if (data.success) {
                                btn.innerHTML = `
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Saved!
                                `;
                                setTimeout(() => {
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                }, 2000);
                            } else {
                                throw new Error(data.message || 'Failed to save');
                            }
                        } catch (error) {
                            btn.innerHTML = `
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Failed
                            `;
                            setTimeout(() => {
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            }, 2000);
                        }
                    });

                    // Load saved defaults
                    (async () => {
                        try {
                            const response = await fetch('{{ route("user.filter-defaults.show") }}?filter_context=schedule_day', {
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });

                            const data = await response.json();

                            if (data.location_id) {
                                locationFilter.value = data.location_id;
                                departmentFilter.disabled = false;
                                departmentFilter.innerHTML = '<option value="">Select Department</option>';
                                allDepartmentOptions.forEach(option => {
                                    if (option.value === '') return;
                                    if (option.dataset.locationId === String(data.location_id)) {
                                        departmentFilter.appendChild(option.cloneNode(true));
                                    }
                                });

                                if (data.department_id) {
                                    departmentFilter.value = data.department_id;
                                    roleFilter.disabled = false;
                                    roleFilter.innerHTML = '<option value="">Select Role</option>';
                                    allRoleOptions.forEach(option => {
                                        if (option.value === '') return;
                                        if (option.dataset.departmentId === String(data.department_id)) {
                                            roleFilter.appendChild(option.cloneNode(true));
                                        }
                                    });

                                    if (data.business_role_id) {
                                        roleFilter.value = data.business_role_id;
                                    }
                                } else {
                                    roleFilter.innerHTML = '<option value="">Select Department First</option>';
                                }

                                filterEmployees();
                            }

                            // Set group_by from saved defaults (unless already set from URL)
                            const urlParams = new URLSearchParams(window.location.search);
                            if (!urlParams.has('group_by') && data.group_by) {
                                groupByFilter.value = data.group_by;
                                // If the saved group_by is different from current, reload with it
                                const currentGroupBy = '{{ request()->query("group_by", "department") }}';
                                if (data.group_by !== currentGroupBy) {
                                    urlParams.set('group_by', data.group_by);
                                    window.location.search = urlParams.toString();
                                }
                            }
                        } catch (error) {
                            console.error('Failed to load filter defaults:', error);
                        }
                    })();

                    // Set initial group_by value from URL
                    const currentGroupBy = '{{ request()->query("group_by", "department") }}';
                    groupByFilter.value = currentGroupBy;

                    // Handle group_by change - reload page with new setting
                    groupByFilter.addEventListener('change', function() {
                        const urlParams = new URLSearchParams(window.location.search);
                        urlParams.set('group_by', this.value);
                        window.location.search = urlParams.toString();
                    });
                }
            };
        }
    </script>
</x-layouts.app>
