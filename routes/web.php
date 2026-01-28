<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BusinessRoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmploymentDetailsController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\LeaveAllowanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftSwapController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SuperAdmin\ImpersonationController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\TenantSettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFilterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
});

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('locations', LocationController::class);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('business-roles', BusinessRoleController::class)->except(['show']);
    Route::resource('users', UserController::class);

    // Employment Details (Admin)
    Route::get('users/{user}/employment', [EmploymentDetailsController::class, 'edit'])->name('users.employment.edit');
    Route::put('users/{user}/employment', [EmploymentDetailsController::class, 'update'])->name('users.employment.update');

    // Profile (Self-Service)
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // Availability (Self-Service)
    Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::get('availability/edit', [AvailabilityController::class, 'edit'])->name('availability.edit');
    Route::post('availability', [AvailabilityController::class, 'store'])->name('availability.store');
    Route::put('availability/{availability}', [AvailabilityController::class, 'update'])->name('availability.update');
    Route::delete('availability/{availability}', [AvailabilityController::class, 'destroy'])->name('availability.destroy');
    Route::get('users/{user}/availability', [AvailabilityController::class, 'show'])->name('users.availability');

    Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('schedule/day', [ScheduleController::class, 'day'])->name('schedule.day');
    Route::get('schedule/draft-count', [ScheduleController::class, 'draftCount'])->name('schedule.draft-count');
    Route::post('schedule/publish', [ScheduleController::class, 'publishAll'])->name('schedule.publish');

    Route::post('shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
    Route::put('shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
    Route::post('shifts/{shift}/assign', [ShiftController::class, 'assign'])->name('shifts.assign');
    Route::post('shifts/{shift}/publish', [ShiftController::class, 'publish'])->name('shifts.publish');
    Route::get('shifts/{shift}/available-users', [ShiftController::class, 'availableUsers'])->name('shifts.available-users');

    Route::resource('leave-types', LeaveTypeController::class)->except(['show']);
    Route::resource('leave-allowances', LeaveAllowanceController::class)->except(['show']);
    Route::resource('leave-requests', LeaveRequestController::class)->except(['edit', 'update']);
    Route::post('leave-requests/{leaveRequest}/submit', [LeaveRequestController::class, 'submit'])->name('leave-requests.submit');
    Route::post('leave-requests/{leaveRequest}/review', [LeaveRequestController::class, 'review'])->name('leave-requests.review');

    Route::get('shift-swaps', [ShiftSwapController::class, 'index'])->name('shift-swaps.index');
    Route::get('shift-swaps/create/{shift}', [ShiftSwapController::class, 'create'])->name('shift-swaps.create');
    Route::post('shift-swaps', [ShiftSwapController::class, 'store'])->name('shift-swaps.store');
    Route::post('shift-swaps/{swapRequest}/accept', [ShiftSwapController::class, 'accept'])->name('shift-swaps.accept');
    Route::post('shift-swaps/{swapRequest}/reject', [ShiftSwapController::class, 'reject'])->name('shift-swaps.reject');
    Route::post('shift-swaps/{swapRequest}/cancel', [ShiftSwapController::class, 'cancel'])->name('shift-swaps.cancel');
    Route::post('shift-swaps/{swapRequest}/approve', [ShiftSwapController::class, 'approve'])->name('shift-swaps.approve');

    Route::post('user/filter-defaults', [UserFilterController::class, 'storeDefault'])->name('user.filter-defaults.store');
    Route::get('user/filter-defaults', [UserFilterController::class, 'getDefault'])->name('user.filter-defaults.show');

    Route::get('settings', [TenantSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [TenantSettingsController::class, 'update'])->name('settings.update');

    // Subscription management
    Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');

    // Feature API
    Route::get('api/features', [FeatureController::class, 'status'])->name('api.features.status');
    Route::get('api/features/{feature}', [FeatureController::class, 'check'])->name('api.features.check');
});

// Super Admin Routes
Route::middleware(['auth', 'super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('tenants', [SuperAdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/{tenant}', [SuperAdminTenantController::class, 'show'])->name('tenants.show');
    Route::get('tenants/{tenant}/edit', [SuperAdminTenantController::class, 'edit'])->name('tenants.edit');
    Route::put('tenants/{tenant}', [SuperAdminTenantController::class, 'update'])->name('tenants.update');
    Route::post('tenants/{tenant}/toggle-status', [SuperAdminTenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

    Route::get('users', [SuperAdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [SuperAdminUserController::class, 'show'])->name('users.show');

    Route::post('impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
});

// Stop impersonation route (accessible when impersonating)
Route::middleware('auth')->post('stop-impersonating', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

Route::prefix('samples')->group(function () {
    Route::view('/', 'samples.index');
    Route::view('/login', 'samples.login');
    Route::view('/register', 'samples.register');
    Route::view('/admin-dashboard', 'samples.admin-dashboard');
    Route::view('/schedule', 'samples.schedule');
});
