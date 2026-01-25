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
    </style>

    @php
        $numHours = count($hours);
        $defaultColors = ['#6366f1', '#8b5cf6', '#06b6d4', '#22c55e', '#f59e0b', '#14b8a6', '#f43f5e', '#f97316', '#3b82f6', '#ec4899'];
        $colorIndex = 0;
    @endphp

    <div x-data="scheduleDayApp()" x-init="init()">
        <!-- Top Header -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Previous Day -->
                        <a href="{{ route('schedule.day', ['date' => $selectedDate->copy()->subDay()->format('Y-m-d')]) }}"
                           class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-xl font-bold text-white">{{ $selectedDate->format('l, F j, Y') }}</h1>
                            <p class="text-sm text-gray-500">{{ $selectedDate->isToday() ? 'Today' : $selectedDate->diffForHumans() }}</p>
                        </div>
                        <!-- Next Day -->
                        <a href="{{ route('schedule.day', ['date' => $selectedDate->copy()->addDay()->format('Y-m-d')]) }}"
                           class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <!-- Today Button -->
                        @if(!$selectedDate->isToday())
                            <a href="{{ route('schedule.day') }}"
                               class="px-3 py-1.5 text-sm font-medium text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                                Today
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-800 rounded-lg p-1">
                            <a href="{{ route('schedule.day', ['date' => $selectedDate->format('Y-m-d')]) }}"
                               class="px-3 py-1.5 text-sm font-medium text-white bg-brand-900 rounded-md">Day</a>
                            <a href="{{ route('schedule.index', ['start' => $selectedDate->copy()->startOfWeek()->format('Y-m-d')]) }}"
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
                <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Location:</label>
                        <select id="filter-location" class="text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
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
                            <div class="relative h-16 bg-gray-800/20">
                                @foreach($unassignedShifts as $shift)
                                    @php
                                        $startHour = $shift->start_time->hour + ($shift->start_time->minute / 60);
                                        $endHour = $shift->end_time->hour + ($shift->end_time->minute / 60);
                                        if ($endHour < $startHour) $endHour += 24; // Overnight
                                        $leftPercent = (($startHour - $dayStartHour) / $numHours) * 100;
                                        $widthPercent = (($endHour - $startHour) / $numHours) * 100;
                                        $leftPercent = max(0, min(100, $leftPercent));
                                        $widthPercent = max(0, min(100 - $leftPercent, $widthPercent));
                                    @endphp
                                    <div class="shift-bar unassigned-shift absolute top-2 bottom-2 rounded-lg p-2 text-xs text-white cursor-pointer hover:brightness-110 transition-colors border border-amber-500/30 {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                         style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $shift->businessRole?->color ?? '#f59e0b' }};"
                                         data-shift-id="{{ $shift->id }}"
                                         data-location-id="{{ $shift->location_id }}"
                                         data-department-id="{{ $shift->department_id }}"
                                         data-role-id="{{ $shift->business_role_id }}"
                                         @click="editModal.open({{ $shift->id }})">
                                        <div class="flex items-center justify-between h-full">
                                            <div class="truncate">
                                                @if($shift->isDraft())
                                                    <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mr-1">Draft</span>
                                                @endif
                                                <span class="font-medium">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                            </div>
                                            <span class="text-white/60 ml-2">{{ $shift->duration_hours }} hrs</span>
                                        </div>
                                        <div class="truncate text-white/80 text-[10px]">{{ $shift->businessRole?->name ?? 'No role' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @forelse($departments as $department)
                        @php
                            $deptUsers = $usersByDepartment->get($department->id, collect());
                            $deptColor = $department->color ?? '#6366f1';
                        @endphp

                        @if($deptUsers->isNotEmpty())
                            <!-- Department Header -->
                            <div class="department-header border-b px-4 py-2"
                                 data-department-id="{{ $department->id }}"
                                 data-location-id="{{ $department->location_id }}"
                                 style="background-color: {{ $deptColor }}20; border-color: {{ $deptColor }}40;">
                                <span class="text-sm font-semibold" style="color: {{ $deptColor }};">{{ $department->name }}</span>
                                <span class="dept-count text-xs ml-2" style="color: {{ $deptColor }}80;">{{ $deptUsers->count() }} {{ Str::plural('employee', $deptUsers->count()) }}</span>
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
                                    <div class="relative h-16 schedule-cell cursor-pointer hover:bg-gray-800/30 transition-colors"
                                         data-user-id="{{ $user->id }}"
                                         data-date="{{ $selectedDate->format('Y-m-d') }}"
                                         data-location-id="{{ $department->location_id }}"
                                         data-department-id="{{ $department->id }}"
                                         @click.self="editModal.create({{ $user->id }}, '{{ $selectedDate->format('Y-m-d') }}', {{ $department->location_id }}, {{ $department->id }})">

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
                                                @endphp
                                                <div class="shift-bar absolute top-2 bottom-2 rounded-lg p-2 text-xs text-white cursor-pointer hover:brightness-110 transition-colors {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                                     style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $shift->businessRole?->color ?? $userColor }};"
                                                     data-shift-id="{{ $shift->id }}"
                                                     @click.stop="editModal.open({{ $shift->id }})">
                                                    <div class="flex items-center justify-between h-full">
                                                        <div class="truncate">
                                                            @if($shift->isDraft())
                                                                <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mr-1">Draft</span>
                                                            @endif
                                                            <span class="font-medium">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                        </div>
                                                        <span class="text-white/60 ml-2">{{ $shift->duration_hours }} hrs</span>
                                                    </div>
                                                    <div class="truncate text-white/80 text-[10px]">{{ $shift->businessRole?->name ?? 'No role' }}</div>
                                                </div>
                                            @endforeach

                                            @if(count($userShifts) === 0)
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
                                <div class="relative h-16 schedule-cell cursor-pointer hover:bg-gray-800/30 transition-colors"
                                     data-user-id="{{ $user->id }}"
                                     data-date="{{ $selectedDate->format('Y-m-d') }}"
                                     @click.self="editModal.create({{ $user->id }}, '{{ $selectedDate->format('Y-m-d') }}', {{ $locations->first()?->id ?? 'null' }}, {{ $departments->first()?->id ?? 'null' }})">

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
                                        @endphp
                                        <div class="shift-bar absolute top-2 bottom-2 rounded-lg p-2 text-xs text-white cursor-pointer hover:brightness-110 transition-colors {{ $shift->isDraft() ? 'is-draft' : '' }}"
                                             style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $shift->businessRole?->color ?? '#4b5563' }};"
                                             data-shift-id="{{ $shift->id }}"
                                             @click.stop="editModal.open({{ $shift->id }})">
                                            <div class="flex items-center justify-between h-full">
                                                <div class="truncate">
                                                    @if($shift->isDraft())
                                                        <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wide mr-1">Draft</span>
                                                    @endif
                                                    <span class="font-medium">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</span>
                                                </div>
                                                <span class="text-white/60 ml-2">{{ $shift->duration_hours }} hrs</span>
                                            </div>
                                            <div class="truncate text-white/80 text-[10px]">{{ $shift->businessRole?->name ?? 'No role' }}</div>
                                        </div>
                                    @endforeach

                                    @if(count($userShifts) === 0)
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
                    const searchFilter = document.getElementById('filter-search');

                    const allDepartmentOptions = Array.from(departmentFilter.querySelectorAll('option'));
                    const allRoleOptions = Array.from(roleFilter.querySelectorAll('option'));

                    const filterEmployees = () => {
                        const selectedLocationId = locationFilter.value;
                        const selectedDepartmentId = departmentFilter.value;
                        const selectedRoleId = roleFilter.value;
                        const searchTerm = searchFilter.value.toLowerCase().trim();

                        const employeeRows = document.querySelectorAll('.employee-row');
                        const departmentHeaders = document.querySelectorAll('.department-header');

                        const visibleByDept = {};

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

                            if (!visibleByDept[rowDepartmentId]) {
                                visibleByDept[rowDepartmentId] = 0;
                            }
                            if (visible) {
                                visibleByDept[rowDepartmentId]++;
                            }
                        });

                        departmentHeaders.forEach(header => {
                            const deptId = header.dataset.departmentId;
                            const locationId = header.dataset.locationId;
                            const visibleCount = visibleByDept[deptId] || 0;

                            let showHeader = visibleCount > 0;

                            if (selectedLocationId && locationId !== selectedLocationId) {
                                showHeader = false;
                            }

                            header.style.display = showHeader ? '' : 'none';

                            const countSpan = header.querySelector('.dept-count');
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
                    searchFilter.addEventListener('input', filterEmployees);
                }
            };
        }
    </script>
</x-layouts.app>
