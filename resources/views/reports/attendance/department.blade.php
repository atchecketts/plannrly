<x-layouts.app title="Department Attendance Report">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-2">
                        <a href="{{ route('reports.attendance.index') }}" class="hover:text-white">Attendance Reports</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-white">{{ $department->name }}</span>
                    </nav>
                    <h2 class="text-lg font-semibold text-white">{{ $department->name }} - Attendance Report</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.attendance.index', ['department_id' => $department->id] + request()->only(['start_date', 'end_date'])) }}"
                       class="border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                        Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($report))
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
                <p class="text-sm text-gray-400">Attendance Rate</p>
                <p class="text-2xl font-bold {{ $report['attendance']['rate'] >= 90 ? 'text-green-400' : ($report['attendance']['rate'] >= 75 ? 'text-yellow-400' : 'text-red-400') }}">
                    {{ $report['attendance']['rate'] }}%
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $report['attendance']['worked'] }}/{{ $report['attendance']['scheduled'] }} shifts
                </p>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
                <p class="text-sm text-gray-400">Punctuality Rate</p>
                <p class="text-2xl font-bold {{ $report['punctuality']['rate'] >= 90 ? 'text-green-400' : ($report['punctuality']['rate'] >= 75 ? 'text-yellow-400' : 'text-red-400') }}">
                    {{ $report['punctuality']['rate'] }}%
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $report['punctuality']['on_time'] }} on time, {{ $report['punctuality']['late'] }} late
                </p>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
                <p class="text-sm text-gray-400">Overtime</p>
                <p class="text-2xl font-bold text-orange-400">{{ $report['overtime']['hours'] }}h</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $report['overtime']['entries'] }} {{ Str::plural('entry', $report['overtime']['entries']) }}
                </p>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
                <p class="text-sm text-gray-400">Missed Shifts</p>
                <p class="text-2xl font-bold {{ $report['missed_shifts']['count'] > 0 ? 'text-red-400' : 'text-green-400' }}">
                    {{ $report['missed_shifts']['count'] }}
                </p>
            </div>
        </div>

        <!-- Hours Summary -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Hours Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <p class="text-sm text-gray-400">Scheduled Hours</p>
                    <p class="text-xl font-bold text-white">{{ $report['total_scheduled_hours'] }}h</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Hours Worked</p>
                    <p class="text-xl font-bold text-white">{{ $report['total_worked_hours'] }}h</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Undertime</p>
                    <p class="text-xl font-bold text-red-400">{{ $report['undertime']['hours'] }}h</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Average Variance</p>
                    @php $avgVariance = $report['average_variance_minutes']; @endphp
                    <p class="text-xl font-bold {{ $avgVariance > 0 ? 'text-orange-400' : ($avgVariance < 0 ? 'text-red-400' : 'text-green-400') }}">
                        @if($avgVariance !== null)
                            {{ $avgVariance >= 0 ? '+' : '' }}{{ $avgVariance }}m
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-12 text-center">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500">Department not found or no data available.</p>
        </div>
    @endif
</x-layouts.app>
