<x-layouts.app title="Leave Requests">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <p class="mt-2 text-sm text-gray-700">Manage leave requests.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('leave-requests.create') }}" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Request leave
            </a>
        </div>
    </div>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                @if(auth()->user()->isAdmin() || auth()->user()->isLocationAdmin() || auth()->user()->isDepartmentAdmin())
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                                @endif
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Dates</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Days</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($leaveRequests as $request)
                                <tr>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isLocationAdmin() || auth()->user()->isDepartmentAdmin())
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ $request->user->full_name }}
                                        </td>
                                    @endif
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $request->leaveType->color }}"></span>
                                            {{ $request->leaveType->name }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $request->total_days }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                            {{ $request->status->color() === 'gray' ? 'bg-gray-50 text-gray-700 ring-gray-600/20' : '' }}
                                            {{ $request->status->color() === 'yellow' ? 'bg-yellow-50 text-yellow-700 ring-yellow-600/20' : '' }}
                                            {{ $request->status->color() === 'green' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                                            {{ $request->status->color() === 'red' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}">
                                            {{ $request->status->label() }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="{{ route('leave-requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        @can('review', $request)
                                            <form action="{{ route('leave-requests.review', $request) }}" method="POST" class="inline ml-4">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                            </form>
                                            <form action="{{ route('leave-requests.review', $request) }}" method="POST" class="inline ml-2">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500">No leave requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $leaveRequests->links() }}
    </div>
</x-layouts.app>
