<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connector;

abstract class Connector
{
    const TRANSPORT_TYPE = 'json';
    private static ConnectorInterface $instance;

    private function __construct()
    {
    }

    public static function defineInstance(string $type, array $settings): ConnectorInterface
    {
        if (empty(self::$instance)) {
            self::$instance = match($type) {
                'webhook' => new WebhookConnector($settings),
                'token' => new TokenConnector($settings)
            };
        }

        return self::$instance;
    }

    
}