<x-layouts.app title="Leave Request Details">
    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <div class="px-4 py-6 sm:px-6">
            <h3 class="text-base font-semibold text-gray-900">Leave Request Information</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Request submitted by {{ $leaveRequest->user->full_name }}</p>
        </div>
        <div class="border-t border-gray-200">
            <dl class="divide-y divide-gray-200">
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-900">Leave Type</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $leaveRequest->leaveType->name }}</dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-900">Date Range</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                        {{ $leaveRequest->start_date->format('M d, Y') }} - {{ $leaveRequest->end_date->format('M d, Y') }}
                        @if($leaveRequest->start_half_day)
                            <span class="text-gray-500">(starts half day)</span>
                        @endif
                        @if($leaveRequest->end_half_day)
                            <span class="text-gray-500">(ends half day)</span>
                        @endif
                    </dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-900">Total Days</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $leaveRequest->total_days }} days</dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-900">Status</dt>
                    <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                        @switch($leaveRequest->status->value)
                            @case('draft')
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Draft</span>
                                @break
                            @case('requested')
                                <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Pending</span>
                                @break
                            @case('approved')
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Approved</span>
                                @break
                            @case('rejected')
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Rejected</span>
                                @break
                        @endswitch
                    </dd>
                </div>
                @if($leaveRequest->reason)
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-900">Reason</dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $leaveRequest->reason }}</dd>
                    </div>
                @endif
                @if($leaveRequest->reviewedBy)
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-900">Reviewed By</dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            {{ $leaveRequest->reviewedBy->full_name }} on {{ $leaveRequest->reviewed_at->format('M d, Y H:i') }}
                        </dd>
                    </div>
                @endif
                @if($leaveRequest->review_notes)
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-900">Review Notes</dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $leaveRequest->review_notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    @if($leaveRequest->isPending() && auth()->user()->can('review', $leaveRequest))
        <div class="mt-6 flex gap-4">
            <form action="{{ route('leave-requests.review', $leaveRequest) }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    Approve
                </button>
            </form>
            <form action="{{ route('leave-requests.review', $leaveRequest) }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Reject
                </button>
            </form>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('leave-requests.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
            &larr; Back to Leave Requests
        </a>
    </div>
</x-layouts.app>
