<x-layouts.app title="Dashboard">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Employees</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['total_employees'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">On Duty Today</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['on_duty_today'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Pending Leave Requests</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">{{ $stats['pending_leave_requests'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Unassigned Shifts</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-orange-600">{{ $stats['unassigned_shifts'] }}</dd>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900">Today's Schedule</h3>
                <div class="mt-6 flow-root">
                    @if($todayShifts->isEmpty())
                        <p class="text-sm text-gray-500">No shifts scheduled for today.</p>
                    @else
                        <ul role="list" class="-my-5 divide-y divide-gray-200">
                            @foreach($todayShifts as $shift)
                                <li class="py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-gray-900">
                                                {{ $shift->user?->full_name ?? 'Unassigned' }}
                                            </p>
                                            <p class="truncate text-sm text-gray-500">
                                                {{ $shift->department->name }} - {{ $shift->businessRole->name }}
                                            </p>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="mt-6">
                    <a href="{{ route('schedule.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        View schedule<span aria-hidden="true"> &rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900">Pending Leave Requests</h3>
                <div class="mt-6 flow-root">
                    @if($pendingLeave->isEmpty())
                        <p class="text-sm text-gray-500">No pending leave requests.</p>
                    @else
                        <ul role="list" class="-my-5 divide-y divide-gray-200">
                            @foreach($pendingLeave as $leave)
                                <li class="py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-gray-900">
                                                {{ $leave->user->full_name }}
                                            </p>
                                            <p class="truncate text-sm text-gray-500">
                                                {{ $leave->leaveType->name }} - {{ $leave->total_days }} days
                                            </p>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d') }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="mt-6">
                    <a href="{{ route('leave-requests.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Review all requests<span aria-hidden="true"> &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
