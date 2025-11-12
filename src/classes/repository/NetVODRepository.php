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

    public function getHashUser(string $mail): string
    {
        $query = "SELECT passwd FROM Utilisateur WHERE mailUser = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $mail]);
        $hash = $stmt->fetchColumn();
        return $hash;
    }

    public function checkPasswordStrength(string $pass): bool
    {
        $length = (strlen($pass) >= 10);
        $digit = preg_match("#[\d]#", $pass); // au moins un digit
        $special = preg_match("#[\W]#", $pass); // au moins un car. spécial
        $lower = preg_match("#[a-z]#", $pass); // au moins une minuscule
        $upper = preg_match("#[A-Z]#", $pass); // au moins une majuscule
        if (!$length || !$digit || !$special || !$lower || !$upper) return false;
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

    public function addUserBD($email, $nomuser, $prenomuser, $password, $cartB)
    {
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

    public function catalogueVOD($recherche, $tri, $genre, $public): array
    {
        $recherche = "%" . $recherche . "%";

        $allowedSort = ['titre', 'annee', 'date_ajout', 'note', 'nbepisode'];;
        if (!in_array($tri, $allowedSort)) {
            $tri = 'titre';
        }

        $triCols = [
            'titre' => 'serie.titre',
            'annee' => 'serie.annee',
            'date_ajout' => 'serie.date_ajout',
            'note' => 'note',
            'nbepisode' => 'nbepisode'
        ];
        $orderBy = $triCols[$tri];

        $query = "SELECT serie.id, serie.titre, serie.descriptif, serie.img, serie.annee, serie.date_ajout, ROUND(AVG(note),2) as note, COUNT(episode.codeEpisode) as nbepisode
            FROM serie 
            INNER JOIN episode ON episode.serie_id = serie.id
            LEFT JOIN StatutSerie ON StatutSerie.id = serie.id
            LEFT JOIN ApourGenre ON serie.id = ApourGenre.id
            LEFT JOIN Genre ON ApourGenre.idGenre = Genre.idGenre
            LEFT JOIN ApourPublic ON serie.id = ApourPublic.id
            LEFT JOIN Public ON ApourPublic.idPublic = Public.idPublic
            WHERE (serie.titre LIKE ? OR serie.descriptif LIKE ?) ";

        if (!empty($genre)) {
            $query .= "AND ";
        }
        $premier = true;
        foreach ($genre as $g) {
            if ($premier === true) {
                $query .= "Genre.libelle = ? ";
                $premier = false;
            } else {
                $query .= "OR Genre.libelle = ? ";
            }
        }

        if (!empty($public)) {
            $query .= " AND ";
        }
        $premier = true;
        foreach ($public as $p) {
            if ($premier === true) {
                $query .= " Public.typePublic = ? ";
                $premier = false;
            } else {
                $query .= " OR Public.typePublic = ? ";
            }
        }

        $query .= "GROUP BY serie.id
            ORDER BY $orderBy";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1, $recherche);
        $stmt->bindParam(2, $recherche);
        $bindIterator = 3;
        foreach ($genre as $g) {
            $stmt->bindParam($bindIterator, $g);
            $bindIterator++;
        }

        foreach ($public as $p) {
            $stmt->bindParam($bindIterator, $p);
            $bindIterator++;
        }
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
        $query = "SELECT ROUND(AVG(note), 2) as Note FROM StatutSerie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        $note = $series && $series['Note'] != 0 ? (float)$series['Note'] : 0;
        $stars = '';
        $fullStars = floor($note);
        $totalStars = 5;
        for ($i = 1; $i <= $totalStars; $i++) {
            if ($i <= $fullStars) {
                $stars .= '<span style="color: gold;">★</span>';
            }else {
                $stars .= '<span style="color: black;">★</span>';
            }
        }

        return $stars . " (".$series['Note'].")";
    }


    public function getTitre($series_id): ?string
    {
        $query = "SELECT titre FROM serie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        if (!$series) {
            return null;
        }
        return $series['titre'];
    }

    public function genererGenre(): ?array
    {
        $query = "SELECT libelle FROM Genre";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }

    public function genererPublic(): ?array
    {
        $query = "SELECT typePublic FROM Public";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
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

    public function getEpisodeSerie(int $idEpisode)
    {
        $query = "SELECT file FROM episode WHERE codeEpisode = :idEpisode";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idEpisode' => $idEpisode]);
        $series = $stmt->fetch();
        return $series['file'];
    }

    public function getDernierEp(int $idSerie): ?array
    {
        $sql = "SELECT codeEpisode FROM episode WHERE serie_id = ? AND numero = (SELECT MAX(numero) FROM episode WHERE serie_id = ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $idSerie);
        $stmt->bindParam(2, $idSerie);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $data;

    }

    public function getNumeroEp(int $idEp): ?array
    {
        $sql = "SELECT numero FROM episode WHERE codeEpisode= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $idEp);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $data;

    }



// ----------------------------------  Table utilisateur ----------------------------------

    /*
     * Fonction pour changer le mot de passe que si la personne a bien apppuyé sur le token
     */
    public function updateMDP(string $user, string $mdp) : string
    {
        $repo = NetVODRepository::getInstance();
        if(!$repo->checkPasswordStrength($mdp)){
            return "<div class='message-info'>Mot de passe invalide</div>";
        }
        if ($this->verifierCompteActif($user)) {
            $nouvmdp = password_hash($mdp, PASSWORD_DEFAULT, ['cost' => 12]);
            $query = "Update Utilisateur set passwd = :mdp  WHERE mailUser = :idUser";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['idUser' => $user, 'mdp' => $nouvmdp]);
            return "<div class='message-info'>Mot de passe changé</div>";
        }
        return "<div class='message-info'>Compte invalide</div>";
    }

    public function getInformation($idUser): array
    {
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

    public function getCommentaire(int $id_serie): array
    {
        $query = "SELECT * FROM StatutSerie INNER JOIN Utilisateur ON StatutSerie.mailUser = Utilisateur.mailUser WHERE id = :id_serie ORDER BY datecommentaire DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_serie' => $id_serie]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $commentaires = $stmt->fetchAll();
        return $commentaires;
    }

    public function setSerieFavoris(int $idSerie, string $email): bool
    {
        $query = "INSERT INTO StatutSerie (id,mailUser,favori)VALUES(?,?,1)";
        $update = "UPDATE StatutSerie SET favori = 1 WHERE id = ? and mailUser = ?";
        $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ? and mailUser = ?";

        $stmt = $this->pdo->prepare($test);
        $stmt->bindParam(1, $idSerie);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_COLUMN);

        if ($res === '0') {
            $stmt2 = $this->pdo->prepare($query);
            $stmt2->bindParam(1, $idSerie);
            $stmt2->bindParam(2, $email);
            $stmt2->execute();
            return $stmt2->rowCount() > 0;
        } else {
            $stmt3 = $this->pdo->prepare($update);
            $stmt3->bindParam(1, $idSerie);
            $stmt3->bindParam(2, $email);
            $stmt3->execute();
            return $stmt3->rowCount() > 0;
        }
    }

    public function setSerieNonFavoris(int $idSerie, string $email): bool
    {
        $update = "UPDATE StatutSerie SET favori = 0 WHERE id = ? and mailUser = ?";
        $stmt = $this->pdo->prepare($update);
        $stmt->bindParam(1, $idSerie);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function setSerieEnCours(int $idSerie, string $email, int $idEp): bool
    {
        $query = "INSERT INTO StatutSerie (id,mailUser,statut,codeEpisode)VALUES(?,?,'en cours',?)";
        $update = "UPDATE StatutSerie SET statut = 'en cours' WHERE id = ? and mailUser = ?";
        $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ? and mailUser = ?";

        $stmt = $this->pdo->prepare($test);
        $stmt->bindParam(1, $idSerie);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_COLUMN);

        if ($res === '0') {
            $stmt2 = $this->pdo->prepare($query);
            $stmt2->bindParam(1, $idSerie);
            $stmt2->bindParam(2, $email);
            $stmt2->bindParam(3, $idEp);
            $stmt2->execute();
            return $stmt2->rowCount() > 0;
        } else {
            $stmt3 = $this->pdo->prepare($update);
            $stmt3->bindParam(1, $idSerie);
            $stmt3->bindParam(2, $email);
            $stmt3->execute();
            return $stmt3->rowCount() > 0;
        }
    }

    public function avanceeSerie(int $codeEpisode, int $idSerie, string $email): bool
    {
        $update = "UPDATE StatutSerie SET codeEpisode = ? WHERE id = ? and mailUser = ?";
        $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ? and mailUser = ?";

        $stmt = $this->pdo->prepare($test);
        $stmt->bindParam(1, $idSerie);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_COLUMN);

        if ($res !== '0') {
            $stmt2 = $this->pdo->prepare($update);
            $stmt2->bindParam(1, $codeEpisode);
            $stmt2->bindParam(2, $idSerie);
            $stmt2->bindParam(3, $email);
            $stmt2->execute();
            return $stmt2->rowCount() > 0;
        } else {
            return false;
        }
    }

    public function getSerieEnCours(string $user): ?array
    {
        $query = "SELECT * FROM StatutSerie inner join serie on serie.id = StatutSerie.id WHERE StatutSerie.mailUser = ? and StatutSerie.statut = 'en cours'";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1, $user);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function addCommentaire($series_id, $commentaire, $user, $note)
    {
        $query = "SELECT * FROM StatutSerie INNER JOIN Utilisateur ON StatutSerie.mailUser = Utilisateur.mailUser WHERE id = :id_serie AND Utilisateur.mailUser = :user";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_serie' => $series_id, 'user' => $user]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $commentaires = $stmt->fetchAll();
        if (empty($commentaires)) {
            $query2 = "INSERT INTO StatutSerie (id,mailUser,commentaire,datecommentaire)VALUES(:serieid,:mailuser,:commentaire,CURRENT_TIME)";
            $stmt2 = $this->pdo->prepare($query2);
            $stmt2->execute(['serieid' => $series_id, 'mailuser' => $user, 'commentaire' => $commentaire]);
        } else {
            $update = "UPDATE StatutSerie SET commentaire = :commentaire WHERE id = :id_serie AND StatutSerie.mailUser = :user";
            $stmt = $this->pdo->prepare($update);
            $stmt->execute(['commentaire' => $commentaire, 'id_serie' => $series_id, 'user' => $user]);
        }
        if (isset($note)) {
            $notequery = "UPDATE StatutSerie SET note = :note WHERE id = :id_serie AND StatutSerie.mailUser = :user";
            $stmt = $this->pdo->prepare($notequery);
            $stmt->execute(['note' => $note, 'id_serie' => $series_id, 'user' => $user]);
        }
    }

    // ----------------------------------  Table Token ----------------------------------


    /*
     * Fonction pour ajouter la ligne token avec l'adresse mail reçu
     *
     * @param user, adresse mail de l'user
     * @param token, token à mettre dans la base de donnée
     */
    public function addToken(string $user, string $token): bool
    {
        $tok = hash('sha256', $token);
        $repo = self::getInstance();
        if (!$repo->verifUser($user)) {
            return false;
        }
        $query = $this->pdo->prepare(
            "INSERT INTO Token (mailUser, token, valider, dateExpi) VALUES (:user, :token, 0, ADDTIME(NOW(), '600')) ON DUPLICATE KEY UPDATE token = :token, valider = 0, dateExpi = ADDTIME(NOW(), '600')");
        $query->execute([
            'user' => $user,
            'token' => $tok
        ]);
        return true;
    }




    /* Fonction pour rendre un compte actif
     *
     * @param token est le token sur lequel l'utilisateur à cliquer
     */
    public function verifierToken(string $token)
    {
        $tok = hash('sha256', $token);
        $queryToken = "Select * from Token where token = :tok and dateExpi < NOW()";
        $stmt = $this->pdo->prepare($queryToken);
        $stmt->execute(['tok' => $tok]);
        if (!empty($stmt->fetchAll(\PDO::FETCH_ASSOC))) {
            $query = $this->pdo->prepare("Update Token set valider = 1 where token = :token");
            $query->execute(['token' => $tok]);
        }
    }

    /* Fonction pour voir si un compte est actif
     *
     * @param $user est l'adresse mail
     *
     * @return vrai si c'est bien actif
     */
    public function verifierCompteActif(string $user): bool
    {
        $queryToken = "Select * from Token where mailUser = :user and valider = 1";
        $query = $this->pdo->prepare($queryToken);
        $stmt = $query->execute(['user' => $user]);
        $nbToken = $query->rowCount();
        if ($nbToken > 0) {
            return true;
        }
        return false;
    }


    public function getSerieFini(string $user): ?array
    {
        $query = "SELECT * FROM StatutSerie inner join serie on serie.id = StatutSerie.id WHERE StatutSerie.mailUser = ? and StatutSerie.statut = 'fini'";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1, $user);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function setSerieTermine(int $idSerie, string $email, int $idEp): bool
    {
        $query = "INSERT INTO StatutSerie (id,mailUser,statut,codeEpisode)VALUES(?,?,'fini',?)";
        $update = "UPDATE StatutSerie SET statut = 'fini' WHERE id = ? and mailUser = ?";
        $test = "SELECT COUNT(*) FROM StatutSerie WHERE id = ? and mailUser = ?";

        $stmt = $this->pdo->prepare($test);
        $stmt->bindParam(1, $idSerie);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_COLUMN);

        if ($res === '0') {
            $stmt2 = $this->pdo->prepare($query);
            $stmt2->bindParam(1, $idSerie);
            $stmt2->bindParam(2, $email);
            $stmt2->bindParam(3, $idEp);
            $stmt2->execute();
            return $stmt2->rowCount() > 0;
        } else {
            $stmt3 = $this->pdo->prepare($update);
            $stmt3->bindParam(1, $idSerie);
            $stmt3->bindParam(2, $email);
            $stmt3->execute();
            return $stmt3->rowCount() > 0;
        }
    }


    /*
     * Photo de profile
     *
     */
    public function getPhotoProfileALL(): array
    {
        $query = "SELECT * FROM PhotoProfil";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    public function getPhotoProfile(string $user): string
    {
        $query = "SELECT img FROM PhotoProfil INNER JOIN Utilisateur on PhotoProfil.idPhoto = Utilisateur.idPhoto where mailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!$data) {
            return "src/style/img/profilepicture/inconnu.png";
        }
        return "src/style/img/profilepicture/" . $data[0]['img'];
    }

    public function setPhotoProfile(mixed $user, mixed $profile_picture)
    {
        $query = "UPDATE Utilisateur SET idPhoto = ? WHERE mailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$profile_picture, $user]);
    }


    public function getUserInfo(string $user)
    {
        $query = "SELECT * FROM Utilisateur WHERE mailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function updateUserInfo(string $user, string $nouveauNom, string $nouveauPrenom): void
    {
        $query = "UPDATE Utilisateur SET nomUser = ? , prenomUser = ? WHERE mailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$nouveauNom, $nouveauPrenom, $user]);
    }

    public function getAllGenres(): array
    {
        $query = "SELECT * FROM Genre";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getGenresForUser(string $email): array
    {
        $query = "SELECT idGenre FROM GenrePrefere WHERE emailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function setGenresForUser(string $email, array $genres): void
    {
        $this->pdo->prepare("DELETE FROM GenrePrefere WHERE emailUser = ?")->execute([$email]);
        $query = "INSERT INTO GenrePrefere (emailUser, idGenre) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($query);
        foreach ($genres as $idGenre) {
            $stmt->execute([$email, $idGenre]);
        }
    }

    public function SeriesUtilisateurfinishorCours(string $email, int $id): bool
    {
        $query = "SELECT statut FROM StatutSerie WHERE mailUser = ? AND id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email, $id]);
        $result = $stmt->fetchColumn();
        if ($result === false || is_null($result)) {
            return false;
        }
        $statut = trim(strtolower($result));
        return in_array($statut, ['en cours', 'fini']);
    }

    public function dejaCommenter(string $email, int $id): bool
    {
        $query = "SELECT commentaire FROM StatutSerie WHERE mailUser = ? AND id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email, $id]);
        $result = $stmt->fetchColumn();
        if ($result === false || is_null($result)) {
            return false;
        }
        $commentaire = trim($result);
        if ($commentaire === '') {
            return false;
        }
        return true;
    }

    private function verifUser(string $user)
    {
        $query = "SELECT mailUser FROM Utilisateur WHERE mailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$data) {
            return false;
        }
        return true;
    }

}


