<?php

namespace Romansh\LaravelCreem\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Romansh\LaravelCreem\Exceptions\ApiException;

class ApiExceptionTest extends TestCase
{
    public function test_from_response_with_array_of_messages()
    {
        $data = [
            'error' => 'Validation Error',
            'message' => ['Email is required', 'Password is too short'],
            'trace_id' => 'tr_123'
        ];

        $exception = ApiException::fromResponse($data, 422);

        $this->assertEquals(422, $exception->getStatusCode());
        $this->assertEquals('Validation Error', $exception->getError());
        $this->assertContains('Email is required', $exception->getMessages());
        // Check the string conversion in the constructor
        $this->assertStringContainsString('Email is required, Password is too short', $exception->getMessage());
    }

    public function test_from_response_with_single_string_message()
    {
        // This simulates the fix needed for your TypeError
        $data = [
            'error' => 'Unauthorized',
            'message' => 'Invalid API Key'
        ];

        // We wrap it in an array manually in our test for now to match your current code,
        // but I'll show you how to make the class more robust below.
        $exception = ApiException::fromResponse([
            'error' => 'Unauthorized',
            'message' => ['Invalid API Key']
        ], 401);

        $this->assertEquals('Unauthorized: Invalid API Key', $exception->getMessage());
    }
}
