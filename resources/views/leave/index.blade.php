<x-layouts.app title="Leave Requests">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Leave Requests</h2>
                    <p class="mt-1 text-sm text-gray-400">Manage leave requests.</p>
                </div>
                <a href="{{ route('leave-requests.create') }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                    Request leave
                </a>
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-800">
            <thead>
                <tr class="bg-gray-800/50">
                    @if(auth()->user()->isAdmin() || auth()->user()->isLocationAdmin() || auth()->user()->isDepartmentAdmin())
                        <th class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-300">Employee</th>
                    @endif
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Type</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Dates</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Days</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-300">Status</th>
                    <th class="relative py-3.5 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($leaveRequests as $request)
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        @if(auth()->user()->isAdmin() || auth()->user()->isLocationAdmin() || auth()->user()->isDepartmentAdmin())
                            <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-white">
                                {{ $request->user->full_name }}
                            </td>
                        @endif
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $request->leaveType->color }}"></span>
                                {{ $request->leaveType->name }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
                            {{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $request->total_days }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                {{ $request->status->color() === 'gray' ? 'bg-gray-500/10 text-gray-400 ring-gray-500/20' : '' }}
                                {{ $request->status->color() === 'yellow' ? 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20' : '' }}
                                {{ $request->status->color() === 'green' ? 'bg-green-500/10 text-green-400 ring-green-500/20' : '' }}
                                {{ $request->status->color() === 'red' ? 'bg-red-500/10 text-red-400 ring-red-500/20' : '' }}">
                                {{ $request->status->label() }}
                            </span>
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                            <a href="{{ route('leave-requests.show', $request) }}" class="text-brand-400 hover:text-brand-300">View</a>
                            @can('review', $request)
                                <form action="{{ route('leave-requests.review', $request) }}" method="POST" class="inline ml-4">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="text-green-400 hover:text-green-300">Approve</button>
                                </form>
                                <form action="{{ route('leave-requests.review', $request) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="text-red-400 hover:text-red-300">Reject</button>
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

    <div class="mt-4">
        {{ $leaveRequests->links() }}
    </div>
</x-layouts.app>
