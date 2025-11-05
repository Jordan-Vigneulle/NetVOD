<?php

namespace iutnc\NetVOD\repository;

use PDO;

class NetVODRepository
{
    private \PDO $pdo;
    private static ?NetVODRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
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

// ----------------------------------  Table série ----------------------------------

public function catalogueVOD($recherche, $tri) : array {
    $recherche = "%" . $recherche . "%";

    $allowedSort = ['titre', 'annee', 'date_ajout'];
    if (!in_array($tri, $allowedSort)) {
        $tri = 'titre';
    }

    $query = "SELECT * FROM serie 
            WHERE titre LIKE ? OR descriptif LIKE ?
            ORDER BY $tri";

    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(1, $recherche);
    $stmt->bindParam(2, $recherche);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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

    public function getMoyNote($series_id): string
    {
        $query = "SELECT ROUND(AVG(note),2) as Note FROM StatutSerie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        if($series['Note']!=0){
            $note = $series['Note'];
        }else{
            $note = 0;
        }
        return $note;
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

    public function getDernierEp(int $idSerie): ?array{
        $sql = "SELECT codeEpisode FROM episode WHERE serie_id = ? AND numero = (SELECT MAX(numero) FROM episode WHERE serie_id = ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1,$idSerie);
        $stmt->bindParam(2,$idSerie);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(!$data){
            return null;
        }
        return $data; 

    }

    public function getNumeroEp(int $idEp): ?array{
        $sql = "SELECT numero FROM episode WHERE codeEpisode= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1,$idEp);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(!$data){
            return null;
        }
        return $data; 

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

    public function getSerieFavori(string $user): array
    {
        $query = "SELECT * FROM StatutSerie inner join serie on serie.id = StatutSerie.id WHERE mailUser = :mail and favori = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['mail' => $user]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    public function getCommentaire(int $id_serie) : array{
        $query = "SELECT * FROM StatutSerie INNER JOIN Utilisateur ON StatutSerie.mailUser = Utilisateur.mailUser WHERE id = :id_serie ORDER BY datecommentaire DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_serie' => $id_serie]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $commentaires = $stmt->fetchAll();
        return $commentaires;
    }

        public function setSerieFavoris(int $idSerie,string $email): bool{
            $query = "INSERT INTO StatutSerie (id,mailUser,favori)VALUES(?,?,1)";
            $update = "UPDATE StatutSerie SET favori = 1 WHERE id = ? and mailUser = ?";
            $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ? and mailUser = ?";

            $stmt = $this->pdo->prepare($test);
            $stmt->bindParam(1,$idSerie);
            $stmt->bindParam(2,$email);
            $stmt->execute();
            $res = $stmt->fetch(\PDO::FETCH_COLUMN);

            if($res === '0'){
                $stmt2 = $this->pdo->prepare($query);
                $stmt2->bindParam(1,$idSerie);
                $stmt2->bindParam(2,$email);
                $stmt2->execute();
                return $stmt->rowCount() > 0;
            }else{
                $stmt3 = $this->pdo->prepare($update);
                $stmt3->bindParam(1,$idSerie);
                $stmt3->bindParam(2,$email);
                $stmt3->execute();
                return $stmt->rowCount() > 0;
            }
        }

        public function setSerieNonFavoris(int $idSerie,string $email): bool{
            $update = "UPDATE StatutSerie SET favori = 0 WHERE id = ? and mailUser = ?";
            $stmt = $this->pdo->prepare($update);
            $stmt->bindParam(1,$idSerie);
            $stmt->bindParam(2,$email);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        }

        public function setSerieEnCours(int $idSerie,string $email,int $idEp): bool{
            $query = "INSERT INTO StatutSerie (id,mailUser,statut,codeEpisode)VALUES(?,?,'en cours',?)";
            $update = "UPDATE StatutSerie SET statut = 'en cours' WHERE id = ? and mailUser = ?";
            $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ? and mailUser = ?";

            $stmt = $this->pdo->prepare($test);
            $stmt->bindParam(1,$idSerie);
            $stmt->bindParam(2,$email);
            $stmt->execute();
            $res = $stmt->fetch(\PDO::FETCH_COLUMN);

            if($res === '0'){
                $stmt2 = $this->pdo->prepare($query);
                $stmt2->bindParam(1,$idSerie);
                $stmt2->bindParam(2,$email);
                $stmt2->bindParam(3,$idEp);
                $stmt2->execute();
                return $stmt->rowCount() > 0;
            }else{
                $stmt3 = $this->pdo->prepare($update);
                $stmt3->bindParam(1,$idSerie);
                $stmt3->bindParam(2,$email);
                $stmt3->execute();
                return $stmt->rowCount() > 0;
            }
        }

        public function getSerieEnCours(string $user): ?array
    {
        $query = "SELECT * FROM StatutSerie inner join serie on serie.id = StatutSerie.id WHERE StatutSerie.mailUser = ? and StatutSerie.statut = 'en cours'";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1,$user);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(!$data){
            return null;
        }
        return $data;
    }

    public function addCommentaire($series_id, $commentaire, $user,$note)
    {
        $query = "SELECT * FROM StatutSerie INNER JOIN Utilisateur ON StatutSerie.mailUser = Utilisateur.mailUser WHERE id = :id_serie AND Utilisateur.mailUser = :user";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_serie' => $series_id, 'user' => $user]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $commentaires = $stmt->fetchAll();
        if(empty($commentaires)){
            $query2 = "INSERT INTO StatutSerie (id,mailUser,commentaire,datecommentaire)VALUES(:serieid,:mailuser,:commentaire,CURRENT_TIME)";
            $stmt2 = $this->pdo->prepare($query2);
            $stmt2->execute(['serieid' => $series_id, 'mailuser' => $user, 'commentaire' => $commentaire]);
        }else{
            $update = "UPDATE StatutSerie SET commentaire = :commentaire WHERE id = :id_serie AND StatutSerie.mailUser = :user";
            $stmt = $this->pdo->prepare($update);
            $stmt->execute(['commentaire'=>$commentaire,'id_serie' => $series_id, 'user' => $user]);
        }
        if(isset($note)){
            $notequery = "UPDATE StatutSerie SET note = :note WHERE id = :id_serie AND StatutSerie.mailUser = :user";
            $stmt = $this->pdo->prepare($notequery);
            $stmt->execute(['note'=>$note,'id_serie' => $series_id, 'user' => $user]);
        }
    }

    public function getSerieFini(string $user): ?array
    {
        $query = "SELECT * FROM StatutSerie inner join serie on serie.id = StatutSerie.id WHERE StatutSerie.mailUser = ? and StatutSerie.statut = 'fini'";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1,$user);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(!$data){
            return null;
        }
        return $data;
    }

    public function setSerieTermine(int $idSerie,string $email,int $idEp): bool{
            $query = "INSERT INTO StatutSerie (id,mailUser,statut,codeEpisode)VALUES(?,?,'fini',?)";
            $update = "UPDATE StatutSerie SET statut = 'fini' WHERE id = ? and mailUser = ?";
            $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ? and mailUser = ?";

            $stmt = $this->pdo->prepare($test);
            $stmt->bindParam(1,$idSerie);
            $stmt->bindParam(2,$email);
            $stmt->execute();
            $res = $stmt->fetch(\PDO::FETCH_COLUMN);

            if($res === '0'){
                $stmt2 = $this->pdo->prepare($query);
                $stmt2->bindParam(1,$idSerie);
                $stmt2->bindParam(2,$email);
                $stmt2->bindParam(3,$idEp);
                $stmt2->execute();
                return $stmt->rowCount() > 0;
            }else{
                $stmt3 = $this->pdo->prepare($update);
                $stmt3->bindParam(1,$idSerie);
                $stmt3->bindParam(2,$email);
                $stmt3->execute();
                return $stmt->rowCount() > 0;
            }
        }

}


