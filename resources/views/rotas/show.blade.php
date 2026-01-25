<x-layouts.app title="{{ $rota->name }}">
    <div class="lg:flex lg:items-center lg:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ $rota->name }}</h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    {{ $rota->start_date->format('M d') }} - {{ $rota->end_date->format('M d, Y') }}
                </div>
                <div class="mt-2 flex items-center text-sm">
                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                        {{ $rota->status->color() === 'gray' ? 'bg-gray-50 text-gray-700 ring-gray-600/20' : '' }}
                        {{ $rota->status->color() === 'green' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                        {{ $rota->status->color() === 'blue' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}">
                        {{ $rota->status->label() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="mt-5 flex gap-3 lg:ml-4 lg:mt-0">
            @can('publish', $rota)
                <form action="{{ route('rotas.publish', $rota) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                        Publish
                    </button>
                </form>
            @endcan
            @can('update', $rota)
                <a href="{{ route('rotas.edit', $rota) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Edit
                </a>
            @endcan
        </div>
    </div>

    <div class="mt-8">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold text-gray-900">Shifts</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $rota->shifts->count() }} shifts scheduled</p>

                <div class="mt-6">
                    @if($rota->shifts->isEmpty())
                        <p class="text-sm text-gray-500">No shifts scheduled yet. Click a cell in the calendar to add a shift.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Date</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Time</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Employee</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($rota->shifts->sortBy('date')->sortBy('start_time') as $shift)
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">
                                                {{ $shift->date->format('D, M d') }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                {{ $shift->user?->full_name ?? 'Unassigned' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $shift->businessRole->name }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                                    {{ $shift->status->color() === 'blue' ? 'bg-blue-50 text-blue-700' : '' }}
                                                    {{ $shift->status->color() === 'green' ? 'bg-green-50 text-green-700' : '' }}
                                                    {{ $shift->status->color() === 'yellow' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                                    {{ $shift->status->color() === 'red' ? 'bg-red-50 text-red-700' : '' }}
                                                    {{ $shift->status->color() === 'gray' ? 'bg-gray-50 text-gray-700' : '' }}">
                                                    {{ $shift->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
