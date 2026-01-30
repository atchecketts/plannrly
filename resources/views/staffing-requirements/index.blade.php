<x-layouts.app title="Staffing Requirements">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Staffing Requirements</h2>
                    <p class="mt-1 text-sm text-gray-400">Define minimum and maximum staffing levels for each role by day and time.</p>
                </div>
                @can('create', App\Models\StaffingRequirement::class)
                    <a href="{{ route('staffing-requirements.create') }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Add requirement
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    <x-table.sortable-header column="role" label="Role" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" :isFirst="true" />
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Scope</th>
                    <x-table.sortable-header column="day" label="Day" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" />
                    <x-table.sortable-header column="start_time" label="Time Window" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" />
                    <x-table.sortable-header column="min" label="Min/Max" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" />
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
                            $groupRequirements = $staffingRequirements->filter(function($req) use ($sortParams, $groupKey) {
                                return match($sortParams['group']) {
                                    'role' => $groupKey === 'role-' . $req->business_role_id,
                                    'day' => $groupKey === 'day-' . $req->day_of_week,
                                    'status' => $groupKey === 'status-' . ($req->is_active ? 'active' : 'inactive'),
                                    default => false,
                                };
                            });
                        @endphp
                        <x-table.group-header :label="$groupLabel" :groupKey="$groupKey" :colspan="7" :count="$groupRequirements->count()" />
                        @foreach($groupRequirements as $requirement)
                            @include('staffing-requirements._row', ['requirement' => $requirement, 'groupKey' => $groupKey])
                        @endforeach
                    @endforeach
                @else
                    @forelse($staffingRequirements as $requirement)
                        @include('staffing-requirements._row', ['requirement' => $requirement, 'groupKey' => null])
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p>No staffing requirements found.</p>
                                    <p class="text-gray-600">Set up requirements to track coverage on your schedule.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>

    @if(!$sortParams['group'] && $staffingRequirements instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $staffingRequirements->links() }}
        </div>
    @endif
</x-layouts.app>
