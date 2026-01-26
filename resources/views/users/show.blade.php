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
    </div>
</x-layouts.app>
