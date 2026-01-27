<x-layouts.app title="User Details">
    <x-slot:header>
        <div class="flex items-center gap-4">
            <a href="{{ route('super-admin.users.index') }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-400 font-semibold text-lg">
                    {{ $user->initials }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $user->full_name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
                </div>
            </div>
        </div>
        @if(!$user->trashed() && !$user->isSuperAdmin())
            <form action="{{ route('super-admin.impersonate.start', $user) }}" method="POST">
                @csrf
                <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-amber-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Impersonate User
                </button>
            </form>
        @endif
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Details -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">User Details</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Full Name</p>
                    <p class="text-white">{{ $user->full_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="text-white">{{ $user->email }}</p>
                </div>
                @if($user->phone)
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="text-white">{{ $user->phone }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <div class="mt-1">
                        @if($user->trashed())
                            <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">Deleted</span>
                        @elseif($user->is_active)
                            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-gray-500/10 px-2 py-1 text-xs font-medium text-gray-400 ring-1 ring-inset ring-gray-500/20">Inactive</span>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Created</p>
                    <p class="text-white">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Last Login</p>
                    <p class="text-white">{{ $user->last_login_at?->format('M d, Y \a\t g:i A') ?? 'Never' }}</p>
                </div>
            </div>
        </div>

        <!-- Tenant & Role Info -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Tenant & Roles</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Tenant</p>
                    @if($user->tenant)
                        <a href="{{ route('super-admin.tenants.show', $user->tenant) }}" class="text-brand-400 hover:text-brand-300">
                            {{ $user->tenant->name }}
                        </a>
                    @else
                        <p class="text-gray-400">No tenant assigned</p>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-2">System Roles</p>
                    @if($user->roleAssignments->count() > 0)
                        <div class="space-y-2">
                            @foreach($user->roleAssignments as $assignment)
                                @php
                                    $location = $assignment->location_id ? \App\Models\Location::withoutGlobalScopes()->find($assignment->location_id) : null;
                                    $department = $assignment->department_id ? \App\Models\Department::withoutGlobalScopes()->find($assignment->department_id) : null;
                                @endphp
                                <div class="bg-gray-800 rounded-lg px-3 py-2">
                                    <p class="text-sm font-medium text-white">{{ $assignment->system_role->label() }}</p>
                                    @if($location || $department)
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            @if($location)
                                                {{ $location->name }}
                                            @endif
                                            @if($location && $department)
                                                &rarr;
                                            @endif
                                            @if($department)
                                                {{ $department->name }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">No roles assigned (Employee)</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Business Roles -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Business Roles</h3>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($user->businessRoles as $role)
                    <div class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $role->color ?? '#6366f1' }}"></div>
                            <div class="flex-1">
                                <p class="font-medium text-white">{{ $role->name }}</p>
                                @if($role->department)
                                    <p class="text-sm text-gray-500">{{ $role->department->name }}</p>
                                @endif
                            </div>
                            @if($role->pivot->is_primary)
                                <span class="text-xs text-brand-400">Primary</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        No business roles assigned.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
