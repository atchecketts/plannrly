<x-layouts.app title="Schedules">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <p class="mt-2 text-sm text-gray-700">Manage your work schedules and rotas.</p>
        </div>
        @can('create', App\Models\Rota::class)
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('rotas.create') }}" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Create schedule
                </a>
            </div>
        @endcan
    </div>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date Range</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Shifts</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($rotas as $rota)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ $rota->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $rota->start_date->format('M d') }} - {{ $rota->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $rota->shifts_count }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                            {{ $rota->status->color() === 'gray' ? 'bg-gray-50 text-gray-700 ring-gray-600/20' : '' }}
                                            {{ $rota->status->color() === 'green' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                                            {{ $rota->status->color() === 'blue' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}">
                                            {{ $rota->status->label() }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="{{ route('rotas.show', $rota) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        @can('update', $rota)
                                            <a href="{{ route('rotas.edit', $rota) }}" class="ml-4 text-indigo-600 hover:text-indigo-900">Edit</a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500">No schedules found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $rotas->links() }}
    </div>
</x-layouts.app>
