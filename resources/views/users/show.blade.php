<x-layouts.app title="{{ $user->full_name }}">
    <div class="lg:flex lg:items-center lg:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ $user->full_name }}</h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    {{ $user->email }}
                </div>
                @if($user->phone)
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        {{ $user->phone }}
                    </div>
                @endif
            </div>
        </div>
        <div class="mt-5 flex gap-3 lg:ml-4 lg:mt-0">
            @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Edit
                </a>
            @endcan
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900">System Roles</h3>
                <div class="mt-4">
                    @if($user->roleAssignments->isEmpty())
                        <p class="text-sm text-gray-500">No roles assigned.</p>
                    @else
                        <ul role="list" class="divide-y divide-gray-100">
                            @foreach($user->roleAssignments as $assignment)
                                <li class="py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $assignment->system_role->label() }}</p>
                                    @if($assignment->location)
                                        <p class="text-xs text-gray-500">Location: {{ $assignment->location->name }}</p>
                                    @endif
                                    @if($assignment->department)
                                        <p class="text-xs text-gray-500">Department: {{ $assignment->department->name }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900">Business Roles</h3>
                <div class="mt-4">
                    @if($user->businessRoles->isEmpty())
                        <p class="text-sm text-gray-500">No business roles assigned.</p>
                    @else
                        <ul role="list" class="divide-y divide-gray-100">
                            @foreach($user->businessRoles as $role)
                                <li class="py-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $role->name }}
                                        @if($role->pivot->is_primary)
                                            <span class="ml-2 inline-flex items-center rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">Primary</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $role->department->name }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
