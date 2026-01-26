<x-layouts.app title="{{ $location->name }}">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-semibold text-white">{{ $location->name }}</h2>
                <p class="mt-1 text-sm text-gray-400">{{ $location->full_address ?: 'No address' }}</p>
            </div>
            <div class="mt-4 flex gap-3 lg:ml-4 lg:mt-0">
                @can('update', $location)
                    <a href="{{ route('locations.edit', $location) }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Edit
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800">
        <div class="px-6 py-4 border-b border-gray-800">
            <h3 class="text-base font-semibold text-white">Departments</h3>
        </div>
        <div class="p-6">
            @if($location->departments->isEmpty())
                <p class="text-sm text-gray-400">No departments in this location.</p>
            @else
                <ul role="list" class="divide-y divide-gray-800">
                    @foreach($location->departments as $department)
                        <li class="flex items-center justify-between gap-6 py-4 first:pt-0 last:pb-0">
                            <div class="flex min-w-0 gap-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm font-semibold text-white">{{ $department->name }}</p>
                                    <p class="mt-1 truncate text-xs text-gray-400">{{ $department->businessRoles->count() }} roles</p>
                                </div>
                            </div>
                            <span class="inline-block h-4 w-4 rounded-full" style="background-color: {{ $department->color }}"></span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.app>
