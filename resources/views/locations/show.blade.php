<x-layouts.app title="{{ $location->name }}">
    <div class="lg:flex lg:items-center lg:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ $location->name }}</h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    {{ $location->full_address ?: 'No address' }}
                </div>
            </div>
        </div>
        <div class="mt-5 flex lg:ml-4 lg:mt-0">
            @can('update', $location)
                <a href="{{ route('locations.edit', $location) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Edit
                </a>
            @endcan
        </div>
    </div>

    <div class="mt-8">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900">Departments</h3>
                <div class="mt-6">
                    @if($location->departments->isEmpty())
                        <p class="text-sm text-gray-500">No departments in this location.</p>
                    @else
                        <ul role="list" class="divide-y divide-gray-100">
                            @foreach($location->departments as $department)
                                <li class="flex items-center justify-between gap-6 py-4">
                                    <div class="flex min-w-0 gap-4">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold text-gray-900">{{ $department->name }}</p>
                                            <p class="mt-1 truncate text-xs text-gray-500">{{ $department->businessRoles->count() }} roles</p>
                                        </div>
                                    </div>
                                    <span class="inline-block h-4 w-4 rounded-full" style="background-color: {{ $department->color }}"></span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
