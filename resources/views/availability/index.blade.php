<x-layouts.app title="My Availability">
    <div class="max-w-6xl">
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">My Availability</h2>
                    <p class="mt-1 text-sm text-gray-400">Set when you're available or unavailable to work.</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <a href="{{ route('availability.edit') }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Add Availability
                    </a>
                </div>
            </div>
        </div>

        {{-- Weekly Overview --}}
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">Weekly Overview</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-7 gap-2">
                    @foreach($weeklySummary as $dayIndex => $day)
                        <div class="text-center">
                            <p class="text-xs font-medium text-gray-400 mb-2">{{ substr($day['day'], 0, 3) }}</p>
                            @if(empty($day['slots']))
                                <div class="h-16 bg-gray-800 rounded flex items-center justify-center">
                                    <span class="text-xs text-gray-500">Not set</span>
                                </div>
                            @else
                                <div class="space-y-1">
                                    @foreach($day['slots'] as $slot)
                                        <div class="p-1 rounded text-xs
                                            @switch($slot['preference_level']->color())
                                                @case('green') bg-green-500/20 text-green-400 @break
                                                @case('blue') bg-blue-500/20 text-blue-400 @break
                                                @case('yellow') bg-yellow-500/20 text-yellow-400 @break
                                                @case('red') bg-red-500/20 text-red-400 @break
                                                @default bg-gray-800 text-gray-400
                                            @endswitch
                                        ">
                                            {{ $slot['time_range'] === 'All day' ? 'All' : explode(' - ', $slot['time_range'])[0] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recurring Rules --}}
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">Recurring Weekly Availability</h3>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($availabilityRules->where('type.value', 'recurring') as $rule)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @switch($rule->preference_level->color())
                                    @case('green') bg-green-500/10 text-green-400 @break
                                    @case('blue') bg-blue-500/10 text-blue-400 @break
                                    @case('yellow') bg-yellow-500/10 text-yellow-400 @break
                                    @case('red') bg-red-500/10 text-red-400 @break
                                    @default bg-gray-500/10 text-gray-400
                                @endswitch
                            ">
                                {{ $rule->preference_level->label() }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $rule->day_name }}</p>
                                <p class="text-xs text-gray-400">{{ $rule->time_range }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($rule->notes)
                                <span class="text-xs text-gray-500" title="{{ $rule->notes }}">Has notes</span>
                            @endif
                            <form action="{{ route('availability.destroy', $rule) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm" onclick="return confirm('Delete this availability rule?')">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400">No recurring availability set.</p>
                        <a href="{{ route('availability.edit') }}" class="mt-2 inline-block text-sm text-brand-400 hover:text-brand-300">Add your weekly availability</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Specific Date Exceptions --}}
        <div class="bg-gray-900 rounded-lg border border-gray-800">
            <div class="px-6 py-4 border-b border-gray-800">
                <h3 class="text-base font-semibold text-white">Specific Date Exceptions</h3>
                <p class="text-sm text-gray-400">Override your regular availability for specific dates.</p>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($availabilityRules->where('type.value', 'specific_date') as $rule)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @switch($rule->preference_level->color())
                                    @case('green') bg-green-500/10 text-green-400 @break
                                    @case('blue') bg-blue-500/10 text-blue-400 @break
                                    @case('yellow') bg-yellow-500/10 text-yellow-400 @break
                                    @case('red') bg-red-500/10 text-red-400 @break
                                    @default bg-gray-500/10 text-gray-400
                                @endswitch
                            ">
                                {{ $rule->preference_level->label() }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $rule->specific_date->format('l, d M Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $rule->time_range }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($rule->notes)
                                <span class="text-xs text-gray-500" title="{{ $rule->notes }}">{{ Str::limit($rule->notes, 30) }}</span>
                            @endif
                            <form action="{{ route('availability.destroy', $rule) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm" onclick="return confirm('Delete this availability rule?')">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400">No specific date exceptions set.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
