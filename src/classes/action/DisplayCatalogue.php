<?php

namespace iutnc\NetVOD\action;

use Dom\HTMLElement;
use iutnc\NetVOD\repository\NetVODRepository;

class DisplayCatalogue extends Action{

    public function execute(): string
    {

            $repo = NetVODRepository::getInstance();
            $catalogue = $repo->catalogueVOD( $_GET['recherche'] ?? '' );
            $html = <<<HTML
                    <div class='playlist-container'>
                    <h2 id='titleaction'>Catalogue</h2>
                    <form method="get">
                        <input type="hidden" name="action" value="display-catalogue">
                        <input type="search" name="recherche" placeholder="Rechercher..." required>
                        <button type="submit" hidden></button>
                    </form>
                    HTML;
            $html .= "<br><br>";
            $html .= "<div class='playlist-grid'>";
                foreach ($catalogue as $cat) {
                    $id = $cat['id'];
                    $html .= <<<HTML
                            <div class='playlist-card'>
                                <h3>{$cat['titre']}</h3>
                                <img src="src/style/img/{$cat['img']}" alt="{$cat['titre']}">
                                <div class='card-actions'>
                                    <a href='?action=display-series&series_id={$id}' class='btn-view-playlist'>Information</a>
                            HTML;
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
