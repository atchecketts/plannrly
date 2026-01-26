<x-layouts.admin-mobile title="Locations" active="locations">
    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
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
               id="search-locations"
               placeholder="Search locations..."
               class="w-full pl-10 pr-4 py-3 bg-gray-900 border border-gray-800 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>

    <!-- Locations List -->
    <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">All Locations</h2>
        <div class="space-y-2" id="locations-list">
            @forelse($locations as $location)
                <a href="{{ route('locations.show', $location) }}"
                   class="location-card block bg-gray-900 rounded-lg border border-gray-800 p-3"
                   data-name="{{ strtolower($location->name) }}"
                   data-city="{{ strtolower($location->city ?? '') }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 {{ $location->is_active ? 'bg-brand-900/50' : 'bg-gray-700' }} rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 {{ $location->is_active ? 'text-brand-300' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-white truncate">{{ $location->name }}</p>
                                @if(!$location->is_active)
                                    <span class="px-1.5 py-0.5 text-xs font-medium bg-red-500/20 text-red-400 rounded shrink-0">Inactive</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 truncate">
                                {{ $location->city ?? 'No city' }}
                                @if($location->departments_count > 0)
                                    &bull; {{ $location->departments_count }} {{ Str::plural('department', $location->departments_count) }}
                                @endif
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            @empty
                <div class="bg-gray-900 rounded-lg border border-gray-800 p-8 text-center">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No locations found</p>
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
            <p class="text-sm text-gray-500">No matching locations</p>
        </div>
    </div>

    <!-- Add Location Button -->
    @can('create', App\Models\Location::class)
        <a href="{{ route('locations.create') }}"
           class="flex items-center justify-center gap-2 w-full py-3 bg-brand-900 text-white font-medium rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Location
        </a>
    @endcan

    <script>
        document.getElementById('search-locations').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.location-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.dataset.name;
                const city = card.dataset.city;

                if (name.includes(query) || city.includes(query)) {
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
