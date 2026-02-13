<?php

namespace Romansh\LaravelCreem\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Artisan command for testing Creem webhook handling.
 */
class TestWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creem:test-webhook
                            {event=checkout.completed : The webhook event type}
                            {--profile=default : The configuration profile to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test webhook to your application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $event = $this->argument('event');
        $profile = $this->option('profile');

        $webhookSecret = config("creem.profiles.{$profile}.webhook_secret");

        if (! $webhookSecret) {
            $this->error("Webhook secret not configured for profile: {$profile}");

            return self::FAILURE;
        }

        $payload = $this->getTestPayload($event);
        $signature = hash_hmac('sha256', json_encode($payload), $webhookSecret);

        $url = url(config('creem.webhook.path'));

        $this->info("Sending test webhook to: {$url}");
        $this->info("Event type: {$event}");

        try {
            $response = Http::withHeaders([
                'X-Creem-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                $this->info('✓ Webhook processed successfully');

                return self::SUCCESS;
            }

            $this->error("✗ Webhook failed with status: {$response->status()}");
            $this->line($response->body());

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("✗ Failed to send webhook: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Get a test payload for the given event.
     */
    protected function getTestPayload(string $event): array
    {
        return [
            'event' => $event,
            'id' => 'test_'.uniqid(),
            'created_at' => now()->timestamp,
            'data' => [
                'object' => 'test_object',
                'id' => 'test_id_'.uniqid(),
            ],
        ];
    }
}
