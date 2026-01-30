<x-layouts.app title="Subscription">
    <div class="max-w-4xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Subscription Management</h2>
            <p class="mt-1 text-sm text-gray-400">View and manage your organization's subscription plan and features.</p>
        </div>

        <!-- Current Plan -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-white">Current Plan</h3>
                @if($subscription)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($subscription->status->value === 'active') bg-green-500/10 text-green-400
                        @elseif($subscription->status->value === 'trialing') bg-blue-500/10 text-blue-400
                        @elseif($subscription->status->value === 'past_due') bg-yellow-500/10 text-yellow-400
                        @else bg-red-500/10 text-red-400
                        @endif">
                        {{ $subscription->status->label() }}
                    </span>
                @endif
            </div>

            @if($subscription)
                <div class="flex items-baseline gap-2 mb-4">
                    <span class="text-3xl font-bold text-white">{{ $subscription->plan->label() }}</span>
                    <span class="text-gray-400">/ {{ $subscription->billing_cycle->label() }}</span>
                </div>

                <p class="text-sm text-gray-400 mb-4">{{ $subscription->plan->description() }}</p>

                @if($subscription->current_period_end)
                    <p class="text-sm text-gray-500">
                        @if($subscription->isTrialing())
                            Trial ends {{ $subscription->current_period_end->format('M j, Y') }}
                        @else
                            Current period ends {{ $subscription->current_period_end->format('M j, Y') }}
                        @endif
                    </p>
                @endif
            @else
                <p class="text-gray-400">No subscription found. Please contact support.</p>
            @endif

            <div class="mt-6 pt-6 border-t border-gray-800">
                <a href="{{ route('subscription.upgrade') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Upgrade Plan
                </a>
            </div>
        </div>

        <!-- Plan Features -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 mb-6">
            <h3 class="text-base font-semibold text-white mb-4">Plan Features</h3>

            <div class="space-y-3">
                @foreach($features as $feature)
                    @php
                        $hasFeature = $tenant->hasFeature($feature);
                        $fromPlan = $subscription?->plan->hasFeature($feature) ?? false;
                        $fromAddon = $activeAddons->contains('feature', $feature);
                    @endphp
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-800' : '' }}">
                        <div class="flex items-center gap-3">
                            @if($hasFeature)
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @endif
                            <div>
                                <p class="text-sm font-medium {{ $hasFeature ? 'text-white' : 'text-gray-500' }}">{{ $feature->label() }}</p>
                                <p class="text-xs text-gray-500">{{ $feature->description() }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($hasFeature)
                                @if($fromPlan)
                                    <span class="text-xs text-green-400">Included in plan</span>
                                @elseif($fromAddon)
                                    <span class="text-xs text-blue-400">Add-on</span>
                                @endif
                            @else
                                <span class="text-xs text-gray-500">${{ $feature->monthlyPrice() }}/mo</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Active Add-ons -->
        @if($activeAddons->isNotEmpty())
            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h3 class="text-base font-semibold text-white mb-4">Active Add-ons</h3>

                <div class="space-y-3">
                    @foreach($activeAddons as $addon)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-800' : '' }}">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $addon->feature->label() }}</p>
                                <p class="text-xs text-gray-500">
                                    Enabled {{ $addon->enabled_at->format('M j, Y') }}
                                    @if($addon->expires_at)
                                        &middot; Expires {{ $addon->expires_at->format('M j, Y') }}
                                    @endif
                                </p>
                            </div>
                            <span class="text-sm text-gray-400">${{ $addon->feature->monthlyPrice() }}/mo</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
