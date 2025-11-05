<?php

namespace iutnc\NetVOD\action;

use iutnc\NetVOD\repository\NetVODRepository;

class DoubleAuthAction extends Action
{

    /*
     * On rend le compte actif
     */
    public function execute(): string
    {
        $repo = NetVODRepository::getInstance();
        $repo->verifierToken($_GET['token']);
        return "<div class='message-info'>Votre compte est bien actif</div>";
    }
}