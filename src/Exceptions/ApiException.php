<?php

namespace Romansh\LaravelCreem\Exceptions;

/**
 * Exception thrown when the Creem API returns an error response.
 */
class ApiException extends CreemException
{
    /**
     * The HTTP status code.
     */
    protected int $statusCode;

    /**
     * The error category.
     */
    protected string $error;

    /**
     * Array of error messages.
     *
     * @var string[]
     */
    protected array $messages;

    /**
     * Create a new API exception from response data.
     */
    public static function fromResponse(array $data, int $statusCode): self
    {
        $messages = $data['message'] ?? ['Unknown error'];
        $error = $data['error'] ?? 'Error';

        $exception = new self(
            sprintf('%s: %s', $error, implode(', ', $messages)),
            $statusCode
        );

        $exception->statusCode = $statusCode;
        $exception->error = $error;
        $exception->messages = $messages;

        if (isset($data['trace_id'])) {
            $exception->setTraceId($data['trace_id']);
        }

        return $exception;
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error category.
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Get all error messages.
     *
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
