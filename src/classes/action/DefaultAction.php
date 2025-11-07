<?php
namespace iutnc\NetVOD\action;


use iutnc\NetVOD\repository\NetVODRepository;

class DefaultAction extends Action{

    public function execute(): string
    {
        $html = "";
        if(isset($_SESSION['user'])){
            $repo = NetVODRepository::getInstance();
            $user = $repo->getInformation($_SESSION['user']);
            $series = $repo->getSerieFavori($_SESSION['user']);
            $seriesEnCours = $repo->getSerieEnCours($_SESSION['user']);
            $seriesTermine = $repo->getSerieFini($_SESSION['user']);
            $prenom = $user['prenomUser'];
            $html .= "<div class='message-info'>Ravi de vous revoir $prenom</div>";
            $html .= "<div class='playlist-container'>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "<h2 id='titleaction'>Ma liste</h2>";
            $html .= "<br><br>";
            $html .= "<div class='playlist-grid'>";
            if(empty($series)){
                $html .= "<div class='message-info'>Vous n'avez pas encore de série préféré ? Qu'attendez vous !</div>";
            }else{
                foreach ($series as $cat) {
                $html .= "<div class='playlist-card'>";
                $html .=  "<a href='?action=display-series&series_id={$cat['id']}'><img src=src/style/img/{$cat['img']} alt='{$cat['titre']}' width='100%')></a>";
                $html .= "<div class='card-actions'>";
                $html .= "</div>";
                $html .= "</div>";
            }
            $html .= "</div>";
            $html .= "</div>";
            }
            $html .= "<br><br>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>Reprendre la lecture</h2>";
            $html .= "<br><br>";
            if(empty($seriesEnCours)){
                $html .= "<div class='message-info'>Vous n'avez pas encore de série en cours ? Qu'attendez vous !</div>";
            }else{
                $html .= "<div class='playlist-grid'>";
                foreach ($seriesEnCours as $cat2) {
                $html .= "<div class='playlist-card'>";
                $html .=  "<a href='?action=lecture-series&episode={$cat2['codeEpisode']}&series_id={$cat2['id']}'><img src=src/style/img/{$cat2['img']} alt='{$cat2['titre']}'></a>";
                        $html .= "<div class='card-actions'>";
                $html .= "</div>";
                $html .= "</div>";
            }
            
            $html .= "</div>";
            $html .= "</div>";
            $html .= "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>Vos séries Terminées</h2>";
            $html .= "<br><br>";
            }
            if(empty($seriesTermine)){
                $html .= "<div class='message-info'>Vous n'avez pas encore de série Terminée ? Qu'attendez vous !</div>";
            }else{
                $html .= "<div class='playlist-grid'>";
                foreach ($seriesTermine as $cat3) {
                $html .= "<div class='playlist-card'>";
                $html .=  "<a href='?action=display-series&series_id={$cat3['id']}'><img src=src/style/img/{$cat3['img']} alt='{$cat3['titre']}'></a>";
                        $html .= "<div class='card-actions'>";
                $html .= "</div>";
                $html .= "</div>";
            }
            $html .= "</div>";
            $html .= "</div>";
            }
        }else{
            $html = "<div class='message-info'>";
            $html .= "<p>Bienvenue sur NetVOD</p>";
            $html .= "<div id='auth-buttons'>";
            $html .= "<a href='?action=add-user' class='btn-auth'>Inscription</a>";
            $html .= "<a href='?action=connexion' class='btn-auth'>Connexion</a>";
            $html .= "</div>";
            $html .= "</div>";
            return $html;
        }
        return $html;
    }


}
