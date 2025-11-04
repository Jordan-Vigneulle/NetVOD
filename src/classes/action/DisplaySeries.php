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
            $episodes = $repo->episodeSeries($_GET['series_id']);
            $titre = $repo->getTitre($_GET['series_id']);
            $desc = $repo->getDesc($_GET['series_id']);
            $html = "<div class='playlist-container'>";
            $html .= "<h2 id='titleaction'>$titre</h2>";
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
                        $html .= "<a href='?action=lecture-series&episode={$id}' class='btn-view-playlist'>Lecture</a>";
                        $html .= "</div>";
                        $html .= "</div>";
                    }
                    $html .= "</div>";
                    $html .= "</div>";
                }
            } else {
                $html .= "<div class='message-info'>Cette série n'existe pas.</div>";
            }return $html;
        }
}
