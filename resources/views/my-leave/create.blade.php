<x-layouts.mobile title="Request Leave" active="profile" :showHeader="false">
    <!-- Status Bar Spacer -->
    <div class="bg-brand-900 h-6"></div>

    <!-- Header -->
    <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('my-leave.index') }}" class="p-2 -ml-2 bg-white/10 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold">Request Leave</h1>
                <p class="text-sm text-brand-200">Submit a new leave request</p>
            </div>
        </div>
    </header>

    <div class="px-4 -mt-4 space-y-4">
        <form action="{{ route('leave-requests.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-800">
                    <h2 class="font-semibold text-white">Leave Details</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label for="leave_type_id" class="block text-xs font-medium text-gray-400 mb-2">Leave Type</label>
                        <select name="leave_type_id" id="leave_type_id" required
                                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            <option value="">Select a leave type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="start_date" class="block text-xs font-medium text-gray-400 mb-2">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            @error('start_date')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-xs font-medium text-gray-400 mb-2">End Date</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            @error('end_date')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-400">
                            <input type="checkbox" name="start_half_day" value="1" {{ old('start_half_day') ? 'checked' : '' }}
                                   class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 rounded focus:ring-brand-500">
                            Half day (start)
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-400">
                            <input type="checkbox" name="end_half_day" value="1" {{ old('end_half_day') ? 'checked' : '' }}
                                   class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 rounded focus:ring-brand-500">
                            Half day (end)
                        </label>
                    </div>

                    <div>
                        <label for="reason" class="block text-xs font-medium text-gray-400 mb-2">Reason (optional)</label>
                        <textarea name="reason" id="reason" rows="3"
                                  placeholder="Briefly explain your leave request..."
                                  class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <button type="submit" name="submit" value="1"
                        class="w-full flex items-center justify-center py-3 bg-brand-900 text-white font-medium rounded-lg hover:bg-brand-800 transition-colors">
                    Submit for Approval
                </button>
                <button type="submit" name="submit" value="0"
                        class="w-full flex items-center justify-center py-3 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    Save as Draft
                </button>
                <a href="{{ route('my-leave.index') }}"
                   class="w-full flex items-center justify-center py-3 bg-transparent border border-gray-700 text-gray-400 font-medium rounded-lg hover:bg-gray-800 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <div class="h-6"></div>
</x-layouts.mobile>
