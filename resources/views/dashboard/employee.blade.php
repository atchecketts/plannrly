<x-layouts.app title="Dashboard">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-gray-900">Upcoming Shifts</h3>
                    <div class="mt-6 flow-root">
                        @if($upcomingShifts->isEmpty())
                            <p class="text-sm text-gray-500">No upcoming shifts scheduled.</p>
                        @else
                            <ul role="list" class="-my-5 divide-y divide-gray-200">
                                @foreach($upcomingShifts as $shift)
                                    <li class="py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $shift->date->format('l, M d, Y') }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $shift->location->name }} - {{ $shift->businessRole->name }}
                                                </p>
                                            </div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">This Week</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $thisWeekHours }} hrs</dd>
            </div>

            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Pending Leave Requests</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">{{ $pendingLeave }}</dd>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-gray-900">Quick Actions</h3>
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('leave-requests.create') }}" class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Request Leave
                        </a>
                        <a href="{{ route('shift-swaps.index') }}" class="flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Swap Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
