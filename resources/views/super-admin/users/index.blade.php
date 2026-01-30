<x-layouts.app title="All Users">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-white">All Users</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage users across all tenants</p>
        </div>
    </x-slot:header>

    <!-- Filters -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 mb-6">
        <form method="GET" action="{{ route('super-admin.users.index') }}" class="flex flex-wrap items-center gap-4" x-data="{ searchTimeout: null }" x-ref="filterForm">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500"
                    x-on:input.debounce.500ms="$refs.filterForm.submit()">
            </div>
            <select name="tenant_id" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-brand-500" x-on:change="$refs.filterForm.submit()">
                <option value="">All Tenants</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-brand-500" x-on:change="$refs.filterForm.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
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
                        <x-table.sortable-header column="name" label="User" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" :isFirst="true" />
                        <x-table.sortable-header column="tenant" label="Tenant" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                        <x-table.sortable-header column="role" label="Role" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                        <x-table.sortable-header column="status" label="Status" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                        <x-table.sortable-header column="last_login" label="Last Login" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                        <th class="px-3 py-3.5 text-right pr-6 text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800" x-data="{
                    expandedGroups: {},
                    toggleGroup(key) {
                        this.expandedGroups[key] = !this.expandedGroups[key];
                    },
                    isExpanded(key) {
                        return this.expandedGroups[key] === true;
                    }
                }">
                    @if($sortParams['group'] && !empty($allGroups))
                        @foreach($allGroups as $group)
                            @php
                                $groupKey = $group['key'];
                                $groupLabel = $group['label'];
                                $groupUsers = $users->filter(function($user) use ($sortParams, $groupKey) {
                                    return match($sortParams['group']) {
                                        'name' => $groupKey === 'name-' . strtoupper(substr($user->first_name, 0, 1)),
                                        'tenant' => $groupKey === 'tenant-' . ($user->tenant ? Str::slug($user->tenant->name) : 'no-tenant'),
                                        'role' => $groupKey === 'role-' . Str::slug($user->getHighestRole()?->label() ?? 'Employee'),
                                        'status' => $groupKey === 'status-' . ($user->is_active ? 'active' : 'inactive'),
                                        'last_login' => $groupKey === 'last_login-' . ($user->last_login_at?->format('Y-m-d') ?? 'Never'),
                                        default => false,
                                    };
                                });
                            @endphp
                            <x-table.group-header :label="$groupLabel" :groupKey="$groupKey" :colspan="6" :count="$groupUsers->count()" />
                            @foreach($groupUsers as $user)
                                @include('super-admin.users._row', ['user' => $user, 'groupKey' => $groupKey])
                            @endforeach
                        @endforeach
                    @else
                        @forelse($users as $user)
                            @include('super-admin.users._row', ['user' => $user, 'groupKey' => null])
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>

        @if(!$sortParams['group'] && $users->hasPages())
            <div class="px-6 py-4 border-t border-gray-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
