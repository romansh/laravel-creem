<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\Http\Middleware\VerifyCreemWebhook;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifyCreemWebhookTest extends TestCase
{
    private VerifyCreemWebhook $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new VerifyCreemWebhook();
    }

    public function test_handle_passes_with_valid_signature()
    {
        $payload = json_encode(['event' => 'checkout.completed']);
        $secret = 'test_secret';
        $signature = hash_hmac('sha256', $payload, $secret);

        config(['creem.profiles.default.webhook_secret' => $secret]);

        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Creem-Signature', $signature);

        $response = $this->middleware->handle($request, function ($req) {
            return response('next called', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('next called', $response->getContent());
    }

    public function test_handle_aborts_when_config_secret_is_missing()
    {
        config(['creem.profiles.default.webhook_secret' => null]);

        $request = Request::create('/webhook', 'POST');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Webhook secret not configured.');

        $this->middleware->handle($request, fn ($r) => null);
    }

    public function test_handle_aborts_when_signature_header_is_missing()
    {
        config(['creem.profiles.default.webhook_secret' => 'test_secret']);

        $request = Request::create('/webhook', 'POST');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Missing webhook signature.');

        $this->middleware->handle($request, fn ($r) => null);
    }

    public function test_handle_aborts_on_invalid_signature()
    {
        config(['creem.profiles.default.webhook_secret' => 'test_secret']);

        $payload = json_encode(['event' => 'checkout.completed']);
        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Creem-Signature', 'invalid_hash');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid webhook signature.');

        $this->middleware->handle($request, fn ($r) => null);
    }

    /**
     * Test logic for dynamic profiles and the null-coalesce operator.
     */
    public function test_handle_works_with_custom_profile()
    {
        $payload = json_encode(['event' => 'test']);
        $secret = 'custom_secret';
        $signature = hash_hmac('sha256', $payload, $secret);

        config(['creem.profiles.billing.webhook_secret' => $secret]);

        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Creem-Signature', $signature);

        $response = $this->middleware->handle($request, function ($req) {
            return response('success');
        }, 'billing');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
