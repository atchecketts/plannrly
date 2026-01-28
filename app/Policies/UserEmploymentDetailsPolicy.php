<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserEmploymentDetails;

class UserEmploymentDetailsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserEmploymentDetails $employmentDetails): bool
    {
        if ($user->id === $employmentDetails->user_id) {
            return true;
        }

        return $user->canManageUser($employmentDetails->user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserEmploymentDetails $employmentDetails): bool
    {
        return $user->canManageUser($employmentDetails->user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserEmploymentDetails $employmentDetails): bool
    {
        return $user->canManageUser($employmentDetails->user);
    }
}
