<x-layouts.app title="Upgrade Plan">
    <div class="max-w-6xl">
        <div class="mb-6">
            <a href="{{ route('subscription.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-white mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Subscription
            </a>
            <h2 class="text-lg font-semibold text-white">Upgrade Your Plan</h2>
            <p class="mt-1 text-sm text-gray-400">Choose the plan that best fits your organization's needs.</p>
        </div>

        <!-- Plan Comparison -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach($plans as $plan)
                @php
                    $isCurrent = $currentPlan === $plan;
                    $isUpgrade = $plan->order() > $currentPlan->order();
                @endphp
                <div class="bg-gray-900 rounded-lg border {{ $isCurrent ? 'border-brand-500' : 'border-gray-800' }} p-6 relative">
                    @if($isCurrent)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-brand-600 text-white text-xs font-medium rounded-full">
                            Current Plan
                        </span>
                    @endif

                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-white">{{ $plan->label() }}</h3>
                        <p class="text-sm text-gray-400 mt-1">{{ $plan->description() }}</p>
                    </div>

                    <div class="text-center mb-6">
                        <span class="text-4xl font-bold text-white">${{ $plan->monthlyPrice() }}</span>
                        <span class="text-gray-400">/month</span>
                        @if($plan->monthlyPrice() > 0)
                            <p class="text-xs text-gray-500 mt-1">or ${{ $plan->annualPrice() }}/year (save {{ round((1 - $plan->annualPrice() / ($plan->monthlyPrice() * 12)) * 100) }}%)</p>
                        @endif
                    </div>

                    <ul class="space-y-3 mb-6">
                        @foreach($features as $feature)
                            @php $hasFeature = $plan->hasFeature($feature); @endphp
                            <li class="flex items-center gap-2 text-sm">
                                @if($hasFeature)
                                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-white">{{ $feature->label() }}</span>
                                @else
                                    <svg class="w-5 h-5 text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="text-gray-500">{{ $feature->label() }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-auto">
                        @if($isCurrent)
                            <button disabled class="w-full px-4 py-2.5 bg-gray-800 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                                Current Plan
                            </button>
                        @elseif($isUpgrade)
                            <button class="w-full px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Upgrade to {{ $plan->label() }}
                            </button>
                        @else
                            <button class="w-full px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Downgrade to {{ $plan->label() }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Feature Add-ons -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <h3 class="text-base font-semibold text-white mb-2">Premium Add-ons</h3>
            <p class="text-sm text-gray-400 mb-6">Enhance your plan with individual features.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($features as $feature)
                    @php
                        $includedInPlan = $currentPlan->hasFeature($feature);
                        $hasAddon = in_array($feature, $activeAddons, true);
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center">
                                @if($feature->icon() === 'sparkles')
                                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                @elseif($feature->icon() === 'chart-bar')
                                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                @elseif($feature->icon() === 'code-bracket')
                                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $feature->label() }}</p>
                                <p class="text-xs text-gray-500">${{ $feature->monthlyPrice() }}/month</p>
                            </div>
                        </div>
                        <div>
                            @if($includedInPlan)
                                <span class="text-xs text-green-400">Included</span>
                            @elseif($hasAddon)
                                <span class="text-xs text-blue-400">Active</span>
                            @else
                                <button class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    Add
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-400">Payment Integration Coming Soon</p>
                    <p class="text-xs text-gray-400 mt-1">Stripe integration for plan upgrades and add-on purchases is currently in development. Contact support to upgrade your plan.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
