<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connector;


interface ConnectorInterface
{
    public function request(string $method, array $params, bool $getCurlInfo = false): array;
    public function requestBatch(array $query, bool $getCurlInfo = false): array;
    public function connectToPortal(string $memberIdOrWebhook);
}
