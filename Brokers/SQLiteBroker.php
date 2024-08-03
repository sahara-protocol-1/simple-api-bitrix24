<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Brokers;

use PDO;
use SimpleApiBitrix24\Exception\SQLiteBrokerException;

class SQLiteBroker
{
    private static SQLiteBroker $instance;
    private array $settings;
    private string $dbFolderPath;
    private string $dbName;
    private string $tableName = 'users';
    private ?PDO $db = null;

    private function __construct($settings)
    {
        $this->settings = $settings;

        $dbFolderPath = $settings['database_settings']['sqlite_database_folder_path'];
        (substr($dbFolderPath, -1, 1) == '/') ? null : $dbFolderPath .= '/';

        $this->dbFolderPath = $dbFolderPath;
        $this->dbName = $settings['database_settings']['sqlite_database_name'];
    }

    public static function getInstance($settings)
    {
        if (empty(self::$instance)) {
            self::$instance = new SQLiteBroker($settings);
        }
        return self::$instance;
    }

    public function connectDatabase():void
    {
        $this->createFolderIfNotExist($this->dbFolderPath);
        $this->db = new PDO("sqlite:{$this->dbFolderPath}{$this->dbName}");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function createFolderIfNotExist($folderPath): void
    {
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    }

    public function getUser($memberId)
    {
        $this->connectDatabase();

        $query = "SELECT * FROM users WHERE member_id = :member_id";
        try {
            $stmt = $this->db->prepare($query);
        } catch (\PDOException $e) {
            throw new \PDOException("install the app first -> ApiBitrix24::installStart(array data). {$e->getMessage()}");
        }
        
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new SQLiteBrokerException("member id not found in database: {$memberId}");
        }

        return $result;
    }

    public function updateUserTokens($data)
    {
        $query = "UPDATE {$this->tableName} SET 
        access_token = :access_token,
        expires_in = :expires_in,
        refresh_token = :refresh_token
        WHERE member_id = :member_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $data['member_id']);
        $stmt->bindParam(':access_token', $data['access_token']);
        $stmt->bindParam(':expires_in', $data['expires_in']);
        $stmt->bindParam(':refresh_token', $data['refresh_token']);

        return $stmt->execute();
    }

    public function createTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id INTEGER PRIMARY KEY,
            member_id TEXT UNIQUE,
            access_token TEXT,
            expires_in TEXT,
            application_token TEXT,
            refresh_token TEXT,
            domain TEXT,
            client_endpoint TEXT
        )";

        $this->db->exec($sql);
    }

    public function insertUserData($data)
    {
        $query = "INSERT INTO {$this->tableName} 
        (member_id, access_token, expires_in, application_token, refresh_token, domain, client_endpoint) 
        VALUES 
        (:member_id, :access_token, :expires_in, :application_token, :refresh_token, :domain, :client_endpoint)";
    
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $data['member_id']);
        $stmt->bindParam(':access_token', $data['access_token']);
        $stmt->bindParam(':expires_in', $data['expires_in']);
        $stmt->bindParam(':application_token', $data['application_token']);
        $stmt->bindParam(':refresh_token', $data['refresh_token']);
        $stmt->bindParam(':domain', $data['domain']);
        $stmt->bindParam(':client_endpoint', $data['client_endpoint']);

        $stmt->execute();
    }

    public function updateUserData($data)
    {
        $query = "UPDATE {$this->tableName} SET 
        access_token = :access_token,
        expires_in = :expires_in,
        application_token = :application_token,
        refresh_token = :refresh_token,
        domain = :domain,
        client_endpoint = :client_endpoint 
        WHERE member_id = :member_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $data['member_id']);
        $stmt->bindParam(':access_token', $data['access_token']);
        $stmt->bindParam(':expires_in', $data['expires_in']);
        $stmt->bindParam(':application_token', $data['application_token']);
        $stmt->bindParam(':refresh_token', $data['refresh_token']);
        $stmt->bindParam(':domain', $data['domain']);
        $stmt->bindParam(':client_endpoint', $data['client_endpoint']);

        $stmt->execute();
    }

    public function isUserNotExist($memberId)
    {
        $query = "SELECT * FROM {$this->tableName} WHERE member_id = :member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result === false) {
            return true;
        }
        return false;
    }



}