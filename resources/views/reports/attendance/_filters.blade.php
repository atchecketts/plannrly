<div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
    <div class="px-6 py-4">
        <form method="GET" action="{{ route($route) }}" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label for="start_date" class="text-sm text-gray-400">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}"
                       class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
            </div>

            <div class="flex flex-col gap-1">
                <label for="end_date" class="text-sm text-gray-400">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}"
                       class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
            </div>

            <div class="flex flex-col gap-1">
                <label for="department_id" class="text-sm text-gray-400">Department</label>
                <select name="department_id" id="department_id" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label for="location_id" class="text-sm text-gray-400">Location</label>
                <select name="location_id" id="location_id" class="bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2">
                    <option value="">All Locations</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-brand-900 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-brand-800 transition-colors">
                Apply Filters
            </button>

            @if(request()->hasAny(['start_date', 'end_date', 'department_id', 'location_id']))
                <a href="{{ route($route) }}" class="text-sm text-gray-400 hover:text-white">Clear</a>
            @endif
        </form>
    </div>
</div>
