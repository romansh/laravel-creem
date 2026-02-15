<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Romansh\LaravelCreem\Facades\Creem;

/*
|--------------------------------------------------------------------------
| Example API Routes for Creem Integration
|--------------------------------------------------------------------------
*/

// List all products
Route::get('/products', function () {
    return Creem::products()->list();
});

// Get a specific product
Route::get('/products/{id}', function (string $id) {
    return Creem::products()->find($id);
});

// List all customers
Route::get('/customers', function () {
    return Creem::customers()->list();
});

// Get customer portal link
Route::post('/customers/{id}/portal', function (string $id) {
    $link = Creem::customers()->createPortalLink($id);

    return response()->json(['portal_url' => $link]);
});

// Get subscription details
Route::get('/subscriptions/{id}', function (string $id) {
    return Creem::subscriptions()->find($id);
});

// Cancel subscription
Route::post('/subscriptions/{id}/cancel', function (string $id) {
    return Creem::subscriptions()->cancel($id);
});

// Pause subscription
Route::post('/subscriptions/{id}/pause', function (string $id) {
    return Creem::subscriptions()->pause($id);
});

// Resume subscription
Route::post('/subscriptions/{id}/resume', function (string $id) {
    return Creem::subscriptions()->resume($id);
});

// Upgrade subscription
Route::post('/subscriptions/{id}/upgrade', function (Request $request, string $id) {
    $productId = $request->input('product_id');

    return Creem::subscriptions()->upgrade($id, $productId);
});
