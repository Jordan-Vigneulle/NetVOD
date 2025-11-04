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
        $query = "SELECT * FROM User WHERE email = :mail";
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

// ----------------------------------  Table série ----------------------------------

    public function catalogueVOD() : array{
        $query = "SELECT * FROM serie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $series = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $series;
    }

    public function getDesc($series_id): string
    {
        $query = "SELECT descriptif FROM serie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        return $series['descriptif'];
    }

    public function getTitre($series_id): ?string
    {
        $query = "SELECT titre FROM serie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        if(!$series){
            return null;
        }
        return $series['titre'];
    }


// ----------------------------------  Table épisode ----------------------------------

    public function episodeSeries($series_id): array
    {
        $query = "SELECT * FROM episode WHERE serie_id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $episodes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $episodes;
    }

    public function getEpisodeSerie(int $idEpisode){
        $query = "SELECT file FROM episode WHERE codeEpisode = :idEpisode";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idEpisode' => $idEpisode]);
        $series = $stmt->fetch();
        return $series['file'];
    }



// ----------------------------------  Table utilisateur ----------------------------------

    public function getInformation($idUser) : array{
        $query = "SELECT nomUser,prenomUser FROM Utilisateur WHERE mailUser = :idUser";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idUser' => $idUser]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $utilisateur = $stmt->fetch();
        return $utilisateur;
    }


// ----------------------------------  Table statutSerie ----------------------------------

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

    public function setSerieFavoris(int $idSerie,string $email): bool{
        $query = "INSERT INTO StatutSerie (id,mailUser,favori)VALUES(?,?,1)";
        $update = "UPDATE StatutSerie SET favori = 1 WHERE id = ?";
        $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ?";

        $stmt = $this->pdo->prepare($test);
        $stmt->bindParam(1,$idSerie);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_COLUMN);

        if($res = 1){
            $stmt2 = $this->pdo->prepare($query);
            $stmt2->bindParam(1,$idSerie);
            $stmt2->bindParam(2,$email);
            $stmt2->execute();
            return $stmt->rowCount() > 0;
        }else{
            $stmt3 = $this->pdo->prepare($update);
            $stmt3->bindParam(1,$idSerie);
            $stmt3->execute();
            return $stmt->rowCount() > 0;
        }
    }
}


