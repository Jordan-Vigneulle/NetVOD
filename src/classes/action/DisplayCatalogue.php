<?php

namespace iutnc\NetVOD\action;


use iutnc\NetVOD\repository\NetVODRepository;

class DisplayCatalogue extends Action{

    public function execute(): string
    {

            $repo = NetVODRepository::getInstance();
            $catalogue = $repo->catalogueVOD();
            $html = "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>Catalogue</h2>";

                $html .= "<div class='playlist-grid'>";
                foreach ($catalogue as $cat) {
                    $id = $cat['id'];
                    $html .= "<div class='playlist-card'>";
                    $html .= "<h3>{$cat['titre']}</h3>";
                    $html .= "<div class='card-actions'>";
                    $html .= "<a href='?action=display-series&series_id={$id}' class='btn-view-playlist'>Information</a>";
                    if(isset($_SESSION['user'])){
                        $html .= "<br><br>";
                        $tabFavoris = $repo->getSerieFavori($_SESSION['user']);
                        $idsFavoris = array_column($tabFavoris, 'id');
                        if (in_array($id, $idsFavoris)) {
                            $html .= "<a href='?action=retirerFavori&series_id={$id}'>";
                            $html .= "<img src='src/style/img/coeur_plein.png' alt='Retirer des favoris' class='icon-favori'>";
                            $html .= "</a>";
                        } else {
                            $html .= "<a href='?action=ajouterFavoris&series_id={$id}'>";
                            $html .= "<img src='src/style/img/coeur_vide.png' alt='Mettre en favori' class='icon-favori'>";
                            $html .= "</a>";
                        }
                    }
                    $html .= "</div>";
                    $html .= "</div>";
                }
                $html .= "</div>";
            $html .= "</div>";
            if (isset($_SESSION['message'])) {
                    $html .= "<div class='message-info'>{$_SESSION['message']}</div>";
                    unset($_SESSION['message']);
            }
            return $html;
    }


}
