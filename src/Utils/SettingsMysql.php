<?php

namespace InstagramAPI;

use PDO;

class SettingsMysql
{
    private $sets;
    private $pdo;
    private $instagramUsername;

    public $tableName = 'user_settings';

    public function __construct($instagramUsername, $username, $password, $host, $dbName)
    {
        $username = is_null($username) ? 'root' : $username;
        $password = is_null($password) ? '' : $password;
        $host = is_null($host) ? 'localhost' : $host;
        $dbName = is_null($dbName) ? 'instagram' : $dbName;

        $this->dbName = $dbName;
        $this->instagramUsername = $instagramUsername;

        $this->connect($username, $password, $host, $dbName);
        $this->autoInstall();
        $this->populateObject();
    }

    public function isLogged()
    {
        if (($this->get('id') != null) && ($this->get('username_id') != null) && ($this->get('token') != null)
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function get($key, $default = null)
    {
        if ($key == 'sets') {
            return $this->sets;
        }
        if (isset($this->sets[$key])) {
            return $this->sets[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        if ($key == 'sets' or $key == 'pdo' or $key == 'instagramUsername') {
            return;
        }

        $this->sets[$key] = $value;
        $this->Save();
    }

    public function Save()
    {
        $this->sets['username'] = $this->instagramUsername;
        if (isset($this->sets['id'])) {
            $sql = "update {$this->tableName} set ";
            $bindList[':id'] = $this->sets['id'];
        } else {
            $sql = "insert into {$this->tableName} set ";
        }

        foreach ($this->sets as $key => $value) {
            if ($key == 'id') {
                continue;
            }
            $fieldList[] = "$key = :$key";
            $bindList[":$key"] = $value;
        }

        $sql = $sql.implode(',', $fieldList).(isset($this->sets['id']) ? ' where id=:id' : '');
        $std = $this->pdo->prepare($sql);

        $std->execute($bindList);

        if (!isset($this->sets['id'])) {
            $this->sets['id'] = $this->pdo->lastinsertid();
        }
    }

    private function connect($username, $password, $host, $dbName)
    {
        try {
            $pdo = new \PDO("mysql:host={$host};dbname={$dbName}", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->query('SET NAMES UTF8');
            $pdo->setAttribute(PDO::ERRMODE_WARNING, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
        } catch (PDOException $e) {
            throw new InstagramException('Settings: cannot connect to mysql adapter', ErrorCode::INTERNAL_SETTINGS_ERROR);
        }
    }

    private function autoInstall()
    {
        $std = $this->pdo->prepare('SHOW TABLES WHERE tables_in_'.$this->dbName.' = :tableName');
        $std->execute([':tableName' => $this->tableName]);
        if ($std->rowCount()) {
            return true;
        }

        $this->pdo->exec('CREATE TABLE `'.$this->tableName."` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NULL DEFAULT NULL,
            `version` VARCHAR(10) NULL DEFAULT NULL,
            `user_agent` VARCHAR(255) NULL DEFAULT NULL,
            `username_id` BIGINT(20) NULL DEFAULT NULL,
            `token` VARCHAR(255) NULL DEFAULT NULL,
            `manufacturer` VARCHAR(255) NULL DEFAULT NULL,
            `device` VARCHAR(255) NULL DEFAULT NULL,
            `model` VARCHAR(255) NULL DEFAULT NULL,
            `cookies` TEXT NULL,
            `date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `last_login` BIGINT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_username` (`username`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");
    }

    private function populateObject()
    {
        $std = $this->pdo->prepare("select * from {$this->tableName} where username=:username");
        $std->execute([':username' => $this->instagramUsername]);
        $result = $std->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            foreach ($result as $key => $value) {
                $this->sets[$key] = $value;
            }
        }
    }
}
