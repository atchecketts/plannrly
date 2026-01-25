<x-layouts.app title="Schedule">
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
        .shift-block.is-draft {
            border: 2px dashed rgba(255, 255, 255, 0.4) !important;
            opacity: 0.75;
        }
        .shift-block.is-draft:hover {
            opacity: 1;
        }
    </style>

    @php
        $numDays = count($weekDates);
        $defaultColors = ['#6366f1', '#8b5cf6', '#06b6d4', '#22c55e', '#f59e0b', '#14b8a6', '#f43f5e', '#f97316', '#3b82f6', '#ec4899'];
        $colorIndex = 0;
    @endphp

    <div x-data="scheduleApp()" x-init="init()"
         @draft-count-change.window="draftCount = Math.max(0, draftCount + $event.detail.delta)"
         @stats-change.window="
            stats.totalShifts = Math.max(0, stats.totalShifts + ($event.detail.shifts || 0));
            stats.totalHours = Math.max(0, stats.totalHours + ($event.detail.hours || 0));
            stats.unassignedShifts = Math.max(0, stats.unassignedShifts + ($event.detail.unassigned || 0));
         ">
        <!-- Top Header -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Previous Week -->
                        <a href="{{ route('schedule.index', ['start' => $startDate->copy()->subWeek()->format('Y-m-d'), 'group_by' => $groupBy]) }}" class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-xl font-bold text-white">{{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</h1>
                            <p class="text-sm text-gray-500">Week {{ $startDate->weekOfYear }}</p>
                        </div>
                        <!-- Next Week -->
                        <a href="{{ route('schedule.index', ['start' => $startDate->copy()->addWeek()->format('Y-m-d'), 'group_by' => $groupBy]) }}" class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <!-- Today Button -->
                        @if(!$startDate->isCurrentWeek())
                            <a href="{{ route('schedule.index', ['group_by' => $groupBy]) }}" class="ml-2 px-3 py-1.5 text-sm text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg transition-colors">
                                Today
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-800 rounded-lg p-1">
                            <a href="{{ route('schedule.day', ['date' => $startDate->format('Y-m-d'), 'group_by' => $groupBy]) }}"
                               class="px-3 py-1.5 text-sm font-medium text-gray-400 hover:text-white rounded-md transition-colors">Day</a>
                            <a href="{{ route('schedule.index', ['start' => $startDate->format('Y-m-d'), 'group_by' => $groupBy]) }}"
                               class="px-3 py-1.5 text-sm font-medium text-white bg-brand-900 rounded-md">Week</a>
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

                <!-- Filters -->
                <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Location:</label>
                        <select id="filter-location" class="text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option value="">Select Location</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" data-location-id="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Department:</label>
                        <select id="filter-department" disabled class="filter-select text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option value="">Select Location First</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" data-location-id="{{ $department->location_id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Role:</label>
                        <select id="filter-role" disabled class="filter-select text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option value="">Select Department First</option>
                            @foreach($businessRoles as $role)
                                <option value="{{ $role->id }}" data-department-id="{{ $role->department_id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
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

        <!-- Schedule Grid -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <div class="min-w-[1000px]">
                    <!-- Day Headers -->
                    <div class="grid bg-gray-900 border-b border-gray-800 sticky top-0 z-10" style="grid-template-columns: 200px repeat({{ $numDays }}, minmax(100px, 1fr));">
                        <div class="p-4 border-r border-gray-800">
                            <span class="text-sm font-medium text-gray-500">Employee</span>
                        </div>
                        @foreach($weekDates as $date)
                            <div class="p-4 border-r border-gray-800 text-center {{ $date->isWeekend() ? 'bg-gray-800/50' : '' }} {{ $date->isToday() ? 'bg-brand-900/20' : '' }}">
                                <div class="text-sm font-medium {{ $date->isWeekend() ? 'text-gray-500' : 'text-gray-400' }} {{ $date->isToday() ? 'text-brand-400' : '' }}">{{ $date->format('D') }}</div>
                                <div class="text-lg font-bold {{ $date->isWeekend() ? 'text-gray-400' : 'text-white' }} {{ $date->isToday() ? 'text-brand-400' : '' }}">{{ $date->format('d') }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Unassigned Shifts Row -->
                    <div class="unassigned-row grid bg-amber-900/10 border-b border-amber-700/30 hover:bg-amber-900/20 transition-colors"
                         data-user-id=""
                         style="grid-template-columns: 200px repeat({{ $numDays }}, minmax(100px, 1fr));">
                        <!-- Unassigned Label -->
                        <div class="p-3 border-r border-amber-700/30 flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-amber-400">Unassigned</div>
                                <div class="text-xs text-amber-500/70" x-text="stats.unassignedShifts + ' ' + (stats.unassignedShifts === 1 ? 'shift' : 'shifts')"></div>
                            </div>
                        </div>

                        <!-- Day Cells for Unassigned -->
                        @foreach($weekDates as $date)
                            @php
                                $dateStr = $date->format('Y-m-d');
                                $unassignedForDate = $unassignedShiftsLookup[$dateStr] ?? [];
                                $isWeekend = $date->isWeekend();
                                $isToday = $date->isToday();
                            @endphp

                            <div class="p-2 border-r border-amber-700/30 {{ $isWeekend ? 'bg-gray-800/30' : '' }} {{ $isToday ? 'bg-brand-900/10' : '' }} schedule-cell cursor-pointer hover:bg-gray-800 transition-colors"
                                 data-user-id=""
                                 data-date="{{ $dateStr }}"
                                 data-location-id="{{ $locations->first()?->id }}"
                                 data-department-id="{{ $departments->first()?->id }}"
                                 @dragover.prevent
                                 @dragenter="handleDragEnter($event)"
                                 @dragleave="handleDragLeave($event)"
                                 @drop="handleDrop($event, null, '{{ $dateStr }}')"
                                 title="Click to add unassigned shift">

                                @if(count($unassignedForDate) > 0)
                                    <div class="space-y-1">
                                        @foreach($unassignedForDate as $shift)
                                            <!-- Unassigned Shift Block -->
                                            <div class="shift-block unassigned-shift text-white rounded-lg p-2 text-xs cursor-move transition-colors hover:brightness-110 border border-amber-500/30 {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                                 style="background-color: {{ $shift->businessRole?->color ?? '#f59e0b' }};"
                                                 data-shift-id="{{ $shift->id }}"
                                                 data-user-id=""
                                                 data-date="{{ $dateStr }}"
                                                 data-status="{{ $shift->status->value }}"
                                                 data-start-time="{{ $shift->start_time->format('H:i') }}"
                                                 data-end-time="{{ $shift->end_time->format('H:i') }}"
                                                 data-location-id="{{ $shift->location_id }}"
                                                 data-department-id="{{ $shift->department_id }}"
                                                 data-role-id="{{ $shift->business_role_id }}"
                                                 draggable="true"
                                                 @dragstart="handleDragStart($event, {{ $shift->id }})"
                                                 @dragend="handleDragEnd($event)"
                                                 @click.stop="editModal.open({{ $shift->id }})">
                                                @if($shift->isDraft())
                                                    <div class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mb-0.5">Draft</div>
                                                @endif
                                                <div class="shift-times font-medium flex items-center justify-between">
                                                    <span>{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                    <span class="text-white/60">{{ $shift->duration_hours }} hrs</span>
                                                </div>
                                                <div class="shift-role truncate" style="color: rgba(255,255,255,0.85);">{{ $shift->businessRole?->name ?? 'No role' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- Empty Cell - Add Unassigned Shift Placeholder -->
                                    <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-amber-700/50 rounded-lg flex items-center justify-center cursor-pointer hover:border-amber-500 hover:bg-amber-500/10 transition-colors"
                                         @click.stop="editModal.create(null, '{{ $dateStr }}', {{ $locations->first()?->id ?? 'null' }}, {{ $departments->first()?->id ?? 'null' }})">
                                        <svg class="w-5 h-5 text-amber-600/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($groupBy === 'department')
                    {{-- Group by Department --}}
                    @forelse($departments as $department)
                        @php
                            $deptUsers = $usersByDepartment->get($department->id, collect());
                            $deptColor = $department->color ?? '#6366f1';
                        @endphp

                        @if($deptUsers->isNotEmpty())
                            <!-- Department Header -->
                            <div class="department-header group-header border-b px-4 py-2"
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
                                @endphp

                                <!-- Employee Row -->
                                <div class="employee-row grid bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
                                     data-user-id="{{ $user->id }}"
                                     data-department-id="{{ $department->id }}"
                                     data-location-id="{{ $department->location_id }}"
                                     data-role-ids="{{ $userRoleIds }}"
                                     data-name="{{ strtolower($user->full_name) }}"
                                     style="grid-template-columns: 200px repeat({{ $numDays }}, minmax(100px, 1fr));">
                                    <!-- Employee Info -->
                                    <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs" style="background-color: {{ $userColor }}30; color: {{ $userColor }};">
                                            {{ $user->initials }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-white employee-name">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $userWeeklyHours[$user->id] ?? 0 }} hrs this week</div>
                                        </div>
                                    </div>

                                    <!-- Day Cells -->
                                    @foreach($weekDates as $date)
                                        @php
                                            $dateStr = $date->format('Y-m-d');
                                            $userShifts = $shiftsLookup[$user->id][$dateStr] ?? [];
                                            $leave = $leaveLookup[$user->id][$dateStr] ?? null;
                                            $isWeekend = $date->isWeekend();
                                            $isToday = $date->isToday();
                                        @endphp

                                        <div class="p-2 border-r border-gray-800 {{ $isWeekend ? 'bg-gray-800/30' : '' }} {{ $isToday ? 'bg-brand-900/10' : '' }} schedule-cell cursor-pointer hover:bg-gray-800 transition-colors"
                                             data-user-id="{{ $user->id }}"
                                             data-date="{{ $dateStr }}"
                                             data-location-id="{{ $department->location_id }}"
                                             data-department-id="{{ $department->id }}"
                                             @dragover.prevent
                                             @dragenter="handleDragEnter($event)"
                                             @dragleave="handleDragLeave($event)"
                                             @drop="handleDrop($event, {{ $user->id }}, '{{ $dateStr }}')"
                                             title="Click to add shift">

                                            @if($leave)
                                                <!-- Leave Block -->
                                                <div class="bg-amber-500/20 border border-amber-500/50 rounded-lg p-2 text-xs">
                                                    <div class="font-medium text-amber-400">{{ $leave->leaveType->name ?? 'Leave' }}</div>
                                                </div>
                                            @elseif(count($userShifts) > 0)
                                                <div class="space-y-1">
                                                    @foreach($userShifts as $shift)
                                                        <!-- Shift Block -->
                                                        <div class="shift-block text-white rounded-lg p-2 text-xs cursor-move transition-colors hover:brightness-110 {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                                             style="background-color: {{ $shift->businessRole?->color ?? $userColor }};"
                                                             data-shift-id="{{ $shift->id }}"
                                                             data-user-id="{{ $user->id }}"
                                                             data-date="{{ $dateStr }}"
                                                             data-status="{{ $shift->status->value }}"
                                                             data-start-time="{{ $shift->start_time->format('H:i') }}"
                                                             data-end-time="{{ $shift->end_time->format('H:i') }}"
                                                             draggable="true"
                                                             @dragstart="handleDragStart($event, {{ $shift->id }})"
                                                             @dragend="handleDragEnd($event)"
                                                             @click.stop="editModal.open({{ $shift->id }})">
                                                            @if($shift->isDraft())
                                                                <div class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mb-0.5">Draft</div>
                                                            @endif
                                                            <div class="shift-times font-medium flex items-center justify-between">
                                                                <span>{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                                <span class="text-white/60">{{ $shift->duration_hours }} hrs</span>
                                                            </div>
                                                            <div class="shift-role truncate" style="color: rgba(255,255,255,0.85);">{{ $shift->businessRole?->name ?? 'No role' }}</div>
                                                        </div>
                                                    @endforeach
                                                    <!-- Add Another Shift Button -->
                                                    <div class="add-shift-btn h-6 border border-dashed border-gray-700 rounded flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors"
                                                         @click.stop="editModal.create({{ $user->id }}, '{{ $dateStr }}', {{ $department->location_id }}, {{ $department->id }})">
                                                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Empty Cell - Add Shift Placeholder -->
                                                <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors"
                                                     @click.stop="editModal.create({{ $user->id }}, '{{ $dateStr }}', {{ $department->location_id }}, {{ $department->id }})">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
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
                                @endphp

                                <!-- Employee Row -->
                                <div class="employee-row grid bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
                                     data-user-id="{{ $user->id }}"
                                     data-department-id="{{ $userDepartment?->id }}"
                                     data-location-id="{{ $userDepartment?->location_id }}"
                                     data-role-ids="{{ $userRoleIds }}"
                                     data-name="{{ strtolower($user->full_name) }}"
                                     style="grid-template-columns: 200px repeat({{ $numDays }}, minmax(100px, 1fr));">
                                    <!-- Employee Info -->
                                    <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-xs" style="background-color: {{ $userColor }}30; color: {{ $userColor }};">
                                            {{ $user->initials }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-white employee-name">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $userWeeklyHours[$user->id] ?? 0 }} hrs this week</div>
                                        </div>
                                    </div>

                                    <!-- Day Cells -->
                                    @foreach($weekDates as $date)
                                        @php
                                            $dateStr = $date->format('Y-m-d');
                                            $userShifts = $shiftsLookup[$user->id][$dateStr] ?? [];
                                            $leave = $leaveLookup[$user->id][$dateStr] ?? null;
                                            $isWeekend = $date->isWeekend();
                                            $isToday = $date->isToday();
                                        @endphp

                                        <div class="p-2 border-r border-gray-800 {{ $isWeekend ? 'bg-gray-800/30' : '' }} {{ $isToday ? 'bg-brand-900/10' : '' }} schedule-cell cursor-pointer hover:bg-gray-800 transition-colors"
                                             data-user-id="{{ $user->id }}"
                                             data-date="{{ $dateStr }}"
                                             data-location-id="{{ $userDepartment?->location_id }}"
                                             data-department-id="{{ $userDepartment?->id }}"
                                             @dragover.prevent
                                             @dragenter="handleDragEnter($event)"
                                             @dragleave="handleDragLeave($event)"
                                             @drop="handleDrop($event, {{ $user->id }}, '{{ $dateStr }}')"
                                             title="Click to add shift">

                                            @if($leave)
                                                <!-- Leave Block -->
                                                <div class="bg-amber-500/20 border border-amber-500/50 rounded-lg p-2 text-xs">
                                                    <div class="font-medium text-amber-400">{{ $leave->leaveType->name ?? 'Leave' }}</div>
                                                </div>
                                            @elseif(count($userShifts) > 0)
                                                <div class="space-y-1">
                                                    @foreach($userShifts as $shift)
                                                        <!-- Shift Block -->
                                                        <div class="shift-block text-white rounded-lg p-2 text-xs cursor-move transition-colors hover:brightness-110 {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                                             style="background-color: {{ $shift->businessRole?->color ?? $userColor }};"
                                                             data-shift-id="{{ $shift->id }}"
                                                             data-user-id="{{ $user->id }}"
                                                             data-date="{{ $dateStr }}"
                                                             data-status="{{ $shift->status->value }}"
                                                             data-start-time="{{ $shift->start_time->format('H:i') }}"
                                                             data-end-time="{{ $shift->end_time->format('H:i') }}"
                                                             draggable="true"
                                                             @dragstart="handleDragStart($event, {{ $shift->id }})"
                                                             @dragend="handleDragEnd($event)"
                                                             @click.stop="editModal.open({{ $shift->id }})">
                                                            @if($shift->isDraft())
                                                                <div class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mb-0.5">Draft</div>
                                                            @endif
                                                            <div class="shift-times font-medium flex items-center justify-between">
                                                                <span>{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                                <span class="text-white/60">{{ $shift->duration_hours }} hrs</span>
                                                            </div>
                                                            <div class="shift-role truncate" style="color: rgba(255,255,255,0.85);">{{ $shift->businessRole?->name ?? 'No role' }}</div>
                                                        </div>
                                                    @endforeach
                                                    <!-- Add Another Shift Button -->
                                                    <div class="add-shift-btn h-6 border border-dashed border-gray-700 rounded flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors"
                                                         @click.stop="editModal.create({{ $user->id }}, '{{ $dateStr }}', {{ $userDepartment?->location_id ?? 'null' }}, {{ $userDepartment?->id ?? 'null' }})">
                                                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Empty Cell - Add Shift Placeholder -->
                                                <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors"
                                                     @click.stop="editModal.create({{ $user->id }}, '{{ $dateStr }}', {{ $userDepartment?->location_id ?? 'null' }}, {{ $userDepartment?->id ?? 'null' }})">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
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
                            <div class="grid bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors" style="grid-template-columns: 200px repeat({{ $numDays }}, minmax(100px, 1fr));">
                                <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gray-500/20 rounded-full flex items-center justify-center text-gray-400 font-medium text-xs">
                                        {{ $user->initials }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-white">{{ $user->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $userWeeklyHours[$user->id] ?? 0 }} hrs this week</div>
                                    </div>
                                </div>

                                @foreach($weekDates as $date)
                                    @php
                                        $dateStr = $date->format('Y-m-d');
                                        $userShifts = $shiftsLookup[$user->id][$dateStr] ?? [];
                                        $isWeekend = $date->isWeekend();
                                        $isToday = $date->isToday();
                                    @endphp

                                    <div class="p-2 border-r border-gray-800 {{ $isWeekend ? 'bg-gray-800/30' : '' }} {{ $isToday ? 'bg-brand-900/10' : '' }} schedule-cell cursor-pointer hover:bg-gray-800 transition-colors"
                                         data-user-id="{{ $user->id }}"
                                         data-date="{{ $dateStr }}"
                                         data-location-id="{{ $locations->first()?->id }}"
                                         data-department-id="{{ $departments->first()?->id }}"
                                         @dragover.prevent
                                         @dragenter="handleDragEnter($event)"
                                         @dragleave="handleDragLeave($event)"
                                         @drop="handleDrop($event, {{ $user->id }}, '{{ $dateStr }}')">
                                        @if(count($userShifts) > 0)
                                            <div class="space-y-1">
                                                @foreach($userShifts as $shift)
                                                    <div class="shift-block text-white rounded-lg p-2 text-xs cursor-move hover:brightness-110 {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                                         style="background-color: {{ $shift->businessRole?->color ?? '#4b5563' }};"
                                                         data-shift-id="{{ $shift->id }}"
                                                         data-user-id="{{ $user->id }}"
                                                         data-date="{{ $dateStr }}"
                                                         data-status="{{ $shift->status->value }}"
                                                         data-start-time="{{ $shift->start_time->format('H:i') }}"
                                                         data-end-time="{{ $shift->end_time->format('H:i') }}"
                                                         draggable="true"
                                                         @dragstart="handleDragStart($event, {{ $shift->id }})"
                                                         @dragend="handleDragEnd($event)"
                                                         @click.stop="editModal.open({{ $shift->id }})">
                                                        @if($shift->isDraft())
                                                            <div class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mb-0.5">Draft</div>
                                                        @endif
                                                        <div class="shift-times font-medium flex items-center justify-between">
                                                            <span>{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                            <span class="text-white/60">{{ $shift->duration_hours }} hrs</span>
                                                        </div>
                                                        <div class="shift-role truncate" style="color: rgba(255,255,255,0.85);">{{ $shift->businessRole?->name ?? 'No role' }}</div>
                                                    </div>
                                                @endforeach
                                                <!-- Add Another Shift Button -->
                                                <div class="add-shift-btn h-6 border border-dashed border-gray-700 rounded flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors"
                                                     @click.stop="editModal.create({{ $user->id }}, '{{ $dateStr }}', {{ $locations->first()?->id ?? 'null' }}, {{ $departments->first()?->id ?? 'null' }})">
                                                    <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors"
                                                 @click.stop="editModal.create({{ $user->id }}, '{{ $dateStr }}', {{ $locations->first()?->id ?? 'null' }}, {{ $departments->first()?->id ?? 'null' }})">
                                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
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
                            <span class="font-semibold text-green-400 ml-1" x-text="stats.totalShifts"></span>
                        </div>
                        <div x-show="stats.unassignedShifts > 0">
                            <span class="text-gray-500">Unassigned:</span>
                            <span class="font-semibold text-amber-400 ml-1" x-text="stats.unassignedShifts"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Employees:</span>
                            <span class="font-semibold text-white ml-1">{{ $users->count() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 text-gray-300 bg-gray-800 border border-gray-700 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                            Copy Previous Week
                        </button>
                        <button class="px-4 py-2 text-gray-300 bg-gray-800 border border-gray-700 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                            Clear All
                        </button>
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
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60" @click="closeNotification()"></div>

            <!-- Modal -->
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
                            <!-- Error Icon -->
                            <template x-if="notification.type === 'error'">
                                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </template>
                            <!-- Success Icon -->
                            <template x-if="notification.type === 'success'">
                                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </template>
                            <!-- Info Icon -->
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

        // Calculate shift hours, handling overnight shifts
        function calculateShiftHours(startTime, endTime) {
            const startParts = startTime.split(':');
            const endParts = endTime.split(':');
            const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);

            // Handle overnight shifts (end time is before start time)
            let durationMinutes = endMinutes - startMinutes;
            if (durationMinutes < 0) {
                durationMinutes += 24 * 60; // Add 24 hours
            }

            return Math.round(durationMinutes / 60);
        }

        function scheduleApp() {
            return {
                modalData: modalData,
                draggedShiftId: null,
                draggedElement: null,
                draftCount: {{ $draftShiftsCount }},
                publishing: false,
                startDate: '{{ $startDate->format('Y-m-d') }}',
                endDate: '{{ $endDate->format('Y-m-d') }}',

                // Stats
                stats: {
                    totalShifts: {{ $totalShifts }},
                    totalHours: {{ $totalHours }},
                    unassignedShifts: {{ $unassignedShifts }}
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

                // Publish all draft shifts
                async publishAll() {
                    if (this.draftCount === 0 || this.publishing) return;

                    const confirmed = confirm(`Publish ${this.draftCount} draft shift(s) for this week?`);
                    if (!confirmed) return;

                    this.publishing = true;

                    try {
                        const locationId = document.getElementById('filter-location').value;
                        const departmentId = document.getElementById('filter-department').value;
                        const roleId = document.getElementById('filter-role').value;

                        const response = await fetch('{{ route("schedule.publish") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                start_date: this.startDate,
                                end_date: this.endDate,
                                location_id: locationId || null,
                                department_id: departmentId || null,
                                business_role_id: roleId || null
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update draft count
                            this.draftCount = 0;

                            // Remove draft styling from all shift blocks
                            document.querySelectorAll('.shift-block.is-draft').forEach(block => {
                                block.classList.remove('is-draft');
                                // Remove draft label
                                const draftLabel = block.querySelector('.text-\\[10px\\]');
                                if (draftLabel) draftLabel.remove();
                                // Update data attribute
                                block.dataset.status = 'published';
                            });

                            this.showSuccess(data.message, 'Shifts Published');
                        } else {
                            this.showError(data.message || 'Failed to publish shifts');
                        }
                    } catch (error) {
                        console.error('Failed to publish shifts:', error);
                        this.showError('Failed to publish shifts. Please try again.');
                    } finally {
                        this.publishing = false;
                    }
                },

                // Refresh draft count from server
                async refreshDraftCount() {
                    try {
                        const locationId = document.getElementById('filter-location').value;
                        const departmentId = document.getElementById('filter-department').value;
                        const roleId = document.getElementById('filter-role').value;

                        const params = new URLSearchParams({
                            start_date: this.startDate,
                            end_date: this.endDate
                        });
                        if (locationId) params.append('location_id', locationId);
                        if (departmentId) params.append('department_id', departmentId);
                        if (roleId) params.append('business_role_id', roleId);

                        const response = await fetch(`{{ route("schedule.draft-count") }}?${params.toString()}`, {
                            headers: { 'Accept': 'application/json' }
                        });

                        const data = await response.json();
                        this.draftCount = data.count;
                    } catch (error) {
                        console.error('Failed to refresh draft count:', error);
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
                    // Check if current user is still valid for new role
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

                    // Create new shift - called when clicking empty cell
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
                            // Find user's first role for this location/department
                            const user = modalData.users.find(u => u.id == userId);
                            if (user && user.role_ids.length > 0) {
                                // Try to find a role in the given department
                                const deptRoles = modalData.roles.filter(r => r.department_id == departmentId);
                                const userDeptRole = deptRoles.find(r => user.role_ids.includes(r.id));
                                roleId = userDeptRole ? userDeptRole.id : user.role_ids[0];
                            }
                        } else {
                            // For unassigned shifts, select the first role in the department
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

                            // Parse date - handle ISO format (2026-01-21T00:00:00.000000Z) to YYYY-MM-DD
                            const dateStr = shift.date.includes('T') ? shift.date.split('T')[0] : shift.date;

                            // Store original values for detecting changes
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

                            if (isCreate) {
                                // Add new shift to DOM
                                this.addShiftToDom(data.shift);
                            } else {
                                // Calculate hour difference for stats update
                                const oldHours = calculateShiftHours(this.originalStartTime, this.originalEndTime);
                                const newHours = calculateShiftHours(this.shift.start_time, this.shift.end_time);
                                const hoursDiff = newHours - oldHours;

                                // Check if assignment changed (for unassigned count)
                                const wasUnassigned = !this.originalUserId;
                                const isUnassigned = !this.shift.user_id;
                                let unassignedDiff = 0;
                                if (wasUnassigned && !isUnassigned) {
                                    unassignedDiff = -1; // Was unassigned, now assigned
                                } else if (!wasUnassigned && isUnassigned) {
                                    unassignedDiff = 1; // Was assigned, now unassigned
                                }

                                // Update stats if hours or assignment changed
                                if (hoursDiff !== 0 || unassignedDiff !== 0) {
                                    window.dispatchEvent(new CustomEvent('stats-change', {
                                        detail: { shifts: 0, hours: hoursDiff, unassigned: unassignedDiff }
                                    }));
                                }

                                // Check if user or date changed
                                const userChanged = this.shift.user_id != this.originalUserId;
                                const dateChanged = this.shift.date !== this.originalDate;

                                if (userChanged || dateChanged) {
                                    // Move shift block to new cell
                                    this.moveShiftInDom(data.shift, this.originalUserId, this.originalDate);
                                } else {
                                    // Just update in place
                                    this.updateShiftInDom(data.shift);
                                }
                            }
                            this.close();
                        } catch (error) {
                            this.error = error.message || 'Failed to save shift';
                        } finally {
                            this.saving = false;
                        }
                    },

                    async deleteShift() {
                        this.deleting = true;
                        this.error = null;

                        // Store info before delete since we'll need it after
                        const wasDraft = this.shift.status === 'draft';
                        const wasUnassigned = !this.shift.user_id;
                        const shiftHours = calculateShiftHours(this.shift.start_time, this.shift.end_time);

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

                            // Remove from DOM (without draft count handling - we do it here)
                            this.removeShiftFromDomOnly(this.shiftId);

                            // Decrement draft count if this was a draft shift
                            if (wasDraft) {
                                window.dispatchEvent(new CustomEvent('draft-count-change', { detail: { delta: -1 } }));
                            }

                            // Update stats
                            window.dispatchEvent(new CustomEvent('stats-change', {
                                detail: { shifts: -1, hours: -shiftHours, unassigned: wasUnassigned ? -1 : 0 }
                            }));

                            this.close();
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

                            // Update local state
                            this.shift.status = 'published';

                            // Update DOM - remove draft styling
                            const shiftBlock = document.querySelector(`[data-shift-id="${this.shiftId}"]`);
                            if (shiftBlock) {
                                shiftBlock.classList.remove('is-draft');
                                shiftBlock.dataset.status = 'published';
                                const draftLabel = shiftBlock.querySelector('.text-\\[10px\\]');
                                if (draftLabel) draftLabel.remove();
                            }

                            // Decrement draft count
                            window.dispatchEvent(new CustomEvent('draft-count-change', { detail: { delta: -1 } }));

                            this.close();
                        } catch (error) {
                            this.error = error.message || 'Failed to publish shift';
                        } finally {
                            this.publishing = false;
                        }
                    },

                    updateShiftInDom(shift) {
                        const shiftBlock = document.querySelector(`[data-shift-id="${shift.id}"]`);
                        if (shiftBlock) {
                            const timesEl = shiftBlock.querySelector('.shift-times');
                            const roleEl = shiftBlock.querySelector('.shift-role');

                            // Update data attributes for time
                            shiftBlock.dataset.startTime = shift.start_time.substring(0, 5);
                            shiftBlock.dataset.endTime = shift.end_time.substring(0, 5);

                            if (timesEl) {
                                timesEl.textContent = `${shift.start_time.substring(0, 5)} - ${shift.end_time.substring(0, 5)}`;
                            }
                            if (roleEl && shift.business_role) {
                                roleEl.textContent = shift.business_role.name;
                                // Update block color to match the role
                                if (shift.business_role.color) {
                                    shiftBlock.style.backgroundColor = shift.business_role.color;
                                }
                            }
                        }
                    },

                    moveShiftInDom(shift, originalUserId, originalDate) {
                        const shiftBlock = document.querySelector(`[data-shift-id="${shift.id}"]`);
                        if (!shiftBlock) return;

                        const originalCell = shiftBlock.closest('.schedule-cell');
                        const dateStr = shift.date.includes('T') ? shift.date.split('T')[0] : shift.date;
                        const isTargetUnassigned = !shift.user_id;
                        const isOriginalUnassigned = !originalUserId;

                        // Find target cell - for unassigned, user_id is empty string
                        const targetUserIdSelector = isTargetUnassigned ? '' : shift.user_id;
                        const targetCell = document.querySelector(`.schedule-cell[data-user-id="${targetUserIdSelector}"][data-date="${dateStr}"]`);

                        // If target cell doesn't exist (user not visible or date not in current week), just remove the shift
                        if (!targetCell) {
                            this.removeShiftFromDom(shift.id);
                            return;
                        }

                        // Update shift block content
                        const timesEl = shiftBlock.querySelector('.shift-times');
                        const roleEl = shiftBlock.querySelector('.shift-role');

                        if (timesEl) {
                            timesEl.textContent = `${shift.start_time.substring(0, 5)} - ${shift.end_time.substring(0, 5)}`;
                        }
                        if (roleEl && shift.business_role) {
                            roleEl.textContent = shift.business_role.name;
                        }
                        if (shift.business_role?.color) {
                            shiftBlock.style.backgroundColor = shift.business_role.color;
                        }

                        // Update data attributes
                        shiftBlock.dataset.userId = shift.user_id ?? '';
                        shiftBlock.dataset.date = dateStr;
                        shiftBlock.dataset.startTime = shift.start_time.substring(0, 5);
                        shiftBlock.dataset.endTime = shift.end_time.substring(0, 5);

                        // Update styling based on whether moving to/from unassigned
                        if (isTargetUnassigned) {
                            shiftBlock.classList.add('border', 'border-amber-500/30');
                        } else {
                            shiftBlock.classList.remove('border', 'border-amber-500/30');
                        }

                        // Handle target cell
                        if (isTargetUnassigned) {
                            // For unassigned row, use space-y-1 container
                            let shiftContainer = targetCell.querySelector('.space-y-1');
                            if (!shiftContainer) {
                                const placeholder = targetCell.querySelector('.add-shift-btn');
                                if (placeholder) {
                                    placeholder.remove();
                                }
                                shiftContainer = document.createElement('div');
                                shiftContainer.className = 'space-y-1';
                                targetCell.appendChild(shiftContainer);
                            }
                            shiftContainer.appendChild(shiftBlock);
                        } else {
                            // Remove placeholder from target cell if it exists
                            const targetPlaceholder = targetCell.querySelector('.add-shift-btn');
                            if (targetPlaceholder) {
                                targetPlaceholder.remove();
                            }
                            targetCell.appendChild(shiftBlock);
                        }

                        // Handle original cell - add placeholder if no shifts remain
                        const origUserId = originalCell.dataset.userId || null;
                        const origDateStr = originalCell.dataset.date;
                        const origLocationId = originalCell.dataset.locationId;
                        const origDepartmentId = originalCell.dataset.departmentId;

                        if (isOriginalUnassigned) {
                            const remainingShifts = originalCell.querySelectorAll('.shift-block');
                            if (remainingShifts.length === 0) {
                                const container = originalCell.querySelector('.space-y-1');
                                if (container) {
                                    container.remove();
                                }
                                const placeholder = document.createElement('div');
                                placeholder.className = 'add-shift-btn h-full min-h-[44px] border-2 border-dashed border-amber-700/50 rounded-lg flex items-center justify-center cursor-pointer hover:border-amber-500 hover:bg-amber-500/10 transition-colors';
                                placeholder.innerHTML = `
                                    <svg class="w-5 h-5 text-amber-600/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                `;
                                placeholder.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    window.Alpine.evaluate(document.querySelector('[x-data]'), `editModal.create(null, '${origDateStr}', ${origLocationId}, ${origDepartmentId})`);
                                });
                                originalCell.appendChild(placeholder);
                            }
                        } else {
                            const placeholder = document.createElement('div');
                            placeholder.className = 'add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors';
                            placeholder.innerHTML = `
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            `;
                            placeholder.addEventListener('click', (e) => {
                                e.stopPropagation();
                                window.Alpine.evaluate(document.querySelector('[x-data]'), `editModal.create(${origUserId}, '${origDateStr}', ${origLocationId}, ${origDepartmentId})`);
                            });
                            originalCell.appendChild(placeholder);
                        }

                        // Update unassigned count
                        this.updateUnassignedCount();
                    },

                    // Remove shift from DOM without draft count handling
                    removeShiftFromDomOnly(shiftId) {
                        const shiftBlock = document.querySelector(`[data-shift-id="${shiftId}"]`);
                        if (shiftBlock) {
                            const cell = shiftBlock.closest('.schedule-cell');
                            const isUnassigned = shiftBlock.dataset.userId === '';
                            shiftBlock.remove();

                            // Get cell data for the click handler
                            const userId = cell.dataset.userId || null;
                            const dateStr = cell.dataset.date;
                            const locationId = cell.dataset.locationId;
                            const departmentId = cell.dataset.departmentId;

                            // For unassigned row, only add placeholder if no shifts remain
                            if (isUnassigned) {
                                const remainingShifts = cell.querySelectorAll('.shift-block');
                                if (remainingShifts.length === 0) {
                                    const container = cell.querySelector('.space-y-1');
                                    if (container) {
                                        container.remove();
                                    }
                                    const placeholder = document.createElement('div');
                                    placeholder.className = 'add-shift-btn h-full min-h-[44px] border-2 border-dashed border-amber-700/50 rounded-lg flex items-center justify-center cursor-pointer hover:border-amber-500 hover:bg-amber-500/10 transition-colors';
                                    placeholder.innerHTML = `
                                        <svg class="w-5 h-5 text-amber-600/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    `;
                                    placeholder.addEventListener('click', (e) => {
                                        e.stopPropagation();
                                        window.Alpine.evaluate(document.querySelector('[x-data]'), `editModal.create(null, '${dateStr}', ${locationId}, ${departmentId})`);
                                    });
                                    cell.appendChild(placeholder);
                                }
                                // Update unassigned count
                                this.updateUnassignedCount();
                            } else {
                                // Add empty placeholder for assigned rows
                                const placeholder = document.createElement('div');
                                placeholder.className = 'add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors';
                                placeholder.innerHTML = `
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                `;
                                placeholder.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    window.Alpine.evaluate(document.querySelector('[x-data]'), `editModal.create(${userId}, '${dateStr}', ${locationId}, ${departmentId})`);
                                });
                                cell.appendChild(placeholder);
                            }
                        }
                    },

                    // Remove shift from DOM with draft count and stats handling (for drag-drop moves outside visible area)
                    removeShiftFromDom(shiftId) {
                        const shiftBlock = document.querySelector(`[data-shift-id="${shiftId}"]`);
                        if (!shiftBlock) return;

                        const wasDraft = shiftBlock.dataset.status === 'draft';
                        const wasUnassigned = shiftBlock.dataset.userId === '';
                        const startTime = shiftBlock.dataset.startTime || '00:00';
                        const endTime = shiftBlock.dataset.endTime || '00:00';
                        const shiftHours = calculateShiftHours(startTime, endTime);

                        this.removeShiftFromDomOnly(shiftId);

                        if (wasDraft) {
                            window.dispatchEvent(new CustomEvent('draft-count-change', { detail: { delta: -1 } }));
                        }

                        // Update stats
                        window.dispatchEvent(new CustomEvent('stats-change', {
                            detail: { shifts: -1, hours: -shiftHours, unassigned: wasUnassigned ? -1 : 0 }
                        }));
                    },

                    addShiftToDom(shift) {
                        const dateStr = shift.date.includes('T') ? shift.date.split('T')[0] : shift.date;
                        const isUnassigned = !shift.user_id;
                        const isDraft = shift.status === 'draft';

                        // Find the correct cell - for unassigned, user_id is empty string
                        const userIdSelector = isUnassigned ? '' : shift.user_id;
                        const cell = document.querySelector(`.schedule-cell[data-user-id="${userIdSelector}"][data-date="${dateStr}"]`);
                        if (!cell) return;

                        // Get role color or default (amber for unassigned)
                        const roleColor = shift.business_role?.color || (isUnassigned ? '#f59e0b' : '#6366f1');

                        // Create shift block
                        const shiftBlock = document.createElement('div');
                        shiftBlock.className = 'shift-block text-white rounded-lg p-2 text-xs cursor-move transition-colors hover:brightness-110';
                        if (isUnassigned) {
                            shiftBlock.classList.add('border', 'border-amber-500/30');
                        }
                        if (isDraft) {
                            shiftBlock.classList.add('is-draft');
                        }
                        shiftBlock.style.backgroundColor = roleColor;
                        shiftBlock.dataset.shiftId = shift.id;
                        shiftBlock.dataset.userId = shift.user_id ?? '';
                        shiftBlock.dataset.date = dateStr;
                        shiftBlock.dataset.status = shift.status;
                        shiftBlock.dataset.startTime = shift.start_time.substring(0, 5);
                        shiftBlock.dataset.endTime = shift.end_time.substring(0, 5);
                        shiftBlock.draggable = true;

                        const draftLabel = isDraft ? '<div class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mb-0.5">Draft</div>' : '';
                        shiftBlock.innerHTML = `
                            ${draftLabel}
                            <div class="shift-times font-medium">${shift.start_time.substring(0, 5)} - ${shift.end_time.substring(0, 5)}</div>
                            <div class="shift-role truncate" style="color: rgba(255,255,255,0.85);">${shift.business_role?.name || 'No role'}</div>
                        `;

                        // Increment draft count if this is a draft shift
                        if (isDraft) {
                            window.dispatchEvent(new CustomEvent('draft-count-change', { detail: { delta: 1 } }));
                        }

                        // Update stats
                        const shiftHours = calculateShiftHours(shift.start_time.substring(0, 5), shift.end_time.substring(0, 5));
                        window.dispatchEvent(new CustomEvent('stats-change', {
                            detail: { shifts: 1, hours: shiftHours, unassigned: isUnassigned ? 1 : 0 }
                        }));

                        // Add event listeners
                        shiftBlock.addEventListener('dragstart', (e) => {
                            window.Alpine.evaluate(document.querySelector('[x-data]'), `handleDragStart($event, ${shift.id})`);
                        });
                        shiftBlock.addEventListener('dragend', (e) => {
                            window.Alpine.evaluate(document.querySelector('[x-data]'), 'handleDragEnd($event)');
                        });
                        shiftBlock.addEventListener('click', (e) => {
                            e.stopPropagation();
                            window.Alpine.evaluate(document.querySelector('[x-data]'), `editModal.open(${shift.id})`);
                        });

                        if (isUnassigned) {
                            // For unassigned row, use space-y-1 container
                            let shiftContainer = cell.querySelector('.space-y-1');
                            if (!shiftContainer) {
                                // Remove placeholder if present
                                const placeholder = cell.querySelector('.add-shift-btn');
                                if (placeholder) {
                                    placeholder.remove();
                                }
                                shiftContainer = document.createElement('div');
                                shiftContainer.className = 'space-y-1';
                                cell.appendChild(shiftContainer);
                            }
                            shiftContainer.appendChild(shiftBlock);

                            // Update unassigned count
                            const app = window.Alpine.evaluate(document.querySelector('[x-data]'), 'this');
                            if (app && app.updateUnassignedCount) {
                                app.updateUnassignedCount();
                            }
                        } else {
                            // Remove placeholder if present
                            const placeholder = cell.querySelector('.add-shift-btn');
                            if (placeholder) {
                                placeholder.remove();
                            }
                            cell.appendChild(shiftBlock);
                        }
                    }
                },

                init() {
                    this.initFilters();
                },

                // Drag and Drop handlers
                handleDragStart(event, shiftId) {
                    this.draggedShiftId = shiftId;
                    this.draggedElement = event.target;
                    event.target.classList.add('dragging');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', shiftId);
                },

                handleDragEnd(event) {
                    event.target.classList.remove('dragging');
                    this.draggedShiftId = null;
                    this.draggedElement = null;

                    // Remove all drag-over classes
                    document.querySelectorAll('.drag-over').forEach(el => {
                        el.classList.remove('drag-over');
                    });
                },

                handleDragEnter(event) {
                    const cell = event.currentTarget;
                    cell.classList.add('drag-over');
                },

                handleDragLeave(event) {
                    const cell = event.currentTarget;
                    // Only remove if we're leaving the cell entirely
                    if (!cell.contains(event.relatedTarget)) {
                        cell.classList.remove('drag-over');
                    }
                },

                async handleDrop(event, userId, date) {
                    event.preventDefault();
                    const cell = event.currentTarget;
                    cell.classList.remove('drag-over');

                    if (!this.draggedShiftId || !this.draggedElement) {
                        return;
                    }

                    const shiftId = this.draggedShiftId;
                    const originalElement = this.draggedElement;
                    const originalCell = originalElement.closest('.schedule-cell');
                    const originalUserId = originalElement.dataset.userId || null;
                    const originalDate = originalElement.dataset.date;

                    // Normalize userId comparison (handle empty string as null)
                    const normalizedOriginalUserId = originalUserId === '' ? null : originalUserId;
                    const normalizedTargetUserId = userId === '' ? null : userId;

                    // Don't do anything if dropped on the same cell
                    if (normalizedOriginalUserId == normalizedTargetUserId && originalDate === date) {
                        return;
                    }

                    // Check if target is unassigned row
                    const isTargetUnassigned = normalizedTargetUserId === null;

                    try {
                        const response = await fetch(`/shifts/${shiftId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                user_id: normalizedTargetUserId,
                                date: date,
                                start_time: originalElement.dataset.startTime,
                                end_time: originalElement.dataset.endTime,
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            // Extract validation error message
                            let errorMessage = data.message || 'Failed to move shift';
                            if (data.errors) {
                                const firstError = Object.values(data.errors)[0];
                                if (Array.isArray(firstError)) {
                                    errorMessage = firstError[0];
                                }
                            }
                            throw new Error(errorMessage);
                        }

                        // Move the element in the DOM
                        const isOriginalUnassigned = normalizedOriginalUserId === null;

                        // Update the element's data attributes
                        originalElement.dataset.userId = normalizedTargetUserId ?? '';
                        originalElement.dataset.date = date;

                        // Update styling based on whether moving to/from unassigned
                        if (isTargetUnassigned) {
                            originalElement.classList.add('border', 'border-amber-500/30');
                        } else {
                            originalElement.classList.remove('border', 'border-amber-500/30');
                        }

                        // Append to the space-y-1 container or create one
                        let shiftContainer = cell.querySelector('.space-y-1');
                        if (!shiftContainer) {
                            // Remove placeholder if present
                            const placeholder = cell.querySelector('.add-shift-btn');
                            if (placeholder) {
                                placeholder.remove();
                            }
                            shiftContainer = document.createElement('div');
                            shiftContainer.className = 'space-y-1';
                            cell.appendChild(shiftContainer);
                        }

                        // Insert shift before the "add shift" button if it exists
                        const addBtn = shiftContainer.querySelector('.add-shift-btn');
                        if (addBtn) {
                            shiftContainer.insertBefore(originalElement, addBtn);
                        } else {
                            shiftContainer.appendChild(originalElement);
                        }

                        // Handle original cell - add placeholder if no shifts remain
                        const dropOrigUserId = originalCell.dataset.userId || null;
                        const dropOrigDateStr = originalCell.dataset.date;
                        const dropOrigLocationId = originalCell.dataset.locationId;
                        const dropOrigDepartmentId = originalCell.dataset.departmentId;

                        // Check if there are remaining shifts in the original cell
                        const remainingShifts = originalCell.querySelectorAll('.shift-block');
                        if (remainingShifts.length === 0) {
                            // No shifts remain - remove container and add placeholder
                            const container = originalCell.querySelector('.space-y-1');
                            if (container) {
                                container.remove();
                            }

                            const placeholder = document.createElement('div');
                            if (isOriginalUnassigned) {
                                placeholder.className = 'add-shift-btn h-full min-h-[44px] border-2 border-dashed border-amber-700/50 rounded-lg flex items-center justify-center cursor-pointer hover:border-amber-500 hover:bg-amber-500/10 transition-colors';
                                placeholder.innerHTML = `
                                    <svg class="w-5 h-5 text-amber-600/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                `;
                                placeholder.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    window.Alpine.evaluate(document.querySelector('[x-data]'), `editModal.create(null, '${dropOrigDateStr}', ${dropOrigLocationId}, ${dropOrigDepartmentId})`);
                                });
                            } else {
                                placeholder.className = 'add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center cursor-pointer hover:border-brand-500 hover:bg-brand-500/10 transition-colors';
                                placeholder.innerHTML = `
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                `;
                                placeholder.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    window.Alpine.evaluate(document.querySelector('[x-data]'), `editModal.create(${dropOrigUserId}, '${dropOrigDateStr}', ${dropOrigLocationId}, ${dropOrigDepartmentId})`);
                                });
                            }
                            originalCell.appendChild(placeholder);
                        }

                        // Update unassigned count in header
                        this.updateUnassignedCount();

                    } catch (error) {
                        console.error('Failed to move shift:', error);
                        this.showError(error.message || 'Failed to move shift. The employee may not have the required role.', 'Cannot Move Shift');
                    }
                },

                updateUnassignedCount() {
                    const unassignedRow = document.querySelector('.unassigned-row');
                    if (unassignedRow) {
                        const shiftCount = unassignedRow.querySelectorAll('.shift-block').length;
                        const countEl = unassignedRow.querySelector('.text-xs.text-amber-500\\/70');
                        if (countEl) {
                            countEl.textContent = `${shiftCount} ${shiftCount === 1 ? 'shift' : 'shifts'}`;
                        }
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

                        // Show/hide unassigned row based on whether any unassigned shifts are visible or no filters applied
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
                                    filter_context: 'schedule',
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
                            const response = await fetch('{{ route("user.filter-defaults.show") }}?filter_context=schedule', {
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
