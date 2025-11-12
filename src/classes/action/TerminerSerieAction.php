<?php

namespace iutnc\NetVOD\action;

use iutnc\NetVOD\repository\NetVODRepository;

require_once 'vendor/autoload.php';

/*
 * Classe permettant de terminer une serie
 * */
class TerminerSerieAction extends Action
{
    public function execute(): string
    {

        if (!isset($_SESSION['user'])) {
            return "<div class='message-info'>Vous devez vous connecter</div>";
        }
        $idSerie = filter_var($_GET['series_id'], FILTER_VALIDATE_INT);
        $r = NetVODRepository::getInstance();
        $verifSerie = $r->getTitre($idSerie);
        if ($verifSerie === null) {
            return "Echec, cette série n'éxiste pas";
        }
        $resultat = $r->setSerieTermine($idSerie, $_SESSION['user'], $_GET['episode']);
        header("Location: .");
        exit();
    }
}
