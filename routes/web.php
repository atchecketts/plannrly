<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BusinessRoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RotaController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ShiftSwapController;
use App\Http\Controllers\UserController;
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

    Route::resource('rotas', RotaController::class);
    Route::post('rotas/{rota}/publish', [RotaController::class, 'publish'])->name('rotas.publish');

    Route::post('shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::put('shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
    Route::post('shifts/{shift}/assign', [ShiftController::class, 'assign'])->name('shifts.assign');
    Route::get('shifts/{shift}/available-users', [ShiftController::class, 'availableUsers'])->name('shifts.available-users');
    Route::get('rotas/{rota}/shifts', [ShiftController::class, 'forRota'])->name('rotas.shifts');

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
});

Route::prefix('samples')->group(function () {
    Route::view('/', 'samples.index');
    Route::view('/login', 'samples.login');
    Route::view('/register', 'samples.register');
    Route::view('/admin-dashboard', 'samples.admin-dashboard');
    Route::view('/schedule', 'samples.schedule');
    Route::view('/employee-mobile', 'samples.employee-mobile');
});
