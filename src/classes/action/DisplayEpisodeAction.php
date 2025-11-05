<?php

namespace iutnc\NetVOD\action;

use iutnc\NetVOD\repository\NetVODRepository;

class DisplayEpisodeAction extends Action
{

    public function execute(): string
    {
        if(isset($_SESSION['user'])){
            $var = filter_var($_GET['episode']);
            $validEpisode = intval($var);
            $r = NetVODRepository::getInstance();
            $query = $r->getEpisodeSerie($validEpisode);
            $idSerie = $_GET['series_id'];
            $r->setSerieEnCours($idSerie,$_SESSION['user'],$_GET['episode']);
            $r->avanceeSerie($validEpisode,$idSerie,$_SESSION['user']);
            $chemin = "src/video/" .$query;
            $html = <<< HTML
                <video width="100%" height="100%" controls>
                <source src="{$chemin}" type="video/mp4"/>
                </video>
            HTML;
           $dernierEp = (int)$r->getDernierEp($idSerie)['codeEpisode'];
           $numeroEp = $r->getNumeroEp($validEpisode)['numero'] ?? null;
           $html .= '<div class="video-controls">';
        if ($numeroEp !== null && (int)$numeroEp !== 1) {
            $episodePrec = $validEpisode - 1;
            $html .= "<a href='?action=lecture-series&series_id=$idSerie&episode=".$episodePrec. "' class='btn-view-playlist'>◀ Épisode précédent</a>";
        } else {
            $html .= "<span></span>";
        }
        if ($validEpisode === $dernierEp) {
            $html .= "<a href='?action=termineSerie&series_id=$idSerie&episode=$validEpisode' class='btn-view-playlist'>Terminer</a>";
        } else {
            $episodeSuiv = $validEpisode + 1;
            $html .= "<a href='?action=lecture-series&series_id=$idSerie&episode=".$episodeSuiv."' class='btn-view-playlist'>Épisode suivant ▶</a>";
        }
        $html .= '</div>';
            return $html;
        }
        return "Vous ne pouvez pas regarder sans être connecté";
    }
}