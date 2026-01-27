<x-layouts.app title="Tenant Details">
    <x-slot:header>
        <div class="flex items-center gap-4">
            <a href="{{ route('super-admin.tenants.index') }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $tenant->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $tenant->slug }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-medium hover:bg-gray-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            <form action="{{ route('super-admin.tenants.toggle-status', $tenant) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="{{ $tenant->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    {{ $tenant->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </x-slot:header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_users'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Active Users</p>
            <p class="text-2xl font-bold text-green-400 mt-1">{{ $stats['active_users'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Locations</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_locations'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Departments</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_departments'] }}</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500">Business Roles</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_business_roles'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tenant Details -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Organization Details</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="text-white">{{ $tenant->email }}</p>
                </div>
                @if($tenant->phone)
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="text-white">{{ $tenant->phone }}</p>
                    </div>
                @endif
                @if($tenant->address)
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="text-white">{{ $tenant->address }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <div class="mt-1">
                        @if($tenant->is_active)
                            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
                        @endif
                        @if($tenant->isOnTrial())
                            <span class="inline-flex items-center rounded-md bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20 ml-1">
                                Trial until {{ $tenant->trial_ends_at->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Created</p>
                    <p class="text-white">{{ $tenant->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-white">Recent Users</h3>
                <a href="{{ route('super-admin.users.index', ['tenant_id' => $tenant->id]) }}" class="text-sm text-brand-400 hover:text-brand-300">
                    View all users
                </a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($tenant->users as $user)
                    <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-400 font-medium">
                                {{ $user->initials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-white">{{ $user->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-400">{{ $user->getHighestRole()?->label() ?? 'Employee' }}</p>
                                @if($user->is_active)
                                    <span class="text-xs text-green-400">Active</span>
                                @else
                                    <span class="text-xs text-red-400">Inactive</span>
                                @endif
                            </div>
                            <form action="{{ route('super-admin.impersonate.start', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 text-amber-400 hover:text-amber-300 hover:bg-gray-800 rounded-lg transition-colors" title="Impersonate user">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        No users found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
