<x-layouts.mobile title="My Leave" active="profile" :showHeader="false">
    <!-- Status Bar Spacer -->
    <div class="bg-brand-900 h-6"></div>

    <!-- Header -->
    <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">My Leave</h1>
                <p class="text-sm text-brand-200 mt-1">View and request time off</p>
            </div>
            <a href="{{ route('my-leave.create') }}" class="p-2 bg-white/20 rounded-lg hover:bg-white/30 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </a>
        </div>
    </header>

    <div class="px-4 -mt-4 space-y-6">
        <!-- Pending Requests Alert -->
        @if($pendingRequests->isNotEmpty())
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-amber-300">{{ $pendingRequests->count() }} Pending Request(s)</p>
                        <p class="text-sm text-amber-400/70">Awaiting manager approval</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Upcoming Approved Leave -->
        @if($upcomingApproved->isNotEmpty())
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Upcoming Leave</h2>
                <div class="space-y-3">
                    @foreach($upcomingApproved as $request)
                        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-white">{{ $request->leaveType->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $request->start_date->format('M d') }}
                                        @if(!$request->start_date->eq($request->end_date))
                                            - {{ $request->end_date->format('M d') }}
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs font-medium rounded-full">
                                            Approved
                                        </span>
                                        <span class="text-xs text-gray-600">{{ $request->total_days }} day(s)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- All Leave Requests -->
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">All Requests</h2>
            @if($leaveRequests->isEmpty())
                <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 text-center">
                    <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No leave requests</p>
                    <a href="{{ route('my-leave.create') }}" class="inline-block mt-3 text-sm text-brand-400 font-medium">
                        Request Time Off
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($leaveRequests as $request)
                        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-white">{{ $request->leaveType->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $request->start_date->format('M d, Y') }}
                                        @if(!$request->start_date->eq($request->end_date))
                                            - {{ $request->end_date->format('M d, Y') }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $request->total_days }} day(s)</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @switch($request->status->value)
                                        @case('draft') bg-gray-700 text-gray-300 @break
                                        @case('requested') bg-amber-500/20 text-amber-400 @break
                                        @case('approved') bg-green-500/20 text-green-400 @break
                                        @case('rejected') bg-red-500/20 text-red-400 @break
                                        @default bg-gray-700 text-gray-300
                                    @endswitch
                                ">
                                    {{ $request->status->label() }}
                                </span>
                            </div>
                            @if($request->reason)
                                <p class="text-xs text-gray-600 mt-2">"{{ Str::limit($request->reason, 80) }}"</p>
                            @endif
                            @if($request->review_notes)
                                <p class="text-xs text-gray-500 mt-2 italic">Manager: {{ $request->review_notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="h-6"></div>
</x-layouts.mobile>
