<x-layouts.admin-mobile title="Business Roles" active="roles">
    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['active'] }}</p>
                    <p class="text-xs text-gray-500">Active</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-700/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-400">{{ $stats['inactive'] }}</p>
                    <p class="text-xs text-gray-500">Inactive</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="relative mb-4">
        <input type="text"
               id="search-roles"
               placeholder="Search roles..."
               class="w-full pl-10 pr-4 py-3 bg-gray-900 border border-gray-800 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>

    <!-- Business Roles List -->
    <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">All Roles</h2>
        <div class="space-y-2" id="roles-list">
            @forelse($businessRoles as $role)
                <div class="role-card bg-gray-900 rounded-lg border border-gray-800 p-3"
                     data-name="{{ strtolower($role->name) }}"
                     data-department="{{ strtolower($role->department->name ?? '') }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                             style="background-color: {{ $role->color }}20;">
                            <span class="w-4 h-4 rounded-full" style="background-color: {{ $role->color }};"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-white truncate">{{ $role->name }}</p>
                                @if(!$role->is_active)
                                    <span class="px-1.5 py-0.5 text-xs font-medium bg-red-500/20 text-red-400 rounded shrink-0">Inactive</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 truncate">
                                {{ $role->department->name }} ({{ $role->department->location->name }})
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            @if($role->default_hourly_rate)
                                <p class="text-sm font-medium text-green-400">${{ number_format($role->default_hourly_rate, 2) }}/hr</p>
                            @endif
                            <p class="text-xs text-gray-500">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</p>
                        </div>
                        @can('update', $role)
                            <a href="{{ route('business-roles.edit', $role) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-8 text-center">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No business roles found</p>
                </div>
            @endforelse
        </div>

        <!-- No Results Message -->
        <div id="no-results" class="hidden bg-gray-900 rounded-lg border border-gray-800 p-8 text-center">
            <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <p class="text-sm text-gray-500">No matching roles</p>
        </div>
    </div>

    <!-- Add Role Button -->
    @can('create', App\Models\BusinessRole::class)
        <a href="{{ route('business-roles.create') }}"
           class="flex items-center justify-center gap-2 w-full py-3 bg-brand-900 text-white font-medium rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Role
        </a>
    @endcan

    <script>
        document.getElementById('search-roles').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.role-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.dataset.name;
                const department = card.dataset.department;

                if (name.includes(query) || department.includes(query)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('no-results').classList.toggle('hidden', visibleCount > 0 || query === '');
        });
    </script>
</x-layouts.admin-mobile>
