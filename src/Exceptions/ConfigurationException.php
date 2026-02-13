<?php

namespace Romansh\LaravelCreem\Exceptions;

/**
 * Exception thrown when there's a configuration error.
 */
class ConfigurationException extends CreemException
{
    /**
     * Create an exception for a missing profile.
     */
    public static function profileNotFound(string $profile): self
    {
        return new self("Creem profile '{$profile}' not found in configuration.");
    }

    /**
     * Create an exception for a missing API key.
     */
    public static function missingApiKey(): self
    {
        return new self('Creem API key is required but not configured.');
    }

    /**
     * Create an exception for invalid inline configuration.
     */
    public static function invalidInlineConfig(array $missing): self
    {
        $fields = implode(', ', $missing);

        return new self("Invalid inline configuration. Missing required fields: {$fields}");
    }
}
