<x-layouts.app title="Add Availability">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Add Availability</h2>
            <p class="mt-1 text-sm text-gray-400">Set when you're available or unavailable to work.</p>
        </div>

        <form action="{{ route('availability.store') }}" method="POST" class="space-y-6" x-data="{ type: 'recurring' }">
            @csrf

            {{-- Type Selection --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Availability Type</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:border-brand-500 transition-colors" :class="{ 'border-brand-500 ring-2 ring-brand-500/20': type === 'recurring' }">
                            <input type="radio" name="type" value="recurring" x-model="type" class="sr-only">
                            <div>
                                <p class="text-sm font-medium text-white">Recurring Weekly</p>
                                <p class="text-xs text-gray-400">Repeats every week on the same day</p>
                            </div>
                        </label>
                        <label class="relative flex items-center p-4 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:border-brand-500 transition-colors" :class="{ 'border-brand-500 ring-2 ring-brand-500/20': type === 'specific_date' }">
                            <input type="radio" name="type" value="specific_date" x-model="type" class="sr-only">
                            <div>
                                <p class="text-sm font-medium text-white">Specific Date</p>
                                <p class="text-xs text-gray-400">Applies only to a specific date</p>
                            </div>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Day/Date Selection --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white" x-text="type === 'recurring' ? 'Select Day' : 'Select Date'">Select Day</h3>
                </div>
                <div class="p-6">
                    {{-- Day of Week (for recurring) --}}
                    <div x-show="type === 'recurring'">
                        <div class="grid grid-cols-7 gap-2">
                            @foreach($days as $index => $day)
                                <label class="relative flex items-center justify-center p-3 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:border-brand-500 transition-colors text-center">
                                    <input type="radio" name="day_of_week" value="{{ $index }}" class="sr-only peer" {{ old('day_of_week') == $index ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-400 peer-checked:text-brand-400">{{ substr($day, 0, 3) }}</span>
                                    <div class="absolute inset-0 border-2 border-transparent peer-checked:border-brand-500 rounded-lg pointer-events-none"></div>
                                </label>
                            @endforeach
                        </div>
                        @error('day_of_week')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Specific Date --}}
                    <div x-show="type === 'specific_date'">
                        <x-form.input
                            name="specific_date"
                            type="date"
                            label="Date"
                            :value="old('specific_date')"
                        />
                    </div>
                </div>
            </div>

            {{-- Time Range --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Time Range</h3>
                    <p class="text-sm text-gray-400">Leave empty for all day.</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input
                            name="start_time"
                            type="time"
                            label="Start Time"
                            :value="old('start_time')"
                        />
                        <x-form.input
                            name="end_time"
                            type="time"
                            label="End Time"
                            :value="old('end_time')"
                        />
                    </div>
                </div>
            </div>

            {{-- Preference Level --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Availability Preference</h3>
                </div>
                <div class="p-6 space-y-3">
                    @foreach($preferenceLevels as $level)
                        <label class="flex items-center p-4 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:border-brand-500 transition-colors">
                            <input type="radio" name="preference_level" value="{{ $level->value }}" class="w-4 h-4 text-brand-500 bg-gray-700 border-gray-600 focus:ring-brand-500" {{ old('preference_level', 'available') === $level->value ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-white flex items-center gap-2">
                                    {{ $level->label() }}
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs
                                        @switch($level->color())
                                            @case('green') bg-green-500/10 text-green-400 @break
                                            @case('blue') bg-blue-500/10 text-blue-400 @break
                                            @case('yellow') bg-yellow-500/10 text-yellow-400 @break
                                            @case('red') bg-red-500/10 text-red-400 @break
                                        @endswitch
                                    ">
                                        {{ $level->color() }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-400">{{ $level->description() }}</p>
                            </div>
                        </label>
                    @endforeach
                    @error('preference_level')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Notes (Optional)</h3>
                </div>
                <div class="p-6">
                    <x-form.textarea
                        name="notes"
                        :value="old('notes')"
                        placeholder="Add any notes about this availability..."
                        rows="2"
                    />
                </div>
            </div>

            {{-- Effective Dates (Optional) --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Effective Period (Optional)</h3>
                    <p class="text-sm text-gray-400">Leave empty if this availability applies indefinitely.</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input
                            name="effective_from"
                            type="date"
                            label="From"
                            :value="old('effective_from')"
                        />
                        <x-form.input
                            name="effective_until"
                            type="date"
                            label="Until"
                            :value="old('effective_until')"
                        />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <x-button variant="secondary" :href="route('availability.index')">Cancel</x-button>
                <x-button type="submit">Save Availability</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
