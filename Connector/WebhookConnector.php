<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connector;

use SimpleApiBitrix24\Brokers\HttpBroker;
use SimpleApiBitrix24\Exception\ConnectorException;

class WebhookConnector implements ConnectorInterface
{
    private ?string $webhook = null;
    private HttpBroker $httpBroker;

    public function __construct(array $settings)
    {
        $this->webhook = $settings['app_settings']['webhook'];
        $this->httpBroker = HttpBroker::getInstance();
    }

    public function request(string $method, array $params, bool $getCurlInfo = false): array
    {
        $this->validateConnectorParam();

        $url = "{$this->webhook}{$method}.".Connector::TRANSPORT_TYPE;
        $data = http_build_query($params);
        return $this->httpBroker->request($url, $data, $getCurlInfo);
    }

    public function requestBatch(array $query, bool $getCurlInfo = false): array
    {
        $this->validateConnectorParam();

        $url = "{$this->webhook}batch.".Connector::TRANSPORT_TYPE;

        $httpQuery = [];
        foreach($query as $key => $value) {
            $httpQuery[] = $value['method'] . '?' . http_build_query($value['params']);
        }

        $data['cmd'] = $httpQuery;
        $data['halt'] = 0;
        $data = http_build_query($data);

        return $this->httpBroker->request($url, $data, $getCurlInfo);
    }

    public function connectToPortal(string $webhook): void
    {
        $this->webhook = $webhook;
        $this->validateConnectorParam($webhook);
    }

    private function validateConnectorParam():void
    {
        if ($this->webhook === null || empty($this->webhook)) {
            throw new ConnectorException("add webhook in ApiSettings or use method ApiBitrix24::connectTo('webhook') before request");
        }

        if (! filter_var($this->webhook, FILTER_VALIDATE_URL)) {
            throw new ConnectorException('Webhook is incorrect. You need to create and copy a webhook from Bitrix24');
        }
    }



}
