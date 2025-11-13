<?php

namespace iutnc\NetVOD\repository;

use PDO;

/**
 * Classe Repository est une classe de regroupement des requêtes SQL
 *
 * On fait un rappel des tables présentes dans la base
 * episode (codeEpisode, numero, titre, resume, duree, file, #serie_id) / PK: codeEpisode
 * serie (id, titre, descriptif, img, annee, date_ajout) / PK: id
 * Utilisateur (mailUser, nomUser, prenomUser, passwd, numeroCarte, role, #idPhoto) / PK: mailUser
 * StatutSerie (#id, #mailUser, commentaire, datecommentaire, favori, statut, #codeEpisode) / PK: id, mailUSer
 * Genre (idGenre, libelle) / PK: idGenre
 * ApourGenre (#id, #idGenre) / PK: id, idGenre
 * Public (idPublic, typePublic) / PK: idPublic
 * ApourPublic(#id, #idPublic) / PK: id, idPublic
 * Token (#mailUser, token, valider, dateExpi) / PK: mailUser, token
 * GenrePrefere (#emailUser, #idGenre) / PK: emailUser, idGenre
 * PhotoProfil (idPhoto, img) /PK: idPhoto.
 *
 * On précise que les hashtags représentent des clés étrangères
 *
 * Les requêtes sont regroupés par table dans cette ordre-ci
 *  - Utilisateur
 *  - Série
 *  - Genre
 *  - Public
 *  - Episode
 *  - Statut série
 *  - Token
 *  - Photo profil
 *  - Genre prefere.
 */
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

    // ----------------------------------  Table Utilisateur ----------------------------------
    // On rappelle la table
    // Utilisateur (mailUser, nomUser, prenomUser, passwd, numeroCarte, role, #idPhoto) / PK: mailUser

    /**
     * Fonction pour récupérer toutes les informations d'un utilisateur
     *
     * @param string $mail l'email de l'utilisateur
     *
     * @return array
     */
    public function getUser(string $mail): array
    {
        $query = "SELECT * FROM Utilisateur WHERE mailUser = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $mail]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Fonction pour vérifier si le mot de passe correspond à ses critères
     * – Plus de dix caractères
     * - Au moins un chiffre
     * - Au moins un caractère spécial
     * - Au moins une miniscule
     * - Au moins une majuscule
     *
     * @param string $pass est le mot de passe
     * @return bool vrai s'il remplit les conditions
     */
    public function checkPasswordStrength(string $pass): bool
    {
        $length = (strlen($pass) >= 10);
        $digit = preg_match("#[\d]#", $pass); // au moins un digit
        $special = preg_match("#[\W]#", $pass); // Au moins un car. Spécial
        $lower = preg_match("#[a-z]#", $pass); // au moins une minuscule
        $upper = preg_match("#[A-Z]#", $pass); // au moins une majuscule
        if (!$length || !$digit || !$special || !$lower || !$upper)
            return false;
        return true;
    }

    /**
     * Vérifie si un utilisateur peut se connecter (email non existant)
     *
     * @param string $mail L'adresse email à vérifier
     *
     * @return bool True si l'email n'existe pas (connexion possible), false si l'email existe déjà
     */
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

    /**
     * Fonction pour ajouter un nouvel utilisateur
     *
     * @param $email
     * @param $nomuser
     * @param $prenomuser
     * @param $password
     * @param $cartB
     * @return void
     */
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

    /**
     * Fonction pour changer le mot de passe que si la personne a bien appuyé sur le token
     *
     * @param string $user le mail de l'utilisateur
     * @param string $mdp le nouveau mot de passe
     *
     * @return string un div différent selon les différents cas (ex : mdp incorrect)
     */
    public function updateMDP(string $user, string $mdp): string
    {
        $repo = NetVODRepository::getInstance();
        // On vérifie si le mdp correspond aux exigences
        if (!$repo->checkPasswordStrength($mdp)) {
            return "<div class='message-info'>Mot de passe invalide</div>";
        }
        // On vérifie si la personne a bien appuyé sur le token
        if ($this->verifierCompteActif($user)) {
            $nouvmdp = password_hash($mdp, PASSWORD_DEFAULT, ['cost' => 12]);
            $query = "Update Utilisateur set passwd = :mdp  WHERE mailUser = :idUser";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['idUser' => $user, 'mdp' => $nouvmdp]);
            return "<div class='message-info'>Mot de passe changé</div>";
        }
        return "<div class='message-info'>Compte invalide</div>";
    }

    /**
     * Fonction pour changer les informations d'un utilisateur
     *
     * @param string $user, mail de l'utilisateur
     * @param string $nouveauNom, nouveau nom de l'utilisateur
     * @param string $nouveauPrenom, nouveau prénom de l'utilisateur
     * @return void
     */
    public function updateUserInfo(string $user, string $nouveauNom, string $nouveauPrenom): void
    {
        $query = "UPDATE Utilisateur SET nomUser = ? , prenomUser = ? WHERE mailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$nouveauNom, $nouveauPrenom, $user]);
    }

    /**
     * Fonction pour savoir si l'utilisateur existe déjà
     *
     * @param string $user, mail de l'utilisateur
     * @return bool vrai si l'utilisateur existe
     */
    private function verifUser(string $user): bool
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

    // ---------------------------------- Table série ----------------------------------
    // On rappelle la table
    // serie (id, titre, descriptif, img, annee, date_ajout) / PK: id

    /**
     * Fonction pour rechercher et trier dans le catalogue
     *
     * @param string $recherche le mot qu'on va rechercher dans le titre ou le descriptif.
     * @param string $tri la façon qu'on veut trier (alphabétique, date de sortie, date d'ajout, note moyenne ou le nombre d'épisodes)
     * @param string[] $genre le ou les genres qu'on veut
     * @param string[] $public le ou les publics qu'on veut
     * @return array on renvoie la liste des séries trouvées
     */


    public function catalogueVOD($recherche, $tri, $genre, $public): array
    {
        // Pour trouver le mot dans un paragraphe
        $recherche = "%" . $recherche . "%";

        $allowedSort = ['titre', 'annee', 'date_ajout', 'note', 'nbepisode'];

        // Si aucun tri n'est choisi, on trie par ordre alphabétique
        if (!in_array($tri, $allowedSort)) {
            $tri = 'titre';
        }

        // Les différents tris possibles
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

        // On met AND au début si il a des genres
        if (!empty($genre)) {
            $query .= "AND ";
        }
        $premier = true;
        foreach ($genre as $g) {
            if ($premier === true) {
                $query .= "Genre.libelle = ? ";
                $premier = false;
            } else {
                $query .= "OR Genre.libelle = ? "; // On a choisi de mettre OR, car on trouvait cela plus logique
            }
        }

        // On met AND au début si il a des publics
        if (!empty($public)) {
            $query .= " AND ";
        }
        $premier = true;
        foreach ($public as $p) {
            if ($premier === true) {
                $query .= " Public.typePublic = ? ";
                $premier = false;
            } else {
                $query .= " OR Public.typePublic = ? "; // On a choisi de mettre OR, car on trouvait cela plus logique
            }
        }

        // On ajoute enfin le tri
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

    /**
     * Fonction pour récupérer la déscription d'une série
     *
     * @param int $series_id est l'id de la série
     * @return string la déscription
     */
    public function getDesc($series_id): string
    {
        $query = "SELECT descriptif FROM serie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        return $series['descriptif'];
    }

    /**
     * Fonction pour récupérer la note moyenne d'une série
     *
     * @param int $series_id est l'id de la série
     * @return string les étoiles et la note entre parentheses
     */
    public function getMoyNote($series_id): string
    {
        $query = "SELECT ROUND(AVG(note), 2) as Note FROM StatutSerie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        
        $note = $series && $series['Note'] != 0 ? (float) $series['Note'] : 0;
        $stars = '';
        $fullStars = floor($note);
        $totalStars = 5;

        // Boucle pour générer les 5 étoiles
        for ($i = 1; $i <= $totalStars; $i++) {
            if ($i <= $fullStars) {
                $stars .= '<span style="color: gold;">★</span>';
            } else {
                $stars .= '<span style="color: black;">★</span>';
            }
        }

        return $stars . " (" . $series['Note'] . ")";
    }

    /**
     * Fonction pour récupérer tout ce que possède une série
     *
     * @param int $series_id est l'id de la série
     * @return array|null
     */
    public function getSerie($series_id): ?array
    {
        $query = "SELECT * FROM serie WHERE id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $series = $stmt->fetch();
        if (!$series) {
            return null;
        }
        return $series;
    }


    // ----------------------------------  Table Genre ----------------------------------
    // Genre (idGenre, libelle) / PK: idGenre

    /**
     * Fonction pour avoir tous les genres présents dans la table Genre
     *
     * @return array|null
     */
    public function genererGenre(): ?array
    {
        $query = "SELECT libelle FROM Genre";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }
    // ----------------------------------  Table Public ----------------------------------
    // Public (idPublic, typePublic) / PK: idPublic

    /**
     * Fonction pour avoir tous les publics présents dans la table Public
     *
     * @return array|null
     */
    public function genererPublic(): ?array
    {
        $query = "SELECT typePublic FROM Public";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchALL(\PDO::FETCH_ASSOC);
    }


    // ----------------------------------  Table épisode ----------------------------------
    // episode (codeEpisode, numero, titre, resume, duree, file, #serie_id) / PK: codeEpisode
    /**
     * Fonction pour avoir tous les épisodes d'une series
     *
     * @param int $series_id l'id de la série
     *
     * @return array
     */
    public function episodeSeries($series_id): array
    {
        $query = "SELECT * FROM episode WHERE serie_id = :idSerie";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idSerie' => $series_id]);
        $episodes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $episodes;
    }

    /**
     * Fonction pour obtenir le dernier épisode d'une série
     *
     * @param int $idSerie la série
     * @return array|null null si elle n'a pas d'épisodes
     */
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

    /**
     * Fonction pour obtenir tous les attributs d'un épisode
     *
     * @param int $idEp le numéro de l'épisode
     * @return array|null si l'épisode n'existe pas
     */
    public function getEpisode(int $idEp): ?array
    {
        $sql = "SELECT * FROM episode WHERE codeEpisode= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $idEp);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $data;

    }

    // ----------------------------------  Table statutSerie ----------------------------------
    // StatutSerie (#id, #mailUser, commentaire, datecommentaire, favori, statut, #codeEpisode) / PK: id, mailUSer
    /**
     * Fonction pour récupérer toutes les lignes dans la table statuSerie qui sont reliés à un utilisateur et dont la série est en favori
     *
     * @param string $user le mail de l'utilisateur
     * @return array, les différents tuples
     */
    public function getSerieFavori(string $user): array
    {
        $query = "SELECT * FROM StatutSerie inner join serie on serie.id = StatutSerie.id WHERE mailUser = :mail and favori = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['mail' => $user]);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Fonction pour récupérer tout ce qui est relié à une série, on range par la date du commentaire
     *
     * @param int $id_serie
     * @return array
     */
    public function getCommentaire(int $id_serie): array
    {
        $query = "SELECT * FROM StatutSerie INNER JOIN Utilisateur ON StatutSerie.mailUser = Utilisateur.mailUser WHERE id = :id_serie ORDER BY datecommentaire DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_serie' => $id_serie]);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $commentaires = $stmt->fetchAll();
        return $commentaires;
    }

    /**
     * Fonction pour mettre une série en favori pour un utilisateur
     *
     * @param int $idSerie, id de la série
     * @param string $email, mail de l'utilisateur
     * @return bool
     */
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
    /**
     * Fonction pour enlever la série des favoris pour un utilisateur
     *
     * @param int $idSerie, id de la série
     * @param string $email, mail de l'utilisateur
     * @return bool
     */
    public function setSerieNonFavoris(int $idSerie, string $email): bool
    {
        $update = "UPDATE StatutSerie SET favori = 0 WHERE id = ? and mailUser = ?";
        $stmt = $this->pdo->prepare($update);
        $stmt->bindParam(1, $idSerie);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }



    /**
     * Fonction pour mettre une série en cours
     *
     * @param int $idSerie, l'id de la série
     * @param string $email, le mail de l'utilisateur
     * @param int $idEp, le code de l'épisode
     * @return bool
     */
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
    /**
     * Fonction pour mettre une série finie
     *
     * @param int $codeEpisode, le code de l'épisode
     * @param int $idSerie, l'id de la série
     * @param string $email, le mail de l'utilisateur
     * @return bool
     */
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

    /**
     * Fonction pour obtenir les séries qui sont en cours pour un utilisateur donné
     *
     * @param string $user, mail de l'utilisateur
     * @return array|null, renvoie des différentes séries
     */
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

    /**
     * Fonction pour ajouter une nouvelle note et un nouveau commentaire
     *
     * @param int $series_id, id de la série
     * @param string $commentaire, le commentaire qu'on veut mettre
     * @param string $user, le mail de l'utilisateur
     * @param int $note, la note qu'on veut mettre
     * @return void
     */
    public function addCommentaire(int $series_id, string $commentaire, string $user, int $note) : void
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

    /**
     * Fonction pour avoir les séries qui sont finies d'un utilisateur donné
     *
     * @param string $user, mail de l'utilisateur
     * @return array|null, liste des séries
     */
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

    /**
     * Fonction pour mettre une série d'un utilisateur terminé
     *
     * @param int $idSerie, id d'une série
     * @param string $email, mail d'un utilisateur
     * @param int $idEp, code d'un épisode
     * @return bool
     */
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

    /**
     * Fonction pour savoir si une série est en cours ou fini par l'utilisateur
     *
     * @param string $email, mail de l'utilisateur
     * @param int $id, id de la série
     * @return bool, vrai si la série est en cours ou fini
     */
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

    /**
     * Fonction pour savoir si l'utilisateur a déjà commenté la série
     *
     * @param string $email, mail de l'utilisateur
     * @param int $id, id de la série
     * @return bool, vrai si c'est déjà commenté
     */
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

    // ----------------------------------  Table Token ----------------------------------
    // Token (#mailUser, token, valider, dateExpi) / PK: mailUser, token

    /**
     * Fonction pour ajouter la ligne token avec l'adresse mail reçu
     *
     * @param string $user, adresse mail de l'user
     * @param string $token, token à mettre dans la base de donnée
     */
    public function addToken(string $user, string $token): bool
    {
        $tok = hash('sha256', $token);
        $repo = self::getInstance();
        if (!$repo->verifUser($user)) {
            return false;
        }
        $query = $this->pdo->prepare(
            "INSERT INTO Token (mailUser, token, valider, dateExpi) VALUES (:user, :token, 0, ADDTIME(NOW(), '600')) ON DUPLICATE KEY UPDATE token = :token, valider = 0, dateExpi = ADDTIME(NOW(), '600')"
        );
        $query->execute([
            'user' => $user,
            'token' => $tok
        ]);
        return true;
    }

    /**
     * Fonction pour rendre un compte actif
     *
     * @param string $token est le token sur lequel l'utilisateur à cliquer
     */
    public function verifierToken(string $token): void
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

    /**
     * Fonction pour voir si un compte est actif
     *
     * @param string $user est l'adresse mail
     *
     * @return bool vrai si c'est bien actif
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

    // ----------------------------------  Table PhotoProfil ----------------------------------
    // PhotoProfil (idPhoto, img) /PK: idPhoto
    /**
     * Récupération de toutes les photos de profils
     *
     * @return array, liste des photos
     */
    public function getPhotoProfileALL(): array
    {
        $query = "SELECT * FROM PhotoProfil";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Récupération de la photo de profil d'un utilisateur
     *
     * @param string $user, mail de l'utilisateur
     * @return string, le nom du fichier de l'image
     */
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

    /**
     * Fonction pour changer la photo de profil d'un utilisateur
     *
     * @param string $user, mail de l'utilisateur
     * @param string $profile_picture, nom de l'image
     * @return void
     */
    public function setPhotoProfile(string $user, string $profile_picture): void
    {
        $query = "UPDATE Utilisateur SET idPhoto = ? WHERE mailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$profile_picture, $user]);
    }

    // ----------------------------------  Table GenrePréférée ----------------------------------
    // GenrePréfère (#emailUser, #idGenre) / PK: emailUser, idGenre

    /**
     * Fonction pour récupérer les genres préférés d'un utilisateur
     *
     * @param string $email, mail de l'utilisateur
     * @return array, liste des genres
     */
    public function getGenresForUser(string $email): array
    {
        $query = "SELECT idGenre FROM GenrePrefere WHERE emailUser = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Fonction pour ajouter de nouveaux genres préférés à un utilisateur
     *
     * @param string $email, mail de l'utilisateur
     * @param array $genres, liste de nouveaux genres
     * @return void
     */
    public function setGenresForUser(string $email, array $genres): void
    {
        $this->pdo->prepare("DELETE FROM GenrePrefere WHERE emailUser = ?")->execute([$email]);
        $query = "INSERT INTO GenrePrefere (emailUser, idGenre) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($query);
        foreach ($genres as $idGenre) {
            $stmt->execute([$email, $idGenre]);
        }
    }
}


