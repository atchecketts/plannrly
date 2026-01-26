<x-layouts.admin-mobile title="Leave Requests" active="requests">
    <!-- Filter Tabs -->
    <div class="flex bg-gray-900 rounded-lg border border-gray-800 p-1 mb-4">
        <a href="{{ route('leave-requests.mobile', ['status' => 'pending']) }}"
           class="flex-1 py-2 text-center text-sm font-medium rounded-md transition-colors {{ $status === 'pending' ? 'bg-brand-900 text-white' : 'text-gray-400' }}">
            Pending
            @if($pendingCount > 0)
                <span class="ml-1 px-1.5 py-0.5 text-xs bg-amber-500/20 text-amber-400 rounded">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('leave-requests.mobile', ['status' => 'approved']) }}"
           class="flex-1 py-2 text-center text-sm font-medium rounded-md transition-colors {{ $status === 'approved' ? 'bg-brand-900 text-white' : 'text-gray-400' }}">
            Approved
        </a>
        <a href="{{ route('leave-requests.mobile', ['status' => 'rejected']) }}"
           class="flex-1 py-2 text-center text-sm font-medium rounded-md transition-colors {{ $status === 'rejected' ? 'bg-brand-900 text-white' : 'text-gray-400' }}">
            Rejected
        </a>
    </div>

    <!-- Leave Requests List -->
    <div class="space-y-2">
        @forelse($leaveRequests as $leave)
            <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
                <div class="p-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 {{ $leave->status->value === 'requested' ? 'bg-amber-500/20' : ($leave->status->value === 'approved' ? 'bg-green-500/20' : 'bg-red-500/20') }} rounded-full flex items-center justify-center shrink-0 {{ $leave->status->value === 'requested' ? 'text-amber-400' : ($leave->status->value === 'approved' ? 'text-green-400' : 'text-red-400') }} font-medium text-sm">
                            {{ substr($leave->user->first_name, 0, 1) }}{{ substr($leave->user->last_name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white">{{ $leave->user->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $leave->leaveType->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs text-gray-400">
                                    {{ $leave->start_date->format('M d') }}
                                    @if(!$leave->start_date->eq($leave->end_date))
                                        - {{ $leave->end_date->format('M d') }}
                                    @endif
                                </span>
                                <span class="text-xs text-gray-600">({{ $leave->total_days }}d)</span>
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if($leave->status->value === 'requested')
                                <span class="px-2 py-1 text-xs font-medium bg-amber-500/20 text-amber-400 rounded">Pending</span>
                            @elseif($leave->status->value === 'approved')
                                <span class="px-2 py-1 text-xs font-medium bg-green-500/20 text-green-400 rounded">Approved</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-500/20 text-red-400 rounded">Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($leave->status->value === 'requested')
                    <div class="px-3 py-2 bg-gray-800/50 border-t border-gray-800 flex gap-2">
                        <form action="{{ route('leave-requests.review', $leave) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="w-full py-2 bg-green-500 text-white text-sm font-medium rounded-lg">
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('leave-requests.review', $leave) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="w-full py-2 bg-gray-700 text-white text-sm font-medium rounded-lg">
                                Reject
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-8 text-center">
                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <p class="text-sm text-gray-500">
                    @if($status === 'pending')
                        No pending leave requests
                    @elseif($status === 'approved')
                        No approved leave requests
                    @else
                        No rejected leave requests
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @if($leaveRequests->hasPages())
        <div class="pt-4">
            {{ $leaveRequests->links() }}
        </div>
    @endif
</x-layouts.admin-mobile>
