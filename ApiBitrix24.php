<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;
use SimpleApiBitrix24\Brokers\InstallationBroker;
use SimpleApiBitrix24\Connector\Connector;
use SimpleApiBitrix24\Connector\ConnectorInterface;
use SimpleApiBitrix24\Exception\ConnectionTypeException;


class ApiBitrix24
{
    public const APP_INFO = 'Simple api bitrix24 v1.01';
    private array $settings;
    private ConnectorInterface $connector;
    private ?InstallationBroker $installationBroker = null;

    public function __construct(ApiSettings $apiSettings)
    {
        $this->settings = $apiSettings->getSettings();
        $this->checkSettings($this->settings);
        $this->setConnector($this->settings);
    }

    private function checkSettings(array $settings): void
    {
        $trigger = 0;

        foreach ($settings['app_type'] as $key => $value) {
            ($value == true) ? $trigger++ : null;
        }

        if ($trigger == 0 || $trigger > 1) {
            throw new ConnectionTypeException('choose only one app type');
        }
    }

    private function setConnector(array $settings): void
    {
        $type = array_keys($settings['app_type'], true)[0];
        $this->connector = Connector::defineInstance($type, $settings);
    }

    public function call(string $method, array $params = [], bool $getCurlInfo = false): array
    {
        return $this->connector->request($method, $params, $getCurlInfo);
    }

    public function callBatch(array $query, bool $getCurlInfo = false): array
    {
        return $this->connector->requestBatch($query, $getCurlInfo);
    }

    public function connectTo(string $webhookUrlOrMemberId): void
    {
        $this->connector->connectToPortal($webhookUrlOrMemberId);
    }

    public function installStart(InstallationAppSettings $installationAppSettings): void
    {
        $data = $installationAppSettings->getSettings();

        $this->installationBroker = InstallationBroker::getInstance($this->settings);
        $this->installationBroker->prepareTable();
        $this->installationBroker->addUser($data);
    }

    public function installFinish(): void
    {
        $this->installationBroker->finish();
    }




}
