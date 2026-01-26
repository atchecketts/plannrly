<x-layouts.mobile title="Request Swap" active="swaps" :showHeader="false">
    <!-- Status Bar Spacer -->
    <div class="bg-brand-900 h-6"></div>

    <!-- Header -->
    <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('my-swaps.index') }}" class="p-2 -ml-2 bg-white/10 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold">Request Swap</h1>
                <p class="text-sm text-brand-200">Find someone to cover your shift</p>
            </div>
        </div>
    </header>

    <div class="px-4 -mt-4 space-y-4">
        <!-- Shift Details -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Shift to Swap</h2>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-brand-600/20 rounded-xl flex flex-col items-center justify-center border border-brand-700/50">
                    <span class="text-xs font-medium text-brand-300">{{ $shift->date->format('D') }}</span>
                    <span class="text-lg font-bold text-brand-400">{{ $shift->date->format('d') }}</span>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-white">{{ $shift->date->format('l, M d') }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                    </p>
                    <p class="text-xs text-gray-600 mt-0.5">
                        @if($shift->department)
                            {{ $shift->department->name }}
                        @endif
                        @if($shift->businessRole)
                            @if($shift->department) &bull; @endif
                            {{ $shift->businessRole->name }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Swap Form -->
        <form action="{{ route('my-swaps.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="requesting_shift_id" value="{{ $shift->id }}">

            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-800">
                    <h2 class="font-semibold text-white">Request Details</h2>
                </div>
                <div class="p-4 space-y-4">
                    @if($availableUsers->isNotEmpty())
                        <div>
                            <label for="target_user_id" class="block text-xs font-medium text-gray-400 mb-2">
                                Send request to (optional)
                            </label>
                            <select name="target_user_id" id="target_user_id"
                                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                                <option value="">Anyone with matching role</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}" {{ old('target_user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('target_user_id')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-600 mt-1">
                                Leave empty to request from anyone with the same role
                            </p>
                        </div>
                    @else
                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-3">
                            <p class="text-sm text-amber-400">
                                No other employees with the same role available. Your request will be open to anyone.
                            </p>
                        </div>
                    @endif

                    <div>
                        <label for="reason" class="block text-xs font-medium text-gray-400 mb-2">
                            Reason (optional)
                        </label>
                        <textarea name="reason" id="reason" rows="3"
                                  placeholder="Let others know why you need this swap..."
                                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('my-swaps.index') }}"
                   class="flex items-center justify-center py-3 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="flex items-center justify-center py-3 bg-brand-900 text-white font-medium rounded-lg hover:bg-brand-800 transition-colors">
                    Submit Request
                </button>
            </div>
        </form>
    </div>

    <div class="h-6"></div>
</x-layouts.mobile>
