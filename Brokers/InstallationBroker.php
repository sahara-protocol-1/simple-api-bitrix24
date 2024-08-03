<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Brokers;

use SimpleApiBitrix24\Exception\InstallationException;


class InstallationBroker
{
    private SQLiteBroker $sqliteBroker;
    private static InstallationBroker $installationBroker;

    private function __construct(array $settings)
    {

        $this->sqliteBroker = SQLiteBroker::getInstance($settings);
    }

    public static function getInstance($settings)
    {
        if (empty(self::$installationBroker)) {
            self::$installationBroker = new InstallationBroker($settings);
        }

        return self::$installationBroker;
    }

    private function validateRegData($data): void
    {
        if (empty($data['member_id']) || 
            empty($data['access_token']) ||
            empty($data['expires_in']) ||
            empty($data['application_token']) ||
            empty($data['refresh_token']) ||
            empty($data['domain']) ||
            empty($data['client_endpoint'])
        ) {
            throw new InstallationException('set all properties in InstallationAppSettings::class - memberId, accessToken, expiresIn, applicationToken, refreshToken, domain');
        }
    }

    public function prepareTable(): void
    {
        $this->sqliteBroker->connectDatabase();
        $this->sqliteBroker->createTable();
    }

    public function addUser(array $data): void
    {
        $this->validateRegData($data);
        
        if ($this->sqliteBroker->isUserNotExist($data['member_id'])) {
            $this->sqliteBroker->insertUserData($data);
        } else {
            $this->sqliteBroker->updateUserData($data);
        }
    }

    public function finish(): void
    {
        ?>
            <head>
                <script src="//api.bitrix24.com/api/v1/"></script>
                <script>
                    BX24.init(function(){
                        BX24.installFinish();
                    });
                </script>
            </head>
        <?php
    }

    
}