<x-layouts.app title="Punctuality Report">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-2">
                        <a href="{{ route('reports.attendance.index') }}" class="hover:text-white">Attendance Reports</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-white">Punctuality</span>
                    </nav>
                    <h2 class="text-lg font-semibold text-white">Punctuality Report</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.attendance.export', ['type' => 'punctuality'] + request()->query()) }}"
                       class="border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                        Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    @include('reports.attendance._filters', ['route' => 'reports.attendance.punctuality'])

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Punctuality Rate</p>
            <p class="text-2xl font-bold {{ $report['summary']['rate'] >= 90 ? 'text-green-400' : ($report['summary']['rate'] >= 75 ? 'text-yellow-400' : 'text-red-400') }}">
                {{ $report['summary']['rate'] }}%
            </p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">On Time</p>
            <p class="text-2xl font-bold text-green-400">{{ $report['summary']['on_time'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Late</p>
            <p class="text-2xl font-bold text-red-400">{{ $report['summary']['late'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Early</p>
            <p class="text-2xl font-bold text-blue-400">{{ $report['summary']['early'] }}</p>
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
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">On Time</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Late</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Early</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Total</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Rate</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Avg Late</th>
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
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-green-400 font-medium">
                                {{ $row['on_time'] }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-red-400 font-medium">
                                {{ $row['late'] }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-blue-400 font-medium">
                                {{ $row['early'] }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-400">
                                {{ $row['total'] }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $row['punctuality_rate'] >= 90 ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                    {{ $row['punctuality_rate'] >= 75 && $row['punctuality_rate'] < 90 ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                    {{ $row['punctuality_rate'] < 75 ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}">
                                    {{ $row['punctuality_rate'] }}%
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-400">
                                @if($row['average_late_minutes'] > 0)
                                    {{ $row['average_late_minutes'] }}m
                                @else
                                    -
                                @endif
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500">No clock-in data found for this period.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
