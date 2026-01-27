<x-layouts.app title="Super Admin Dashboard">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-white">Super Admin Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">System-wide overview and management</p>
        </div>
    </x-slot:header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Tenants</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_tenants'] }}</p>
                </div>
                <div class="w-10 h-10 bg-brand-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Tenants</p>
                    <p class="text-2xl font-bold text-green-400 mt-1">{{ $stats['active_tenants'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_users'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Users</p>
                    <p class="text-2xl font-bold text-green-400 mt-1">{{ $stats['active_users'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">New Tenants (MTD)</p>
                    <p class="text-2xl font-bold text-brand-400 mt-1">{{ $stats['new_tenants_this_month'] }}</p>
                </div>
                <div class="w-10 h-10 bg-brand-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">New Users (MTD)</p>
                    <p class="text-2xl font-bold text-brand-400 mt-1">{{ $stats['new_users_this_month'] }}</p>
                </div>
                <div class="w-10 h-10 bg-brand-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Quick Actions -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Quick Actions</h3>
            </div>
            <div class="divide-y divide-gray-800">
                <a href="{{ route('super-admin.tenants.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                    <div class="w-10 h-10 bg-brand-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">Manage Tenants</p>
                        <p class="text-xs text-gray-500">View and manage organizations</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('super-admin.users.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                    <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">All Users</p>
                        <p class="text-xs text-gray-500">Browse users across tenants</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('super-admin.users.index') }}" class="px-6 py-4 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                    <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">Impersonate User</p>
                        <p class="text-xs text-gray-500">Login as any user for support</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Recent Tenants -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-white">Recent Tenants</h3>
                <a href="{{ route('super-admin.tenants.index') }}" class="text-sm text-brand-400 hover:text-brand-300">
                    View all
                </a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($recentTenants->take(5) as $tenant)
                    <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="px-6 py-3 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                        <div class="w-8 h-8 bg-brand-900/50 rounded-lg flex items-center justify-center text-brand-400 font-medium text-xs">
                            {{ strtoupper(substr($tenant->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $tenant->name }}</p>
                            <p class="text-xs text-gray-500">{{ $tenant->users_count }} users</p>
                        </div>
                        @if($tenant->is_active)
                            <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                        @else
                            <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                        @endif
                    </a>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        No tenants yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-white">Recent Users</h3>
                <a href="{{ route('super-admin.users.index') }}" class="text-sm text-brand-400 hover:text-brand-300">
                    View all
                </a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($recentUsers->take(5) as $user)
                    <a href="{{ route('super-admin.users.show', $user) }}" class="px-6 py-3 flex items-center gap-3 hover:bg-gray-800/50 transition-colors">
                        <div class="w-8 h-8 bg-purple-900/50 rounded-full flex items-center justify-center text-purple-400 font-medium text-xs">
                            {{ $user->initials }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $user->full_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $user->tenant?->name ?? 'No tenant' }}</p>
                        </div>
                        @if($user->is_active)
                            <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                        @else
                            <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                        @endif
                    </a>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        No users yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Full Tenants Table -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
            <h3 class="font-semibold text-white">All Tenants</h3>
            <a href="{{ route('super-admin.tenants.index') }}" class="text-sm text-brand-400 hover:text-brand-300">
                Manage tenants
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-800/50">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">Organization</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Email</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Users</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Status</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Created</th>
                        <th class="px-3 py-3.5 text-right pr-6 text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($recentTenants as $tenant)
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">{{ $tenant->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $tenant->email }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-white">{{ $tenant->users_count }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($tenant->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $tenant->created_at->format('M d, Y') }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-right pr-6">
                                <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="text-brand-400 hover:text-brand-300 text-sm font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No tenants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
