<x-layouts.app title="Tenants">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-white">Tenants</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage all organizations using the platform</p>
        </div>
    </x-slot:header>

    <!-- Filters -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 mb-6">
        <form method="GET" action="{{ route('super-admin.tenants.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenants..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-brand-500">
            </div>
            <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-brand-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-brand-700 transition-colors">
                Filter
            </button>
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
                        <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">Organization</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Contact</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Users</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Status</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Created</th>
                        <th class="px-3 py-3.5 text-right pr-6 text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-brand-900/50 rounded-lg flex items-center justify-center text-brand-400 font-semibold">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $tenant->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $tenant->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4">
                                <p class="text-sm text-white">{{ $tenant->email }}</p>
                                @if($tenant->phone)
                                    <p class="text-sm text-gray-500">{{ $tenant->phone }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-white">
                                {{ $tenant->users_count }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($tenant->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">
                                        Inactive
                                    </span>
                                @endif
                                @if($tenant->isOnTrial())
                                    <span class="inline-flex items-center rounded-md bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20 ml-1">
                                        Trial
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
                                {{ $tenant->created_at->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('super-admin.tenants.toggle-status', $tenant) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 {{ $tenant->is_active ? 'text-amber-400 hover:text-amber-300' : 'text-green-400 hover:text-green-300' }} hover:bg-gray-800 rounded-lg transition-colors" title="{{ $tenant->is_active ? 'Deactivate' : 'Activate' }}">
                                            @if($tenant->is_active)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No tenants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="px-6 py-4 border-t border-gray-800">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
