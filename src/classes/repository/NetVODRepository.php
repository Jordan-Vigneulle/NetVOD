<?php

namespace iutnc\NetVOD\repository;

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
        $query = "SELECT passwd FROM Utilisateur WHERE mailUser = :email";
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
        $query = "SELECT * FROM Utilisateur WHERE mailUser = :mail";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['mail' => $mail]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return false;
        }
        return true;
    }
    public function addUserBD($email,$nomuser,$prenomuser, $password,$cartB) {
        $hashC = password_hash($cartB, PASSWORD_DEFAULT);
        $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        $query = "INSERT INTO Utilisateur (mailUser,nomUser,prenomUser,passwd,numeroCarte, role) VALUES (:email, :nomuser,:prenomuser,:passwd,:hashC,1)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'email' => $email,
            'nomuser' => $nomuser,
            'prenomuser' => $prenomuser,
            'passwd' => $hash,
            'hashC' => $hashC
        ]);
    }

    public function catalogueVOD() : array{
        $query = "SELECT * FROM serie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $series = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $series;
    }

    public function episodeSeries($series_id)
    {
        $query = "SELECT * FROM episode WHERE serie_id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $episodes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $episodes;
    }

    public function getTitre($series_id)
    {
        $query = "SELECT titre FROM serie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        return $series['titre'];
    }

    public function getDesc($series_id)
    {
        $query = "SELECT descriptif FROM serie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        return $series['descriptif'];
    }

    public function getEpisodeSerie(int $idEpisode){
        $query = "SELECT file FROM episode WHERE codeEpisode = :idEpisode";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idEpisode' => $idEpisode]);
        $series = $stmt->fetch();
        return $series['file'];
    }

    public function getInformation($idUser) : array{
        $query = "SELECT nomUser,prenomUser FROM Utilisateur WHERE mailUser = :idUser";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idUser' => $idUser]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $utilisateur = $stmt->fetch();
        return $utilisateur;
    }

    public function getSerieFavori($user)
    {
        $query = "SELECT titre FROM StatutSerie inner join serie on serie.id = StatutSerie.id WHERE mailUser = :mail and favori = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['mail' => $user]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data['titre'];
    }

    public function getCommentaire($id_serie) : array{
        $query = "SELECT nomUser,commentaire FROM StatutSerie INNER JOIN Utilisateur ON StatutSerie.mailUser = Utilisateur.mailUser WHERE id = :id_serie ORDER BY datecommentaire DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_serie' => $id_serie]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $commentaires = $stmt->fetchAll();
        return $commentaires;
    }
}