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
            $prenom = $user['nomUser'];
            $html .= "<div class='message-info'>Ravi de vous revoir $prenom</div>";
            $html .= "<div class='playlist-container'>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "<h2 id='titleaction'>Vos séries préférées</h2>";
            $html .= "<br><br>";
            $html .= "<div class='playlist-grid'>";
            if(empty($series)){
                $html .= "<div class='message-info'>Vous n'avez pas encore de série préféré ? Qu'attendez vous !</div>";
            }else{
                foreach ($series as $cat) {
                $html .= "<div class='playlist-card'>";
                $html .= "<h3>{$cat['titre']}</h3>";
                $html .= "<div class='card-actions'>";
                $html .= "<a href='?action=display-series&series_id={$cat['id']}' class='btn-view-playlist'>Direction episode</a>";
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
            $html .= "<h2 id='titleaction'>Vos séries en cours</h2>";
            $html .= "<br><br>";
            if(empty($seriesEnCours)){
                $html .= "<div class='message-info'>Vous n'avez pas encore de série en cours ? Qu'attendez vous !</div>";
            }else{
                $html .= "<div class='playlist-grid'>";
                foreach ($seriesEnCours as $cat2) {
                $html .= "<div class='playlist-card'>";
                $html .= "<h3>{$cat2['titre']}</h3>";
                $html .= "<div class='card-actions'>";
                $html .= "<a href='?action=display-series&series_id={$cat2['id']}' class='btn-view-playlist'>Direction episode</a>";
                $html .= "</div>";
                $html .= "</div>";
            }
            $html .= "</div>";
            $html .= "</div>";
            }
        }else{
            return "<div class='message-info'>Bienvenue sur NetVOD</div>";
        }
        return $html;
    }


}
