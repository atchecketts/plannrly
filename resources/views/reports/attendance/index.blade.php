<x-layouts.app title="Attendance Reports">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">Attendance Reports</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('reports.attendance.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex flex-col gap-1">
                    <label for="start_date" class="text-sm text-gray-400">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}"
                           class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                </div>

                <div class="flex flex-col gap-1">
                    <label for="end_date" class="text-sm text-gray-400">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}"
                           class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                </div>

                <div class="flex flex-col gap-1">
                    <label for="department_id" class="text-sm text-gray-400">Department</label>
                    <select name="department_id" id="department_id" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label for="location_id" class="text-sm text-gray-400">Location</label>
                    <select name="location_id" id="location_id" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-brand-900 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-brand-800 transition-colors">
                    Apply Filters
                </button>

                @if(request()->hasAny(['start_date', 'end_date', 'department_id', 'location_id']))
                    <a href="{{ route('reports.attendance.index') }}" class="text-sm text-gray-400 hover:text-white">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Attendance Rate -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Attendance Rate</p>
                    <p class="text-2xl font-bold {{ $summary['attendance']['rate'] >= 90 ? 'text-green-400' : ($summary['attendance']['rate'] >= 75 ? 'text-yellow-400' : 'text-red-400') }}">
                        {{ $summary['attendance']['rate'] }}%
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                {{ $summary['attendance']['worked'] }}/{{ $summary['attendance']['scheduled'] }} shifts
            </p>
        </div>

        <!-- Punctuality Rate -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Punctuality Rate</p>
                    <p class="text-2xl font-bold {{ $summary['punctuality']['rate'] >= 90 ? 'text-green-400' : ($summary['punctuality']['rate'] >= 75 ? 'text-yellow-400' : 'text-red-400') }}">
                        {{ $summary['punctuality']['rate'] }}%
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                {{ $summary['punctuality']['on_time'] }} on time, {{ $summary['punctuality']['late'] }} late
            </p>
        </div>

        <!-- Overtime Hours -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Overtime Hours</p>
                    <p class="text-2xl font-bold text-orange-400">
                        {{ $summary['overtime']['hours'] }}h
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                {{ $summary['overtime']['entries'] }} {{ Str::plural('entry', $summary['overtime']['entries']) }} with overtime
            </p>
        </div>

        <!-- Missed Shifts -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Missed Shifts</p>
                    <p class="text-2xl font-bold {{ $summary['missed_shifts'] > 0 ? 'text-red-400' : 'text-green-400' }}">
                        {{ $summary['missed_shifts'] }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                No-shows and missed shifts
            </p>
        </div>
    </div>

    <!-- Hours Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Hours Summary</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Scheduled Hours</span>
                    <span class="text-white font-medium">{{ $summary['total_scheduled_hours'] }}h</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Actual Hours Worked</span>
                    <span class="text-white font-medium">{{ $summary['total_hours_worked'] }}h</span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-800 pt-4">
                    <span class="text-gray-400">Variance</span>
                    @php
                        $variance = $summary['total_hours_worked'] - $summary['total_scheduled_hours'];
                    @endphp
                    <span class="font-medium {{ $variance > 0 ? 'text-orange-400' : ($variance < 0 ? 'text-red-400' : 'text-green-400') }}">
                        {{ $variance >= 0 ? '+' : '' }}{{ round($variance, 2) }}h
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Average Variance</span>
                    @php $avgVariance = $summary['average_variance_minutes']; @endphp
                    <span class="font-medium {{ $avgVariance > 0 ? 'text-orange-400' : ($avgVariance < 0 ? 'text-red-400' : 'text-green-400') }}">
                        @if($avgVariance !== null)
                            {{ $avgVariance >= 0 ? '+' : '' }}{{ $avgVariance }}m per shift
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Detailed Reports</h3>
            <div class="space-y-3">
                <a href="{{ route('reports.attendance.punctuality', request()->query()) }}" class="flex items-center justify-between p-3 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-500/20 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-white">Punctuality Report</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('reports.attendance.hours', request()->query()) }}" class="flex items-center justify-between p-3 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-500/20 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="text-white">Hours Worked Report</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('reports.attendance.overtime', request()->query()) }}" class="flex items-center justify-between p-3 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-500/20 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <span class="text-white">Overtime Report</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="{{ route('reports.attendance.absence', request()->query()) }}" class="flex items-center justify-between p-3 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-red-500/20 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-white">Absence Report</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
