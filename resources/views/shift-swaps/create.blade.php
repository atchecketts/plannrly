<x-layouts.app title="Request Shift Swap">
    <div class="max-w-2xl">
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Request Shift Swap</h2>
                        <p class="mt-1 text-sm text-gray-400">Request to swap your shift with another employee.</p>
                    </div>
                    <a href="{{ route('shift-swaps.index') }}" class="text-gray-400 hover:text-gray-300 text-sm">
                        Back to list
                    </a>
                </div>
            </div>
        </div>

        <!-- Your Shift Details -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-sm font-medium text-gray-300">Your Shift</h3>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center gap-4">
                    @if($shift->businessRole)
                        <span class="inline-block h-4 w-4 rounded-full" style="background-color: {{ $shift->businessRole->color }}"></span>
                    @endif
                    <div>
                        <p class="text-white font-medium">{{ $shift->date->format('l, M d, Y') }}</p>
                        <p class="text-gray-400 text-sm">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</p>
                        @if($shift->businessRole)
                            <p class="text-gray-500 text-sm mt-1">{{ $shift->businessRole->name }}</p>
                        @endif
                        @if($shift->department)
                            <p class="text-gray-500 text-sm">{{ $shift->department->name }} @ {{ $shift->location?->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Swap Request Form -->
        <form action="{{ route('shift-swaps.store') }}" method="POST" class="bg-gray-900 rounded-lg border border-gray-800">
            @csrf
            <input type="hidden" name="requesting_shift_id" value="{{ $shift->id }}">

            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-sm font-medium text-gray-300">Swap Details</h3>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- Target User -->
                <div>
                    <label for="target_user_id" class="block text-sm font-medium text-gray-300 mb-2">
                        Swap with Employee <span class="text-red-400">*</span>
                    </label>
                    <select name="target_user_id" id="target_user_id" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">Select an employee...</option>
                        @foreach($eligibleUsers as $user)
                            <option value="{{ $user->id }}" {{ old('target_user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('target_user_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @if($eligibleUsers->isEmpty())
                        <p class="mt-1 text-sm text-amber-400">No eligible employees found for this role.</p>
                    @endif
                </div>

                <!-- Target Shift (Optional) -->
                <div>
                    <label for="target_shift_id" class="block text-sm font-medium text-gray-300 mb-2">
                        Their Shift to Take <span class="text-gray-500">(optional)</span>
                    </label>
                    <select name="target_shift_id" id="target_shift_id"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">None - just give away my shift</option>
                        @foreach($eligibleShifts as $eligibleShift)
                            <option value="{{ $eligibleShift->id }}" {{ old('target_shift_id') == $eligibleShift->id ? 'selected' : '' }}>
                                {{ $eligibleShift->user->full_name }} - {{ $eligibleShift->date->format('D, M d') }} ({{ $eligibleShift->start_time->format('H:i') }} - {{ $eligibleShift->end_time->format('H:i') }})
                            </option>
                        @endforeach
                    </select>
                    @error('target_shift_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Leave empty if you just want to give away your shift without taking one in return.</p>
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-300 mb-2">
                        Reason <span class="text-gray-500">(optional)</span>
                    </label>
                    <textarea name="reason" id="reason" rows="3"
                              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                              placeholder="Why do you need to swap this shift?">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-800/50 rounded-b-lg flex items-center justify-end gap-3">
                <a href="{{ route('shift-swaps.index') }}" class="px-4 py-2.5 text-gray-400 hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                    Send Swap Request
                </button>
            </div>
        </form>

        <!-- Help Text -->
        <div class="mt-6 bg-gray-900/50 rounded-lg border border-gray-800 p-4">
            <h4 class="text-sm font-medium text-gray-300 mb-2">How shift swaps work</h4>
            <ol class="text-sm text-gray-500 space-y-1 list-decimal list-inside">
                <li>You send a swap request to another employee</li>
                <li>They accept or reject your request</li>
                @if(auth()->user()->tenant?->tenantSettings?->require_admin_approval_for_swaps ?? true)
                    <li>If accepted, an admin must approve the swap</li>
                    <li>Once approved, the shifts are automatically swapped</li>
                @else
                    <li>Once accepted, the shifts are automatically swapped</li>
                @endif
            </ol>
        </div>
    </div>
</x-layouts.app>
