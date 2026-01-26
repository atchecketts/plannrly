<x-layouts.app title="Leave Request Details">
    <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
        <div class="px-6 py-4">
            <h2 class="text-lg font-semibold text-white">Leave Request Information</h2>
            <p class="mt-1 text-sm text-gray-400">Request submitted by {{ $leaveRequest->user->full_name }}</p>
        </div>
    </div>

    <div class="bg-gray-900 rounded-lg border border-gray-800">
        <dl class="divide-y divide-gray-800">
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-300">Leave Type</dt>
                <dd class="mt-1 text-sm text-white sm:col-span-2 sm:mt-0">{{ $leaveRequest->leaveType->name }}</dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-300">Date Range</dt>
                <dd class="mt-1 text-sm text-white sm:col-span-2 sm:mt-0">
                    {{ $leaveRequest->start_date->format('M d, Y') }} - {{ $leaveRequest->end_date->format('M d, Y') }}
                    @if($leaveRequest->start_half_day)
                        <span class="text-gray-400">(starts half day)</span>
                    @endif
                    @if($leaveRequest->end_half_day)
                        <span class="text-gray-400">(ends half day)</span>
                    @endif
                </dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-300">Total Days</dt>
                <dd class="mt-1 text-sm text-white sm:col-span-2 sm:mt-0">{{ $leaveRequest->total_days }} days</dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-300">Status</dt>
                <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                    @switch($leaveRequest->status->value)
                        @case('draft')
                            <span class="inline-flex items-center rounded-md bg-gray-500/10 px-2 py-1 text-xs font-medium text-gray-400">Draft</span>
                            @break
                        @case('requested')
                            <span class="inline-flex items-center rounded-md bg-yellow-500/10 px-2 py-1 text-xs font-medium text-yellow-400">Pending</span>
                            @break
                        @case('approved')
                            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400">Approved</span>
                            @break
                        @case('rejected')
                            <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400">Rejected</span>
                            @break
                    @endswitch
                </dd>
            </div>
            @if($leaveRequest->reason)
                <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-300">Reason</dt>
                    <dd class="mt-1 text-sm text-white sm:col-span-2 sm:mt-0">{{ $leaveRequest->reason }}</dd>
                </div>
            @endif
            @if($leaveRequest->reviewedBy)
                <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-300">Reviewed By</dt>
                    <dd class="mt-1 text-sm text-white sm:col-span-2 sm:mt-0">
                        {{ $leaveRequest->reviewedBy->full_name }} on {{ $leaveRequest->reviewed_at->format('M d, Y H:i') }}
                    </dd>
                </div>
            @endif
            @if($leaveRequest->review_notes)
                <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-300">Review Notes</dt>
                    <dd class="mt-1 text-sm text-white sm:col-span-2 sm:mt-0">{{ $leaveRequest->review_notes }}</dd>
                </div>
            @endif
        </dl>
    </div>

    @if($leaveRequest->isPending() && auth()->user()->can('review', $leaveRequest))
        <div class="mt-6 flex gap-4">
            <form action="{{ route('leave-requests.review', $leaveRequest) }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                    Approve
                </button>
            </form>
            <form action="{{ route('leave-requests.review', $leaveRequest) }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                    Reject
                </button>
            </form>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('leave-requests.index') }}" class="text-sm font-medium text-brand-400 hover:text-brand-300">
            &larr; Back to Leave Requests
        </a>
    </div>
</x-layouts.app>
