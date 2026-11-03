<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ShopReviewController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\ShopApplicationController;
use App\Http\Controllers\Customer\ShopProfileController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('shop/apply', [ShopApplicationController::class, 'create'])->name('shop.apply');
    Route::post('shop', [ShopApplicationController::class, 'store'])->name('shop.store');
    Route::post('shop/resubmit', [ShopApplicationController::class, 'resubmit'])->name('shop.resubmit');

    Route::get('shop/edit', [ShopProfileController::class, 'edit'])->name('shop.edit');
    Route::patch('shop', [ShopProfileController::class, 'update'])->name('shop.update');
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

        Route::get('shops', [ShopReviewController::class, 'index'])->name('shops.index');
        Route::patch('shops/{shop}/status', [ShopReviewController::class, 'updateStatus'])->name('shops.status');

        Route::resource('categories', CategoryController::class)->except(['show']);
    });

require __DIR__.'/settings.php';
