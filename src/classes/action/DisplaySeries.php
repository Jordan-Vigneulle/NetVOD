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
            $moynote = 0;
            $intvalserieid = intval($_GET['series_id']);
            $episodes = $repo->episodeSeries($intvalserieid);
            $titre = $repo->getTitre($intvalserieid);
            $desc = $repo->getDesc($intvalserieid);
            $moynote = $repo->getMoyNote($intvalserieid);
            $html = "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>$titre</h2>";
            $html .= "<h3 id='titleaction'> Note moyenne : $moynote </h3>";
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
                        $html .= "<a href='?action=lecture-series&episode={$id}&series_id={$intvalserieid}'><img class='icon-favori' src='src/style/img/play.png' alt='play'></a>";
                        $html .= "</div>";
                        $html .= "</div>";
                    }
                    $html .= "</div>";
                    $html .= "</div>";

                $repo = NetVODRepository::getInstance();
                $commentaires = $repo->getCommentaire($_GET['series_id']);
                if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['user']) && $repo->SeriesUtilisateurfinishorCours($_SESSION['user'],$intvalserieid)===true) {
                    $html .= "<br><br>";
                    $html .= <<<HTML
                        <form method="post" action="?action=display-series&series_id={$intvalserieid}">
                        <div id="titleaction">Commentaire</div>
                        <input type="text" name="commentaire" placeholder="Commentaire" required>
                        <input type="number" name="note" placeholder="Note">
                        <input type="submit" value="Publier">
                        </form>
                        HTML;
                }else if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])){
                    $commentaire = filter_var($_POST['commentaire'], FILTER_DEFAULT);
                    $note = filter_var($_POST['note'], FILTER_SANITIZE_NUMBER_INT);
                    if($note >= 0 && $note <= 5){
                        $repo = NetVODRepository::getInstance();
                        $repo->addCommentaire(intval($_GET['series_id']), $commentaire,$_SESSION['user'],$note);
                        $html .= "<div class='message-info'>Commentaire ajouté.</div>";
                        header("Location: ?action=display-series&series_id={$intvalserieid}");
                }else{
                        $html .= "<div class='message-info'>Note doit être comprise entre 0 à 5.</div>";
                    }
                }
                $html .= "<div class='playlist-container'>";
                $html .= "<h2 id='titleaction'>Commentaire</h2>";
                foreach ($commentaires as $commentaire) {
                    if(!empty($commentaire['commentaire'])){
                    $html .= "<div class='playlist-card'>";
                    $html .= "<h3>{$commentaire['nomUser']}</h3>";
                    $html .= "{$commentaire['commentaire']}";
                    if(isset($commentaire['note'])){
                        $html .= "<p>{$commentaire['note']}/5</p>";
                    }
                    $html .= "</div>";
                    $html .= "<br><br>";
                    }
                }
            }
            } else {
                $html .= "<div class='message-info'>Cette série n'existe pas.</div>";
            }
        return $html;
        }
}
