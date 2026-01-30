<?php

namespace App\Http\Controllers;

use App\Enums\FeatureAddon;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;

class FeatureController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    public function status(): JsonResponse
    {
        $user = auth()->user();

        if (! $user || ! $user->tenant) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $tenant = $user->tenant;
        $featureStatus = $this->subscriptionService->getFeatureStatus($tenant);

        return response()->json([
            'plan' => $tenant->subscription?->plan?->value,
            'status' => $tenant->subscription?->status?->value,
            'features' => $featureStatus,
        ]);
    }

    public function check(string $feature): JsonResponse
    {
        $user = auth()->user();

        if (! $user || ! $user->tenant) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $featureEnum = FeatureAddon::tryFrom($feature);

        if (! $featureEnum) {
            return response()->json([
                'error' => 'Unknown feature',
                'feature' => $feature,
            ], 400);
        }

        $hasFeature = $user->tenant->hasFeature($featureEnum);

        return response()->json([
            'feature' => $feature,
            'enabled' => $hasFeature,
            'label' => $featureEnum->label(),
        ]);
    }
}
