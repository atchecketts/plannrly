<x-layouts.app title="Hours Worked Report">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-2">
                        <a href="{{ route('reports.attendance.index') }}" class="hover:text-white">Attendance Reports</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-white">Hours Worked</span>
                    </nav>
                    <h2 class="text-lg font-semibold text-white">Hours Worked Report</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.attendance.export', ['type' => 'hours'] + request()->query()) }}"
                       class="border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                        Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    @include('reports.attendance._filters', ['route' => 'reports.attendance.hours'])

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Total Scheduled</p>
            <p class="text-2xl font-bold text-white">{{ $report['summary']['total_scheduled_hours'] }}h</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Total Worked</p>
            <p class="text-2xl font-bold text-white">{{ $report['summary']['total_worked_hours'] }}h</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Variance</p>
            @php $variance = $report['summary']['variance_hours']; @endphp
            <p class="text-2xl font-bold {{ $variance > 0 ? 'text-orange-400' : ($variance < 0 ? 'text-red-400' : 'text-green-400') }}">
                {{ $variance >= 0 ? '+' : '' }}{{ $variance }}h
            </p>
        </div>
    </div>

    <!-- By Employee Table -->
    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800">
            <h3 class="text-lg font-semibold text-white">By Employee</h3>
        </div>

        @if($report['by_user']->isNotEmpty())
            <table class="min-w-full divide-y divide-gray-800">
                <thead>
                    <tr class="bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase">Employee</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Scheduled</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Actual</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Variance</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Entries</th>
                        <th class="relative py-3 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($report['by_user'] as $row)
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-brand-500/20 rounded-full flex items-center justify-center text-brand-400 text-xs font-medium">
                                        {{ substr($row['user']->first_name, 0, 1) }}{{ substr($row['user']->last_name, 0, 1) }}
                                    </div>
                                    <span class="text-sm text-white font-medium">{{ $row['user']->full_name }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-400">
                                {{ $row['scheduled_hours'] }}h
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-white font-medium">
                                {{ $row['actual_hours'] }}h
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm">
                                @php $rowVariance = $row['variance_hours']; @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $rowVariance >= 0.5 ? 'bg-orange-500/10 text-orange-400 ring-orange-500/20' : '' }}
                                    {{ $rowVariance > -0.5 && $rowVariance < 0.5 ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                    {{ $rowVariance <= -0.5 ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}">
                                    {{ $rowVariance >= 0 ? '+' : '' }}{{ $rowVariance }}h
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-400">
                                {{ $row['entry_count'] }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                <a href="{{ route('reports.attendance.employee', ['user' => $row['user']->id] + request()->query()) }}"
                                   class="text-brand-400 hover:text-brand-300">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-gray-500">No time entries found for this period.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
