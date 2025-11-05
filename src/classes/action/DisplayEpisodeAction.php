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
            $chemin = "src/video/" .$query;
            $html = <<< HTML
                <video width="100%" height="100%" controls>
                <source src="{$chemin}" type="video/mp4"/>
                </video>
            HTML;
            $dernierEp = $r->getDernierEp($idSerie)['codeEpisode'];
            if($dernierEp && $validEpisode === (int)$dernierEp){
                $html .= '<div style="width: 100%; display: flex; justify-content: flex-end; margin-top: 20px;">';
                $html .= "<a href='?action=termineSerie&series_id=$idSerie&episode=$validEpisode' class='btn-view-playlist'>Terminer</a>";
                $html .= '</div>';
            }else{
                $html .= '<div style="width: 100%; display: flex; justify-content: right; margin-top: 20px;">';
                $html .= "<a href='?action=termineSerie&series_id=$idSerie&episode=$validEpisode' class='btn-view-playlist'>EpisodeSuivant</a>";
                $html .= '</div>';
                $html .= '<div style="width: 100%; display: flex; justify-content: left; margin-top: 20px;">';
                $html .= "<a href='?action=termineSerie&series_id=$idSerie&episode=$validEpisode' class='btn-view-playlist'>EpisodePrécédent</a>";
                $html .= '</div>';
            }
            return $html;
        }
        return "Vous ne pouvez pas regarder sans être connecté";
    }
}