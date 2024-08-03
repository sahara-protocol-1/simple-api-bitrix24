<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connector;

use SimpleApiBitrix24\Brokers\HttpBroker;
use SimpleApiBitrix24\Brokers\SQLiteBroker;
use SimpleApiBitrix24\Exception\ConnectorException;
use SimpleApiBitrix24\Exception\RefreshTokenException;

class TokenConnector implements ConnectorInterface
{
    private int $refreshTokenAttempts = 0;
    private int $attemptsLimit = 10;
    private ?string $accessToken = null;
    private string $refreshToken;
    private string $clientEndpoint;
    private string $memberId;
    private array $settings;
    private string $refreshDomain = 'https://oauth.bitrix.info/oauth/token/';
    private SQLiteBroker $sqliteBroker;
    private HttpBroker $httpBroker;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->sqliteBroker = SQLiteBroker::getInstance($settings);
        $this->httpBroker = HttpBroker::getInstance();

        if (! empty($settings['app_settings']['member_id'])) {
            $this->connectToPortal($settings['app_settings']['member_id']);
        }
    }

    public function connectToPortal($memberId): void
    {
        $userData = $this->sqliteBroker->getUser($memberId);
        $this->accessToken = $userData['access_token'];
        $this->refreshToken = $userData['refresh_token'];
        $this->clientEndpoint = $userData['client_endpoint'];
        $this->memberId = $memberId;
    }

    public function request(string $method, array $params, bool $getCurlInfo = false): array
    {
        $this->validateConnectorParam();
        
        $url = "{$this->clientEndpoint}{$method}.".Connector::TRANSPORT_TYPE;
        $params['auth'] = $this->accessToken;
        $data = http_build_query($params);

        $result = $this->httpBroker->request($url, $data, $getCurlInfo);

        $expiredToken = $result['error'] ?? false;
        if ($expiredToken == 'expired_token') {
            if ($this->refreshTokens()) {
                unset($params['auth']);
                $result = $this->request($method, $params, $getCurlInfo);
            }
        }
        
        return $result;
    }

    public function requestBatch(array $query, bool $getCurlInfo = false): array
    {
        $this->validateConnectorParam();

        $url = "{$this->clientEndpoint}batch.".Connector::TRANSPORT_TYPE;

        $httpQuery = [];
        foreach($query as $key => $value) {
            $httpQuery[] = $value['method'] . '?' . http_build_query($value['params']);
        }

        $data['cmd'] = $httpQuery;
        $data['halt'] = 0;
        $data['auth'] = $this->accessToken;
        $data = http_build_query($data);
        
        $result = $this->httpBroker->request($url, $data, $getCurlInfo);

        $expiredToken = $result['error'] ?? false;
        if ($expiredToken == 'expired_token') {
            if ($this->refreshTokens()) {
                $result = $this->requestBatch($query, $getCurlInfo);
            }
        }
        
        return $result;
    }

    private function refreshTokens()
    {
        if ($this->refreshTokenAttempts >= $this->attemptsLimit) {
            throw new RefreshTokenException("refresh tokens: can't refresh tokens, invalid grant");
        }
        $this->refreshTokenAttempts++;
        $url = $this->refreshDomain;

        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->settings['app_settings']['client_id'],
            'client_secret' => $this->settings['app_settings']['client_secret'],
            'refresh_token' => $this->refreshToken
        ];
        $data = http_build_query($params);

        $result = $this->httpBroker->request($url, $data);
        
        $error = $result['error'] ?? false;
        if ($error == 'invalid_grant' ) {
            throw new RefreshTokenException('refresh tokens: invalid grant');
        }

        $updateResult = $this->sqliteBroker->updateUserTokens($result);
        
        $this->connectToPortal($this->memberId);

        return $updateResult;
    }

    private function validateConnectorParam(): void
    {
        if ($this->accessToken === null) {
            throw new ConnectorException("use method ApiBitrix24::connectTo('memberId') befor request");
        }
    }

}
