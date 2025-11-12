<?php

namespace iutnc\NetVOD\action;

use iutnc\NetVOD\repository\NetVODRepository;

class DisplayCatalogue extends Action
{
    public function execute(): string
    {
        $repo = NetVODRepository::getInstance();
        $recherche = $_GET['recherche'] ?? '';
        $tri = $_GET['tri'] ?? '';
        $genre = $_GET['genre'] ?? [];
        $public = $_GET['public'] ?? [];

        $catalogue = $repo->catalogueVOD($recherche, $tri, $genre, $public);

        $checkedTitre = ($tri === 'titre') ? 'checked' : '';
        $checkedAnnee = ($tri === 'annee') ? 'checked' : '';
        $checkedDate = ($tri === 'date_ajout') ? 'checked' : '';
        $checkedEpisodes = ($tri === 'nbepisode') ? 'checked' : '';
        $checkedNote = ($tri === 'notemoy') ? 'checked' : '';

        $tabGenre = $repo->genererGenre();
        $tabPublic = $repo->genererPublic();

        $html = <<<HTML
        <div class='playlist-container'>
            <h2 id='titleaction'>Catalogue</h2>

            <form method="get" class="form-catalogue">
                <input type="hidden" name="action" value="display-catalogue">
                <input type="search" name="recherche" placeholder="Rechercher..." value="$recherche">

                <div class="playlist-filters-container">

                    <div class="filtre-colonne">
                        <label class="titre-section">Trier par</label>
                        <div class="checkbox-group">
                            <label><input type="radio" name="tri" value="titre" $checkedTitre> Titre</label>
                            <label><input type="radio" name="tri" value="annee" $checkedAnnee> Année de sortie</label>
                            <label><input type="radio" name="tri" value="date_ajout" $checkedDate> Date d'ajout</label>
                            <label><input type="radio" name="tri" value="nbepisode" $checkedEpisodes> Nombre épisodes</label>
                            <label><input type="radio" name="tri" value="notemoy" $checkedNote> Note moyenne</label>
                        </div>
                    </div>

                    <div class="filtre-colonne">
                        <label class="titre-section">Filtre genre</label>
                        <div class="checkbox-group">
        HTML;

        foreach ($tabGenre as $tgenre) {
            $libelle = htmlspecialchars($tgenre['libelle']);
            $checked = in_array($libelle, $genre) ? 'checked' : '';
            $html .= "<label><input type='checkbox' name='genre[]' value='{$libelle}' $checked> {$libelle}</label>";
        }

        $html .= <<<HTML
                        </div>
                    </div>

                    <div class="filtre-colonne">
                        <label class="titre-section">Filtre public</label>
                        <div class="checkbox-group">
        HTML;

        foreach ($tabPublic as $tpublic) {
            $type = htmlspecialchars($tpublic['typePublic']);
            $checked = in_array($type, $public) ? 'checked' : '';
            $html .= "<label><input type='checkbox' name='public[]' value='{$type}' $checked> {$type}</label>";
        }

        $html .= <<<HTML
                        </div>
                    </div>

                </div>

                <button type="submit" class="btn-appliquer">Appliquer</button>
            </form>
            <br>
            <br>

            <div class="playlist-grid">
        HTML;

        foreach ($catalogue as $cat) {
            $id = $cat['id'];
            $img = htmlspecialchars($cat['img']);
            $titre = htmlspecialchars($cat['titre']);

            $html .= <<<HTML
            <div class="playlist-card">
                <a href="?action=display-series&series_id={$id}" class="playlist-name">
                    <img src="src/style/img/{$img}" alt="{$titre}" width="100%">
                </a>
                <div class="card-actions">
            HTML;

            if (isset($_SESSION['user'])) {
                $tabFavoris = $repo->getSerieFavori($_SESSION['user']);
                $idsFavoris = array_column($tabFavoris, 'id');

                if (in_array($id, $idsFavoris)) {
                    $html .= "<a href='?action=retirerFavori&series_id={$id}'><img src='src/style/img/coeur_plein.png' class='icon-favori' alt='Retirer des favoris'></a>";
                } else {
                    $html .= "<a href='?action=ajouterFavoris&series_id={$id}'><img src='src/style/img/coeur_vide.png' class='icon-favori' alt='Ajouter aux favoris'></a>";
                }
            }

            $html .= "</div></div>";
        }

        $html .= "</div></div>";

        if (isset($_SESSION['message'])) {
            $html .= "<div class='message-info'>{$_SESSION['message']}</div>";
            unset($_SESSION['message']);
        }

        return $html;
    }
}
