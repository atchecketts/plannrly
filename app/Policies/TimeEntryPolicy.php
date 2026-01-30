<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TimeEntry $timeEntry): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $timeEntry->tenant_id) {
            return false;
        }

        if ($timeEntry->user_id === $user->id) {
            return true;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function clockIn(User $user, TimeEntry $timeEntry): bool
    {
        return $timeEntry->user_id === $user->id;
    }

    public function clockOut(User $user, TimeEntry $timeEntry): bool
    {
        return $timeEntry->user_id === $user->id;
    }

    public function startBreak(User $user, TimeEntry $timeEntry): bool
    {
        return $timeEntry->user_id === $user->id;
    }

    public function endBreak(User $user, TimeEntry $timeEntry): bool
    {
        return $timeEntry->user_id === $user->id;
    }

    public function adjust(User $user, TimeEntry $timeEntry): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $timeEntry->tenant_id) {
            return false;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function approve(User $user, TimeEntry $timeEntry): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $timeEntry->tenant_id) {
            return false;
        }

        if ($timeEntry->isApproved()) {
            return false;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function approveMultiple(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }
}
