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
            $query = NetVODRepository::getInstance()->getEpisodeSerie($validEpisode);
            $html = <<< HTML
                <video width="100%" height="100%" controls>
                <source src="{$query}" type="video/mp4" />
                </video>
            HTML;
            return $html;
        }
        return "Vous ne pouvez pas regarder sans être connecté";
    }
}