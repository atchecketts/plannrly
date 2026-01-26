<x-layouts.app title="Super Admin Dashboard">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <h2 class="text-lg font-semibold text-white">Super Admin Dashboard</h2>
            <p class="mt-1 text-sm text-gray-400">System-wide statistics and tenant management.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <dt class="truncate text-sm font-medium text-gray-400">Total Tenants</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-white">{{ $stats['total_tenants'] }}</dd>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <dt class="truncate text-sm font-medium text-gray-400">Active Tenants</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-400">{{ $stats['active_tenants'] }}</dd>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <dt class="truncate text-sm font-medium text-gray-400">Total Users</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-white">{{ $stats['total_users'] }}</dd>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <dt class="truncate text-sm font-medium text-gray-400">New Tenants (MTD)</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-brand-400">{{ $stats['new_tenants_this_month'] }}</dd>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800">
        <div class="px-6 py-4 border-b border-gray-800">
            <h3 class="text-base font-semibold text-white">Recent Tenants</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-800/50">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">Name</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Email</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Status</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($recentTenants as $tenant)
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">{{ $tenant->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $tenant->email }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($tenant->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400">Inactive</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $tenant->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
