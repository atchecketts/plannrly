<x-layouts.app title="Overtime Report">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-2">
                        <a href="{{ route('reports.attendance.index') }}" class="hover:text-white">Attendance Reports</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-white">Overtime</span>
                    </nav>
                    <h2 class="text-lg font-semibold text-white">Overtime Report</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.attendance.export', ['type' => 'overtime'] + request()->query()) }}"
                       class="border border-gray-700 text-gray-300 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                        Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    @include('reports.attendance._filters', ['route' => 'reports.attendance.overtime'])

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Total Overtime Hours</p>
            <p class="text-2xl font-bold text-orange-400">{{ $report['summary']['hours'] }}h</p>
        </div>
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-4">
            <p class="text-sm text-gray-400">Overtime Entries</p>
            <p class="text-2xl font-bold text-white">{{ $report['summary']['entries'] }}</p>
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
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-300 uppercase">Overtime Hours</th>
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
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm">
                                <span class="inline-flex items-center rounded-md bg-orange-500/10 px-2 py-1 text-xs font-medium text-orange-400 ring-1 ring-inset ring-orange-500/20">
                                    {{ $row['overtime_hours'] }}h
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-400">
                                {{ $row['overtime_entries'] }}
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <p class="text-gray-500">No overtime entries found for this period.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
