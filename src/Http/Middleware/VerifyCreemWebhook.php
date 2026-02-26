<?php

namespace Romansh\LaravelCreem\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to verify Creem webhook signatures.
 */
class VerifyCreemWebhook
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $profile = null): Response
    {
        $profile = $profile ?? 'default';

        $webhookSecret = config("creem.profiles.{$profile}.webhook_secret");

        if (! $webhookSecret) {
            abort(500, 'Webhook secret not configured.');
        }

        // Creem sends the signature in the 'creem-signature' header.
        $signature = $request->header('creem-signature');

        if (! $signature) {
            abort(401, 'Missing webhook signature.');
        }

        $payload = $request->getContent();
        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! hash_equals($computedSignature, $signature)) {
            abort(403, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
