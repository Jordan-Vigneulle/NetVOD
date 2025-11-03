<?php

namespace iutnc\NetVOD\repository;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;
use PDO;
use PDOException;

class NetVODRepository
{
    private \PDO $pdo;
    private static ?NetVODRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new NetVODRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file)
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new \Exception("Error reading configuration file");
        }
        $driver = $conf['driver'];
        $host = $conf['host'];
        $database = $conf['database'];
        self::$config = [
            'dsn' => "$driver:host=$host;dbname=$database;charset=utf8mb4",
            'user' => $conf['username'],
            'pass' => $conf['password']
        ];
    }
    public function getHashUser(string $mail) : string{
        $query = "SELECT passwd FROM User WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $mail]);
        $hash = $stmt->fetchColumn();
        return $hash;
    }

    public function checkPasswordStrength(string $pass): bool {
        echo $pass;
        $length = (strlen($pass) >= 10);
        $digit = preg_match("#[\d]#", $pass); // au moins un digit
        $special = preg_match("#[\W]#", $pass); // au moins un car. spécial
        $lower = preg_match("#[a-z]#", $pass); // au moins une minuscule
        $upper = preg_match("#[A-Z]#", $pass); // au moins une majuscule
        if (!$length || !$digit || !$special || !$lower || !$upper)return false;
        return true;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function checkUserConnect(string $mail): bool
    {
        $query = "SELECT * FROM User WHERE email = :mail";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['mail' => $mail]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return false;
        }
        return true;
    }
    public function addUserBD($email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        $query = "INSERT INTO User (email, passwd, role) VALUES (:email, :passwd, 1)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'email' => $email,
            'passwd' => $hash,
        ]);
    }

}