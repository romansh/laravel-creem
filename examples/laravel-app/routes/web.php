<?php

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Example Web Routes for Creem Integration
|--------------------------------------------------------------------------
*/

// Checkout routes
Route::get('/checkout/{product}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

// Example: Profile-specific checkout
Route::post('/checkout/product-a', [CheckoutController::class, 'storeWithProfile'])
    ->name('checkout.product-a');

// Example: Inline config checkout
Route::post('/checkout/custom', [CheckoutController::class, 'storeWithInlineConfig'])
    ->name('checkout.custom');
