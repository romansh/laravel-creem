<?php

use Romansh\LaravelCreem\Http\Controllers\WebhookController;
use Romansh\LaravelCreem\Http\Middleware\VerifyCreemWebhook;
use Illuminate\Support\Facades\Route;

Route::post(config('creem.webhook.path', '/creem/webhook'), WebhookController::class)
    ->middleware([
        ...config('creem.webhook.middleware', ['api']),
        VerifyCreemWebhook::class,
    ])
    ->name('creem.webhook');
