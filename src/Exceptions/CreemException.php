<?php

namespace Romansh\LaravelCreem\Exceptions;

use Exception;

/**
 * Base exception for all Creem-related errors.
 */
class CreemException extends Exception
{
    /**
     * The trace ID from the error response.
     */
    protected ?string $traceId = null;

    /**
     * Set the trace ID.
     */
    public function setTraceId(?string $traceId): self
    {
        $this->traceId = $traceId;

        return $this;
    }

    /**
     * Get the trace ID.
     */
    public function getTraceId(): ?string
    {
        return $this->traceId;
    }
}
