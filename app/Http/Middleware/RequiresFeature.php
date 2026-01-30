<?php

namespace App\Http\Middleware;

use App\Enums\FeatureAddon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->tenant) {
            abort(403, 'Access denied.');
        }

        $featureEnum = FeatureAddon::tryFrom($feature);

        if (! $featureEnum) {
            abort(500, "Unknown feature: {$feature}");
        }

        if (! $user->tenant->hasFeature($featureEnum)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Feature not available',
                    'feature' => $feature,
                    'message' => "This feature requires a plan upgrade or the {$featureEnum->label()} add-on.",
                ], 403);
            }

            return redirect()
                ->route('subscription.upgrade')
                ->with('error', "This feature requires the {$featureEnum->label()} add-on or a higher plan.");
        }

        return $next($request);
    }
}
