<x-layouts.mobile title="Shift Swaps" active="swaps" :showHeader="false">
    <!-- Header -->
    <header class="bg-brand-600 text-white px-4 pb-6 pt-8">
        <h1 class="text-xl font-bold">Shift Swaps</h1>
        <p class="text-sm text-brand-200 mt-1">Manage your swap requests</p>
    </header>

    <div class="px-4 -mt-4 space-y-6">
        <!-- Incoming Requests -->
        @if($incomingRequests->isNotEmpty())
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Incoming Requests</h2>
                <div class="space-y-3">
                    @foreach($incomingRequests as $request)
                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-amber-300">
                                        {{ $request->requestingShift->user->full_name ?? 'Unknown' }}
                                    </p>
                                    <p class="text-sm text-amber-400/70">
                                        Wants to swap: {{ $request->requestingShift->date->format('M d') }}
                                        ({{ $request->requestingShift->start_time->format('g:i A') }} - {{ $request->requestingShift->end_time->format('g:i A') }})
                                    </p>
                                    @if($request->reason)
                                        <p class="text-xs text-amber-400/50 mt-1">"{{ $request->reason }}"</p>
                                    @endif
                                    <div class="flex gap-2 mt-3">
                                        <form action="{{ route('shift-swaps.accept', $request) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-400 transition-colors">
                                                Accept
                                            </button>
                                        </form>
                                        <form action="{{ route('shift-swaps.reject', $request) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-colors">
                                                Decline
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Outgoing Requests -->
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">My Requests</h2>
            @if($outgoingRequests->isEmpty())
                <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 text-center">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No swap requests yet</p>
                    <p class="text-xs text-gray-600 mt-1">Go to My Shifts to request a swap</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($outgoingRequests as $request)
                        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                                    @switch($request->status->value)
                                        @case('pending') bg-amber-500/20 @break
                                        @case('accepted') bg-green-500/20 @break
                                        @case('rejected') bg-red-500/20 @break
                                        @case('cancelled') bg-gray-700 @break
                                        @default bg-gray-700
                                    @endswitch
                                ">
                                    @switch($request->status->value)
                                        @case('pending')
                                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            @break
                                        @case('accepted')
                                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            @break
                                        @case('rejected')
                                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                    @endswitch
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="font-medium text-white">
                                                {{ $request->requestingShift->date->format('M d, Y') }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ $request->requestingShift->start_time->format('g:i A') }} - {{ $request->requestingShift->end_time->format('g:i A') }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @switch($request->status->value)
                                                @case('pending') bg-amber-500/20 text-amber-400 @break
                                                @case('accepted') bg-green-500/20 text-green-400 @break
                                                @case('rejected') bg-red-500/20 text-red-400 @break
                                                @case('cancelled') bg-gray-700 text-gray-400 @break
                                                @default bg-gray-700 text-gray-400
                                            @endswitch
                                        ">
                                            {{ $request->status->label() }}
                                        </span>
                                    </div>
                                    @if($request->targetShift && $request->targetShift->user)
                                        <p class="text-xs text-gray-600 mt-1">
                                            Requested to: {{ $request->targetShift->user->full_name }}
                                        </p>
                                    @endif
                                    @if($request->status->value === 'pending')
                                        <form action="{{ route('shift-swaps.cancel', $request) }}" method="POST" class="mt-2">
                                            @csrf
                                            <button type="submit" class="text-xs text-red-400 hover:text-red-300">
                                                Cancel Request
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="h-6"></div>
</x-layouts.mobile>
