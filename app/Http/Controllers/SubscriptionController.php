<?php

namespace App\Http\Controllers;

use App\Enums\FeatureAddon;
use App\Enums\SubscriptionPlan;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            abort(403);
        }

        $tenant = $user->tenant;
        $subscription = $tenant->subscription;
        $activeAddons = $tenant->activeFeatureAddons()->get();

        $plans = SubscriptionPlan::cases();
        $features = FeatureAddon::cases();

        return view('subscription.index', compact(
            'tenant',
            'subscription',
            'activeAddons',
            'plans',
            'features'
        ));
    }

    public function upgrade(): View
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            abort(403);
        }

        $tenant = $user->tenant;
        $subscription = $tenant->subscription;
        $currentPlan = $subscription?->plan ?? SubscriptionPlan::Basic;

        $plans = SubscriptionPlan::cases();
        $features = FeatureAddon::cases();
        $activeAddons = $tenant->activeFeatureAddons()->pluck('feature')->all();

        return view('subscription.upgrade', compact(
            'tenant',
            'subscription',
            'currentPlan',
            'plans',
            'features',
            'activeAddons'
        ));
    }
}
