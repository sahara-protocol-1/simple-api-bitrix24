<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;

class InstallationAppSettings
{
    private ?string $memberId;
    private ?string $accessToken;
    private ?string $expiresIn;
    private ?string $applicationToken;
    private ?string $refreshToken;
    private ?string $domain;
    private ?string $clientEndpoint;

    public function setMemberId(string $memberId): InstallationAppSettings
    {
        $this->memberId = $memberId;
        return $this;
    }

    public function setAccessToken(string $accessToken): InstallationAppSettings
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function setExpiresIn(string $expiresIn): InstallationAppSettings
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }

    public function setRefreshToken(string $refreshToken): InstallationAppSettings
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function setDomain(string $domain): InstallationAppSettings
    {
        $this->domain = $domain;
        $this->setClientEndpoint("https://{$domain}/rest/");
        return $this;
    }

    public function setApplicationToken(string $applicationToken): InstallationAppSettings
    {
        $this->applicationToken = $applicationToken;
        return $this;
    }

    private function setClientEndpoint(string $clientEndpoint): InstallationAppSettings
    {
        $this->clientEndpoint = $clientEndpoint;
        return $this;
    }
    
    public function getSettings(): array
    {
        $installationSettings = [
            'member_id' => $this->memberId,
            'access_token' => $this->accessToken,
            'expires_in' => $this->expiresIn,
            'application_token' => $this->applicationToken,
            'refresh_token' => $this->refreshToken,
            'domain' => $this->domain,
            'client_endpoint' => $this->clientEndpoint,
        ];

        return $installationSettings;
    }

}
