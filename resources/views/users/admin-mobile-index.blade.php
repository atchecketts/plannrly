<x-layouts.admin-mobile title="Team" active="team" headerTitle="Team">
    <div class="px-4 space-y-4">
        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $stats['active'] }}</p>
                        <p class="text-xs text-gray-500">Active</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
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
        <div class="relative">
            <input type="text"
                   id="search-users"
                   placeholder="Search team members..."
                   class="w-full pl-10 pr-4 py-3 bg-gray-900 border border-gray-800 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-transparent">
            <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- Team Members List -->
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Team Members</h2>
            <div class="space-y-3" id="users-list">
                @forelse($users as $user)
                    <a href="{{ route('users.show', $user) }}"
                       class="user-card block bg-gray-900 rounded-xl border border-gray-800 p-4 hover:bg-gray-800/50 transition-colors"
                       data-name="{{ strtolower($user->full_name) }}"
                       data-email="{{ strtolower($user->email) }}">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 {{ $user->is_active ? 'bg-brand-900/50' : 'bg-gray-700' }} rounded-full flex items-center justify-center {{ $user->is_active ? 'text-brand-300' : 'text-gray-500' }} font-medium">
                                {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-white">{{ $user->full_name }}</p>
                                    @if(!$user->is_active)
                                        <span class="px-1.5 py-0.5 text-xs font-medium bg-red-500/20 text-red-400 rounded">Inactive</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                                @php
                                    $primaryRole = $user->businessRoles->firstWhere('pivot.is_primary', true) ?? $user->businessRoles->first();
                                @endphp
                                @if($primaryRole)
                                    <p class="text-xs text-gray-600 mt-0.5">
                                        {{ $primaryRole->department?->name }}
                                        @if($primaryRole->name)
                                            &bull; {{ $primaryRole->name }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-xs font-medium {{ $user->isSuperAdmin() || $user->isAdmin() ? 'text-purple-400' : ($user->isLocationAdmin() || $user->isDepartmentAdmin() ? 'text-blue-400' : 'text-gray-500') }}">
                                    {{ $user->getHighestRole()?->label() ?? 'Employee' }}
                                </span>
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="bg-gray-900 rounded-xl border border-gray-800 p-8 text-center">
                        <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">No team members found</p>
                    </div>
                @endforelse
            </div>

            <!-- No Results Message (hidden by default) -->
            <div id="no-results" class="hidden bg-gray-900 rounded-xl border border-gray-800 p-8 text-center">
                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-500">No matching team members</p>
            </div>
        </div>

        <!-- Add Employee Button -->
        @can('create', App\Models\User::class)
            <a href="{{ route('users.create') }}"
               class="flex items-center justify-center gap-2 w-full py-3 bg-brand-900 text-white font-medium rounded-xl hover:bg-brand-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Add Team Member
            </a>
        @endcan
    </div>

    <div class="h-6"></div>

    <script>
        document.getElementById('search-users').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.user-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.dataset.name;
                const email = card.dataset.email;

                if (name.includes(query) || email.includes(query)) {
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
