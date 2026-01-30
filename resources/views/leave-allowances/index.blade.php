<x-layouts.app title="Leave Allowances">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Leave Allowances</h2>
                    <p class="mt-1 text-sm text-gray-400">Manage employee leave allowances for each year.</p>
                </div>
                @can('create', App\Models\LeaveAllowance::class)
                    <a href="{{ route('leave-allowances.create') }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Add allowance
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('leave-allowances.index') }}" class="flex flex-wrap items-end gap-4" x-data x-ref="filterForm">
                <div class="w-32">
                    <label for="year" class="block text-sm font-medium text-gray-300 mb-1">Year</label>
                    <select name="year" id="year" class="w-full rounded-lg bg-gray-800 border-gray-700 text-gray-200 focus:ring-brand-900 focus:border-brand-900" x-on:change="$refs.filterForm.submit()">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-48">
                    <label for="leave_type" class="block text-sm font-medium text-gray-300 mb-1">Leave Type</label>
                    <select name="leave_type" id="leave_type" class="w-full rounded-lg bg-gray-800 border-gray-700 text-gray-200 focus:ring-brand-900 focus:border-brand-900" x-on:change="$refs.filterForm.submit()">
                        <option value="">All Types</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ $leaveTypeId == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($leaveTypeId)
                    <a href="{{ route('leave-allowances.index', ['year' => $year]) }}" class="text-gray-400 hover:text-gray-300 py-2 px-2">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">Employee</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Leave Type</th>
                    <x-table.sortable-header column="total" label="Total" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" align="right" />
                    <x-table.sortable-header column="used" label="Used" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" align="right" />
                    <x-table.sortable-header column="carried_over" label="Carried Over" :currentSort="$sortParams['sort']" :currentDirection="$sortParams['direction']" :currentGroup="$sortParams['group']" :groupable="true" align="right" />
                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-300">Remaining</th>
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
                            $groupAllowances = $allowances->filter(function($allowance) use ($sortParams, $groupKey) {
                                return match($sortParams['group']) {
                                    'total' => $groupKey === 'total-' . $allowance->total_days,
                                    'used' => $groupKey === 'used-' . $allowance->used_days,
                                    'carried_over' => $groupKey === 'carried_over-' . $allowance->carried_over_days,
                                    default => false,
                                };
                            });
                        @endphp
                        <x-table.group-header :label="$groupLabel" :groupKey="$groupKey" :colspan="7" :count="$groupAllowances->count()" />
                        @foreach($groupAllowances as $allowance)
                            @include('leave-allowances._row', ['allowance' => $allowance, 'groupKey' => $groupKey])
                        @endforeach
                    @endforeach
                @else
                    @forelse($allowances as $allowance)
                        @include('leave-allowances._row', ['allowance' => $allowance, 'groupKey' => null])
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">No allowances found for {{ $year }}. Add an allowance to get started.</td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>

    @if(!$sortParams['group'])
        <div class="mt-4">
            {{ $allowances->links() }}
        </div>
    @endif
</x-layouts.app>
