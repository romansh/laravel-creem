<?php

namespace Romansh\LaravelCreem\Services\Concerns;

trait NormalizesPaginatedResponses
{
    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    protected function normalizePaginatedResponse(array $response): array
    {
        if (! isset($response['total']) && isset($response['pagination']['total_records'])) {
            $response['total'] = $response['pagination']['total_records'];
        }

        return $response;
    }
}