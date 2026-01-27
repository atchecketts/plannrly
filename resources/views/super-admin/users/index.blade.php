<x-layouts.app title="All Users">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-white">All Users</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage users across all tenants</p>
        </div>
    </x-slot:header>

    <!-- Filters -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 mb-6">
        <form method="GET" action="{{ route('super-admin.users.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500">
            </div>
            <select name="tenant_id" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-brand-500">
                <option value="">All Tenants</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-brand-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
            <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-brand-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('tenant_id') || request('status'))
                <a href="{{ route('super-admin.users.index') }}" class="text-gray-400 hover:text-white transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-800/50">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">User</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Tenant</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Role</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Status</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Last Login</th>
                        <th class="px-3 py-3.5 text-right pr-6 text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-800/50 transition-colors {{ $user->trashed() ? 'opacity-60' : '' }}">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-400 font-medium">
                                        {{ $user->initials }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $user->full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4">
                                @if($user->tenant)
                                    <a href="{{ route('super-admin.tenants.show', $user->tenant) }}" class="text-sm text-brand-400 hover:text-brand-300">
                                        {{ $user->tenant->name }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-500">No tenant</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @php $role = $user->getHighestRole(); @endphp
                                @if($role)
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                        @if($role->value === 'super_admin') bg-amber-500/10 text-amber-400 ring-amber-500/20
                                        @elseif($role->value === 'admin') bg-purple-500/10 text-purple-400 ring-purple-500/20
                                        @else bg-gray-500/10 text-gray-400 ring-gray-500/20 @endif ring-1 ring-inset">
                                        {{ $role->label() }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Employee</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($user->trashed())
                                    <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">
                                        Deleted
                                    </span>
                                @elseif($user->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-gray-500/10 px-2 py-1 text-xs font-medium text-gray-400 ring-1 ring-inset ring-gray-500/20">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super-admin.users.show', $user) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if(!$user->trashed() && !$user->isSuperAdmin())
                                        <form action="{{ route('super-admin.impersonate.start', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-amber-400 hover:text-amber-300 hover:bg-gray-800 rounded-lg transition-colors" title="Impersonate">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
