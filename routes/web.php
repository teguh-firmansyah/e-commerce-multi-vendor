<?php

use App\Http\Controllers\Customer\ShopApplicationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('shop/apply', [ShopApplicationController::class, 'create'])->name('shop.apply');
    Route::post('shop', [ShopApplicationController::class, 'store'])->name('shop.store');
});

Route::middleware(['auth', 'verified', 'seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {
        Route::inertia('dashboard', 'Seller/Dashboard')->name('dashboard');
    });

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::inertia('dashboard', 'Admin/Dashboard')->name('dashboard');
    });

require __DIR__.'/settings.php';
