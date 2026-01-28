<x-layouts.app title="{{ $user->full_name }}">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-semibold text-white">{{ $user->full_name }}</h2>
                <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:gap-x-6">
                    <p class="text-sm text-gray-400">{{ $user->email }}</p>
                    @if($user->phone)
                        <p class="text-sm text-gray-400">{{ $user->phone }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-4 flex gap-3 lg:ml-4 lg:mt-0">
                @can('update', $user)
                    <a href="{{ route('users.employment.edit', $user) }}" class="border border-gray-700 text-gray-300 py-2.5 px-4 rounded-lg font-medium hover:bg-gray-800 transition-colors">
                        Employment Details
                    </a>
                    <a href="{{ route('users.edit', $user) }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Edit
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-gray-900 rounded-lg border border-gray-800">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">System Roles</h3>
            </div>
            <div class="p-6">
                @if($user->roleAssignments->isEmpty())
                    <p class="text-sm text-gray-400">No roles assigned.</p>
                @else
                    <ul role="list" class="divide-y divide-gray-800">
                        @foreach($user->roleAssignments as $assignment)
                            <li class="py-3 first:pt-0 last:pb-0">
                                <p class="text-sm font-medium text-white">{{ $assignment->system_role->label() }}</p>
                                @if($assignment->location)
                                    <p class="text-xs text-gray-400">Location: {{ $assignment->location->name }}</p>
                                @endif
                                @if($assignment->department)
                                    <p class="text-xs text-gray-400">Department: {{ $assignment->department->name }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">Business Roles</h3>
            </div>
            <div class="p-6">
                @if($user->businessRoles->isEmpty())
                    <p class="text-sm text-gray-400">No business roles assigned.</p>
                @else
                    <ul role="list" class="divide-y divide-gray-800">
                        @foreach($user->businessRoles as $role)
                            <li class="py-3 first:pt-0 last:pb-0">
                                <p class="text-sm font-medium text-white">
                                    {{ $role->name }}
                                    @if($role->pivot->is_primary)
                                        <span class="ml-2 inline-flex items-center rounded bg-brand-500/10 px-2 py-0.5 text-xs font-medium text-brand-400">Primary</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $role->department->name }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Employment Details --}}
        @if($user->employmentDetails)
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-white">Employment Details</h3>
                    @can('update', $user)
                        <a href="{{ route('users.employment.edit', $user) }}" class="text-sm text-brand-400 hover:text-brand-300">Edit</a>
                    @endcan
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Status</dt>
                        <dd>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @switch($user->employmentDetails->employment_status->color())
                                    @case('green') bg-green-500/10 text-green-400 @break
                                    @case('blue') bg-blue-500/10 text-blue-400 @break
                                    @case('yellow') bg-yellow-500/10 text-yellow-400 @break
                                    @case('orange') bg-orange-500/10 text-orange-400 @break
                                    @case('red') bg-red-500/10 text-red-400 @break
                                    @default bg-gray-500/10 text-gray-400
                                @endswitch
                            ">
                                {{ $user->employmentDetails->employment_status->label() }}
                            </span>
                        </dd>
                    </div>
                    @if($user->employmentDetails->employment_start_date)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-400">Start Date</dt>
                            <dd class="text-sm text-white">{{ $user->employmentDetails->employment_start_date->format('d M Y') }}</dd>
                        </div>
                    @endif
                    @if($user->employmentDetails->pay_type)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-400">Pay Type</dt>
                            <dd class="text-sm text-white">{{ $user->employmentDetails->pay_type->label() }}</dd>
                        </div>
                    @endif
                    @if($user->employmentDetails->target_hours_per_week)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-400">Target Hours/Week</dt>
                            <dd class="text-sm text-white">{{ $user->employmentDetails->target_hours_per_week }}h</dd>
                        </div>
                    @endif
                    @if($user->employmentDetails->isOnProbation())
                        <div class="mt-3 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                            <p class="text-sm text-yellow-400">On probation until {{ $user->employmentDetails->probation_end_date->format('d M Y') }}</p>
                        </div>
                    @endif
                    @if($user->employmentDetails->isLeavingSoon())
                        <div class="mt-3 p-3 bg-orange-500/10 border border-orange-500/30 rounded-lg">
                            <p class="text-sm text-orange-400">Leaving on {{ $user->employmentDetails->final_working_date->format('d M Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
