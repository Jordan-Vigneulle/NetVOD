<?php

namespace iutnc\NetVOD\action;


use iutnc\NetVOD\repository\NetVODRepository;

class DisplayCatalogue extends Action{

    public function execute(): string
    {
        session_start();
            $repo = NetVODRepository::getInstance();
            $catalogue = $repo->catalogueVOD();
            $html = '<link rel="stylesheet" href="src/style/css/style.css">';
            $html .= "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>Catalogue</h2>";
                $html .= "<div class='playlist-grid'>";
                foreach ($catalogue as $cat) {
                    $id = $cat['id'];
                    $html .= "<div class='playlist-card'>";
                    $html .= "<h3>{$cat['titre']}</h3>";
                    $html .= "<div class='card-actions'>";
                    $html .= "<a href='?action=display-catalogue&playlist_id={$id}' class='btn-view-playlist'>Lecture</a>";
                    if($_SESSION['user']){
                        $html .= "<br><br>";
                        $html .= "<a href='' class='btn-fav'>Mettre en favori</a>";
                    }
                    $html .= "</div>";
                    $html .= "</div>";
                }
                $html .= "</div>";
            $html .= "</div>";
            return $html;
    }


}
