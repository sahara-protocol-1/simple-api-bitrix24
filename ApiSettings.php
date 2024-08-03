<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;

class ApiSettings
{
    private bool $isWebhookAuth;
    private bool $isTokenAuth;
    private ?string $startWithConnection;
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $sqliteDbPath;
    private ?string $sqliteDbName;

    public function isWebhookAuth(bool $bool): ApiSettings
    {
        $this->isWebhookAuth = $bool;
        return $this;
    }

    public function isTokenAuth(bool $bool): ApiSettings
    {
        $this->isTokenAuth = $bool;
        return $this;
    }

    public function startWithConnection(string $webhookUrlOrMemberId): ApiSettings
    {
        $this->startWithConnection = $webhookUrlOrMemberId;
        return $this;
    }

    public function setClientId(string $clientId = ''): ApiSettings
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret(string $clientSecret = ''): ApiSettings
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function setSQLiteDbPath(string $sqliteDbPath = ''): ApiSettings
    {
        $this->sqliteDbPath = $sqliteDbPath;
        return $this;
    }

    public function setSQLiteDbName(string $sqliteDbName = 'Bitrix24Tokens.db'): ApiSettings
    {
        $this->sqliteDbName = $sqliteDbName;
        return $this;
    }

    public function getSettings(): array
    {
        $webhook = '';
        $memberId = '';
        if (filter_var($this->startWithConnection, FILTER_VALIDATE_URL)) {
            $webhook = $this->startWithConnection;
        } else {
            $memberId = $this->startWithConnection;
        }

        $apiSettings = [
            'app_type' => [
                'webhook' => $this->isWebhookAuth,
                'token' => $this->isTokenAuth
            ],
            'app_settings' => [
                'webhook' => $webhook,
                'member_id' => $memberId,                                    // Start app with connection to member_id
                'client_id' => $this->clientId,                              // for refresh tokens local and public app
                'client_secret' => $this->clientSecret,                      // for refresh tokens local and public app
            ],
            'database_settings' => [
                'sqlite_database_folder_path' => $this->sqliteDbPath,        // database folder path
                'sqlite_database_name' => $this->sqliteDbName                // database name
            ]
        ];

        return $apiSettings;
    }
}