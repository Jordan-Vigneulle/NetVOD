<?php
namespace iutnc\NetVOD\action;


class DefaultAction extends Action{

    public function execute(): string
    {
        session_start();
        $html = "";
        if(isset($_SESSION['user'])){
            $repo = NetVODRepository::getInstance();
            $user = $repo->getInformation($_SESSION['user']);
            $series = $repo->getSerieFavori($_SESSION['user']);
            $prenom = $user['nomUser'];
            $html .= "<div class='message-info'>Ravi de vous revoir $prenom</div>";
            if(empty($series)){
                $html .= "<div class='message-info'>Vous n'avez pas encore de série préféré ? Qu'attendez vous !</div>";
                return $html;
            }
            $html .= "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>Vos séries préférées</h2>";
            $html .= "<div class='playlist-grid'>";
            foreach ($series as $cat) {
                $id = $cat['id'];
                $html .= "<div class='playlist-card'>";
                $html .= "<h3>{$cat['titre']}</h3>";
                $html .= "<div class='card-actions'>";
                $html .= "<a href='?action=display-series&series_id={$id}' class='btn-view-playlist'>Information</a>";
                if(isset($_SESSION['user'])){
                    $html .= "<br><br>";
                    $html .= "<a href='' class='btn-view-playlist'>Mettre en favori</a>";
                }
                $html .= "</div>";
                $html .= "</div>";
            }
            $html .= "</div>";
            $html .= "</div>";
        }else{
            return "<div class='message-info'>Bienvenue sur NetVOD</div>";
        }
        return $html;
//        if(isset($_SESSION['user'])){
//            $query = prepare(Select titre
//                             from StatutVideo inner join
//                            where favori = '1');
//            $query->execute();
//
//        }
        return "Bienvenue";
    }


}
