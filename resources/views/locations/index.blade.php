<x-layouts.app title="Locations">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Locations</h2>
                    <p class="mt-1 text-sm text-gray-400">A list of all locations in your organization.</p>
                </div>
                @can('create', App\Models\Location::class)
                    <a href="{{ route('locations.create') }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Add location
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    <x-table.sortable-header column="name" label="Name" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" :isFirst="true" />
                    <x-table.sortable-header column="city" label="City" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                    <x-table.sortable-header column="departments" label="Departments" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                    <x-table.sortable-header column="status" label="Status" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                    <th class="relative py-3.5 pl-3 pr-6"><span class="sr-only">Actions</span></th>
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
                            $groupLocations = $locations->filter(function($location) use ($sortParams, $groupKey) {
                                return match($sortParams['group']) {
                                    'name' => $groupKey === 'name-' . strtoupper(substr($location->name, 0, 1)),
                                    'city' => $groupKey === 'city-' . Str::slug($location->city ?: 'no-city'),
                                    'departments' => $groupKey === 'departments-' . $location->departments_count,
                                    'status' => $groupKey === 'status-' . ($location->is_active ? 'active' : 'inactive'),
                                    default => false,
                                };
                            });
                        @endphp
                        <x-table.group-header :label="$groupLabel" :groupKey="$groupKey" :colspan="5" :count="$groupLocations->count()" />
                        @foreach($groupLocations as $location)
                            @include('locations._row', ['location' => $location, 'groupKey' => $groupKey])
                        @endforeach
                    @endforeach
                @else
                    @forelse($locations as $location)
                        @include('locations._row', ['location' => $location, 'groupKey' => null])
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500">No locations found.</td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>

    @if(!$sortParams['group'])
        <div class="mt-4">
            {{ $locations->links() }}
        </div>
    @endif
</x-layouts.app>
