<x-layouts.app title="Departments">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Departments</h2>
                    <p class="mt-1 text-sm text-gray-400">A list of all departments in your organization.</p>
                </div>
                @can('create', App\Models\Department::class)
                    <a href="{{ route('departments.create') }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Add department
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">Name</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Location</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Roles</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Status</th>
                    <th class="relative py-3.5 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($departments as $department)
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $department->color }}"></span>
                                {{ $department->name }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $department->location->name }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $department->business_roles_count }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($department->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                            @can('update', $department)
                                <a href="{{ route('departments.edit', $department) }}" class="text-brand-400 hover:text-brand-300">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500">No departments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>
</x-layouts.app>
