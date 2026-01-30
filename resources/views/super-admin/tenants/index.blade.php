<x-layouts.app title="Tenants">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-white">Tenants</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage all organizations using the platform</p>
        </div>
    </x-slot:header>

    <!-- Quick Search Filters -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 mb-6">
        <form method="GET" action="{{ route('super-admin.tenants.index') }}" class="flex flex-wrap items-center gap-4" x-data x-ref="filterForm">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenants..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500"
                    x-on:input.debounce.500ms="$refs.filterForm.submit()">
            </div>
            <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-brand-500" x-on:change="$refs.filterForm.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @if(request('search') || request('status'))
                <a href="{{ route('super-admin.tenants.index') }}" class="text-gray-400 hover:text-white transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-800/50">
                    <tr>
                        <x-table.sortable-header column="organization" label="Organization" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" :isFirst="true" />
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Contact</th>
                        <x-table.sortable-header column="users" label="Users" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                        <x-table.sortable-header column="status" label="Status" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                        <x-table.sortable-header column="created" label="Created" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
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
                                $groupTenants = $tenants->filter(function($tenant) use ($sortParams, $groupKey) {
                                    return match($sortParams['group']) {
                                        'organization' => $groupKey === 'organization-' . strtoupper(substr($tenant->name, 0, 1)),
                                        'users' => $groupKey === 'users-' . $tenant->users_count,
                                        'status' => $groupKey === 'status-' . ($tenant->is_active ? 'active' : 'inactive'),
                                        'created' => $groupKey === 'created-' . $tenant->created_at->format('Y-m-d'),
                                        default => false,
                                    };
                                });
                            @endphp
                            <x-table.group-header :label="$groupLabel" :groupKey="$groupKey" :colspan="6" :count="$groupTenants->count()" />
                            @foreach($groupTenants as $tenant)
                                @include('super-admin.tenants._row', ['tenant' => $tenant, 'groupKey' => $groupKey])
                            @endforeach
                        @endforeach
                    @else
                        @forelse($tenants as $tenant)
                            @include('super-admin.tenants._row', ['tenant' => $tenant, 'groupKey' => null])
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No tenants found.
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>

        @if(!$sortParams['group'] && $tenants->hasPages())
            <div class="px-6 py-4 border-t border-gray-800">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
