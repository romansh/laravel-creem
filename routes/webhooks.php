<?php

use Illuminate\Support\Facades\Route;
use Romansh\LaravelCreem\Http\Controllers\WebhookController;
use Romansh\LaravelCreem\Http\Middleware\VerifyCreemWebhook;

// Use Route::group with callback to defer config() calls until routes are actually registered
Route::group([], function () {
    Route::post(config('creem.webhook.path', '/creem/webhook'), WebhookController::class)
        ->middleware([
            ...config('creem.webhook.middleware', ['api']),
            VerifyCreemWebhook::class,
        ])
        ->name('creem.webhook');
});
