<?php

namespace App\Policies;

use App\Models\StaffingRequirement;
use App\Models\User;

class StaffingRequirementPolicy
{
    /**
     * Determine whether the user can view any staffing requirements.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isLocationAdmin();
    }

    /**
     * Determine whether the user can view the staffing requirement.
     */
    public function view(User $user, StaffingRequirement $staffingRequirement): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $staffingRequirement->tenant_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isLocationAdmin()) {
            // Location admins can view requirements for their locations
            if ($staffingRequirement->location_id === null) {
                return true;
            }

            return in_array($staffingRequirement->location_id, $user->getManagedLocationIds());
        }

        return false;
    }

    /**
     * Determine whether the user can create staffing requirements.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the staffing requirement.
     */
    public function update(User $user, StaffingRequirement $staffingRequirement): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->tenant_id === $staffingRequirement->tenant_id;
    }

    /**
     * Determine whether the user can delete the staffing requirement.
     */
    public function delete(User $user, StaffingRequirement $staffingRequirement): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->tenant_id === $staffingRequirement->tenant_id;
    }
}
