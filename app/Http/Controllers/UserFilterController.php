<?php

namespace App\Http\Controllers;

use App\Models\UserFilterDefault;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserFilterController extends Controller
{
    public function storeDefault(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filter_context' => ['required', 'string', 'max:100'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'business_role_id' => ['nullable', 'integer', 'exists:business_roles,id'],
            'group_by' => ['nullable', 'string', 'in:department,role'],
        ]);

        $user = auth()->user();

        $additionalFilters = [];
        if (isset($validated['group_by'])) {
            $additionalFilters['group_by'] = $validated['group_by'];
        }

        UserFilterDefault::updateOrCreate(
            [
                'user_id' => $user->id,
                'filter_context' => $validated['filter_context'],
            ],
            [
                'location_id' => $validated['location_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'business_role_id' => $validated['business_role_id'] ?? null,
                'additional_filters' => $additionalFilters ?: null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Filter defaults saved.']);
    }

    public function getDefault(Request $request): JsonResponse
    {
        $context = $request->query('filter_context', 'rota');

        $defaults = UserFilterDefault::where('user_id', auth()->id())
            ->where('filter_context', $context)
            ->first();

        if (! $defaults) {
            return response()->json([
                'location_id' => null,
                'department_id' => null,
                'business_role_id' => null,
                'group_by' => 'department',
            ]);
        }

        return response()->json([
            'location_id' => $defaults->location_id,
            'department_id' => $defaults->department_id,
            'business_role_id' => $defaults->business_role_id,
            'group_by' => $defaults->getFilter('group_by', 'department'),
        ]);
    }
}
