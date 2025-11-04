<?php
namespace iutnc\NetVOD\action;


use iutnc\NetVOD\repository\NetVODRepository;

class DisplaySeries extends Action
{

    public function execute(): string
    {
        $html ="";
        if (isset($_GET['series_id'])) {
            $repo = NetVODRepository::getInstance();
            $episodes  = "";
            $titre = "";
            $intvalserieid = intval($_GET['series_id']);
            $episodes = $repo->episodeSeries($intvalserieid);
            $titre = $repo->getTitre($intvalserieid);
            $desc = $repo->getDesc($intvalserieid);
            $html = "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>$titre</h2>";
            $html .= "<p id='descriptionaction'>$desc</p>";
            $html .= "<div class='playlist-grid'>";
            if ($episodes) {
                    foreach ($episodes as $episode) {
                        $id = $episode['codeEpisode'];
                        $html .= "<div class='playlist-card'>";
                        $html .= "<h3>{$episode['numero']}</h3>";
                        $html .= "<h3>{$episode['titre']}</h3>";
                        $html .= "<h5>{$episode['resume']}</h5>";
                        $html .= "<div class='card-actions'>";
                        $html .= "<a href='?action=lecture-series&episode={$id}' class='btn-view-playlist'>Lecture</a>";
                        $html .= "</div>";
                        $html .= "</div>";
                    }
                    $html .= "</div>";
                    $html .= "</div>";

                $repo = NetVODRepository::getInstance();
                $commentaires = $repo->getCommentaire($_GET['series_id']);
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    return <<<HTML
                        <form method="post" action="?action=connexion">
                        <div id="titleaction">Commentaire</div>
                        <input type="nom" name="commentaire" placeholder="Commentaire" required>
                        <input type="submit" value="Publier">
                         </form>
                        HTML;
                }else{
                    $commentaire = filter_var($_POST['commentaire'], FILTER_SANITIZE_EMAIL);
                    $repo = NetVODRepository::getInstance();
                    $repo->addCommentaire($_GET['series_id'], $commentaire,$_SESSION['user']);
                    return "<div class='message-info'>Commentaire ajouté.</div>";
                }
                $html .= "<div class='playlist-container'>";
                $html .= "<h2 id='titleaction'>Commentaire</h2>";
                $html .= "<div class='playlist-grid'>";
                foreach ($commentaires as $commentaire) {
                    $html .= "<div class='playlist-card'>";
                    $html .= "<h3>{$commentaire['nomUser']}</h3>";
                    $html .= "{$commentaire['commentaire']}";
                    $html .= "</div>";
                    $html .= "</div>";
                }
                $html .= "</div>";
                $html .= "</div>";

            }
            } else {
                $html .= "<div class='message-info'>Cette série n'existe pas.</div>";
            }
        return $html;
        }
}
