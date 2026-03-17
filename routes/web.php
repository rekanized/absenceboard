<?php

use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureCurrentUser;
use Illuminate\Support\Facades\Route;

use App\Livewire\VacationPlanner;

Route::middleware(EnsureCurrentUser::class)->group(function () {
	Route::get('/', VacationPlanner::class)->name('planner');

	Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
	Route::post('/admin/application-name', [AdminController::class, 'updateApplicationName'])->name('admin.application-name.update');
	Route::post('/admin/impersonate', [AdminController::class, 'impersonate'])->name('admin.impersonate');
	Route::post('/admin/absence-options', [AdminController::class, 'storeAbsenceOption'])->name('admin.absence-options.store');
});
