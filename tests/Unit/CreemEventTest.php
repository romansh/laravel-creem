<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\Events\CheckoutCompleted;

class CreemEventTest extends TestCase
{
    public function test_creem_event_properties_are_set_from_payload()
    {
        $payload = [
            'id' => 'evt_123',
            'eventType' => 'checkout.completed',
            'created_at' => 1690000000000,
            'object' => ['order' => ['id' => 'ord_1'], 'amount' => 1000],
        ];

        $event = new CheckoutCompleted($payload);

        $this->assertSame('evt_123', $event->eventId);
        $this->assertSame('checkout.completed', $event->eventType);
        $this->assertSame(1690000000000, $event->createdAt);
        $this->assertSame($payload['object'], $event->object);
        $this->assertSame($payload, $event->payload);
    }
}
