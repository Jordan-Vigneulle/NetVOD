<?php

namespace iutnc\NetVOD\action;
use iutnc\NetVOD\repository\NetVODRepository;

class ModificationProfileAction extends Action
{

    public function execute(): string
    {
        if (isset($_SESSION['user'])) {
            $html = "<h2 id='titleaction'>Photo de profil</h2>";
            $html .= "<div class='profilepicture-container'>";
            $repo = NetVODRepository::getInstance();
            $photos = $repo->getPhotoProfileALL();
            foreach ($photos as $photo) {
                $html .= "<div class='profilepicture'>";
                $html .= "<a href='?action=modif-user&profile_picture={$photo['idPhoto']}'><img src='src/style/img/profilepicture/{$photo['img']}' alt='{$photo['idPhoto']}'></a>";
                $html .= "</div>";
            }
            if (isset($_GET['profile_picture']) && $_GET['profile_picture'] != "" && $_GET['profile_picture'] <= 16 && $_GET['profile_picture'] > 0) {
                $repo->setPhotoProfile($_SESSION['user'], $_GET['profile_picture']);
            }
            $html .= "</div>"; // fin profilepicture-container
            $userData = $repo->getUserInfo($_SESSION['user']);
            $nomActuel = htmlspecialchars($userData['nomUser'] ?? '');
            $prenomActuel = htmlspecialchars($userData['prenomUser'] ?? '');
            $html .= "<h2 id='titleaction'>Informations personnelles</h2>";
            $html .= "<form method='post' action='?action=modif-user' class='user-info-form'>";
            $html .= "<label for='nom'>Nom :</label><br>";
            $html .= "<input type='text' id='nom' name='nom' placeholder='Nom' value='{$nomActuel}' required><br><br>";
            $html .= "<label for='prenom'>Prénom :</label><br>";
            $html .= "<input type='text' id='prenom' name='prenom' placeholder='Prénom' value='{$prenomActuel}' required><br><br>";
            $genres = $repo->getAllGenres();
            $genresUser = $repo->getGenresForUser($_SESSION['user']);
            $html .= "<div id='genre-container'>";
            $html .= "<h3>Genres préférés :</h3>";
            $html .= "<div class='genre-checkboxes'>";
            foreach ($genres as $genre) {
                $checked = in_array($genre['idGenre'], $genresUser) ? 'checked' : '';
                $html .= "<label><input type='checkbox' name='genres[]' value='{$genre['idGenre']}' $checked> {$genre['libelle']}</label>";
            }
            $html .= "</div></div><br>";
            $html .= "<button type='submit' name='valider_infos'>Valider</button>";
            $html .= "</form>";
            $html .= "<br><br>";
            $html .= "<div id='auth-buttons'>";
            $html .= "<a href='?action=connexion' class='btn-auth'>Deconnexion</a>";
            $html .= "</div>";
            if (isset($_POST['valider_infos'])) {
                $nouveauNom = trim($_POST['nom']);
                $nouveauPrenom = trim($_POST['prenom']);
                $nouveauxGenres = $_POST['genres'] ?? [];
                if ($nouveauNom !== $userData['nomUser'] || $nouveauPrenom !== $userData['prenomUser']) {
                    $repo->updateUserInfo($_SESSION['user'], $nouveauNom, $nouveauPrenom);
                }
                $repo->setGenresForUser($_SESSION['user'], $nouveauxGenres);

                header("Location: ?action=modif-user");
                exit;
            }
        } else {
            $html = "<h2 id='message-info'>Vous n'êtes pas connecté</h2>";
        }
        return $html;
    }
}